from collections import Counter
from hashlib import sha256
import re

import joblib
import pytest
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.pipeline import Pipeline

from train_model import (
    DEFAULT_BENCHMARK_DATASET_PATH,
    DEFAULT_TRAINING_DATASET_PATH,
    INTENTS,
    analyze_predictions,
    build_confusion_matrix,
    build_pipeline,
    load_dataset,
    load_training_and_benchmark,
    print_error_analysis,
    train_and_evaluate,
)


def test_training_and_benchmark_are_separate_balanced_datasets() -> None:
    training_texts, training_intents = load_dataset(DEFAULT_TRAINING_DATASET_PATH)
    benchmark_texts, benchmark_intents = load_dataset(
        DEFAULT_BENCHMARK_DATASET_PATH
    )

    assert DEFAULT_TRAINING_DATASET_PATH != DEFAULT_BENCHMARK_DATASET_PATH
    assert len(training_texts) == 1_400
    assert Counter(training_intents) == Counter({intent: 280 for intent in INTENTS})
    assert len(set(training_texts)) == 1_400
    assert len(benchmark_texts) == 30
    assert Counter(benchmark_intents) == Counter({intent: 6 for intent in INTENTS})
    assert set(training_texts).isdisjoint(benchmark_texts)


def test_frozen_benchmark_file_is_byte_for_byte_unchanged() -> None:
    benchmark_digest = sha256(DEFAULT_BENCHMARK_DATASET_PATH.read_bytes()).hexdigest()

    assert benchmark_digest == (
        "8f3cdcf858ce41c9236b5cdbaa391e876ad98175179f57e88d746a7a5f73065c"
    )


def test_combined_loader_rejects_data_leakage() -> None:
    with pytest.raises(ValueError, match="must not contain the same messages"):
        load_training_and_benchmark(
            DEFAULT_BENCHMARK_DATASET_PATH,
            DEFAULT_BENCHMARK_DATASET_PATH,
        )


def test_training_has_no_normalized_label_conflicts_or_benchmark_near_duplicates() -> None:
    training_texts, training_intents = load_dataset(DEFAULT_TRAINING_DATASET_PATH)
    benchmark_texts, _ = load_dataset(DEFAULT_BENCHMARK_DATASET_PATH)
    labels_by_normalized_message: dict[str, set[str]] = {}

    for message, intent in zip(training_texts, training_intents, strict=True):
        normalized = re.sub(r"[^a-z0-9 ]", "", message.lower())
        normalized = " ".join(normalized.split())
        labels_by_normalized_message.setdefault(normalized, set()).add(intent)

    features = TfidfVectorizer(
        analyzer="char_wb",
        ngram_range=(3, 5),
    ).fit_transform(training_texts + benchmark_texts)
    similarities = cosine_similarity(
        features[: len(training_texts)],
        features[len(training_texts) :],
    )

    assert all(
        len(intents) == 1 for intents in labels_by_normalized_message.values()
    )
    assert similarities.max() < 0.70


def test_pipeline_uses_unchanged_tfidf_and_logistic_regression_settings() -> None:
    pipeline = build_pipeline()
    tfidf = pipeline.named_steps["tfidf"]
    classifier = pipeline.named_steps["classifier"]

    assert isinstance(tfidf, TfidfVectorizer)
    assert tfidf.lowercase is True
    assert tfidf.ngram_range == (1, 2)
    assert tfidf.stop_words == "english"
    assert tfidf.sublinear_tf is True
    assert isinstance(classifier, LogisticRegression)
    assert classifier.max_iter == 1_000
    assert classifier.random_state == 42


def test_training_saves_a_loadable_fitted_pipeline(tmp_path) -> None:
    model_path = tmp_path / "intent_classifier.joblib"

    _, accuracy = train_and_evaluate(
        DEFAULT_TRAINING_DATASET_PATH,
        DEFAULT_BENCHMARK_DATASET_PATH,
        model_path,
    )
    saved_pipeline = joblib.load(model_path)
    prediction = saved_pipeline.predict(["Could someone help me access my workspace?"])

    assert model_path.exists()
    assert isinstance(saved_pipeline, Pipeline)
    assert set(saved_pipeline.classes_) == set(INTENTS)
    assert prediction[0] in INTENTS
    assert 0.0 <= accuracy <= 1.0


def test_error_analysis_covers_every_frozen_benchmark_prediction(capsys) -> None:
    (
        training_texts,
        training_intents,
        benchmark_texts,
        benchmark_intents,
    ) = load_training_and_benchmark()
    pipeline = build_pipeline()
    pipeline.fit(training_texts, training_intents)

    results = analyze_predictions(pipeline, benchmark_texts, benchmark_intents)
    matrix = build_confusion_matrix(results)
    mistakes = [result for result in results if not result.is_correct]
    print_error_analysis(results)
    output = capsys.readouterr().out

    assert len(results) == 30
    assert all(0.0 <= result.confidence <= 1.0 for result in results)
    assert len(matrix) == len(INTENTS)
    assert all(len(row) == len(INTENTS) for row in matrix)
    assert sum(sum(row) for row in matrix) == 30
    assert sum(matrix[index][index] for index in range(len(INTENTS))) == 30 - len(
        mistakes
    )
    assert len(mistakes) == 0
    assert matrix == [
        [6, 0, 0, 0, 0],
        [0, 6, 0, 0, 0],
        [0, 0, 6, 0, 0],
        [0, 0, 0, 6, 0],
        [0, 0, 0, 0, 6],
    ]
    assert "All benchmark predictions:" in output
    assert f"Misclassified examples only ({len(mistakes)}):" in output
    assert "Confusion matrix (rows = actual, columns = predicted):" in output
