from collections import Counter
from hashlib import sha256

import joblib
import pytest
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.pipeline import Pipeline

from train_model import DEFAULT_MODEL_PATH as INTENT_MODEL_PATH
from train_priority_model import (
    DEFAULT_BENCHMARK_DATASET_PATH,
    DEFAULT_MODEL_PATH,
    DEFAULT_TRAINING_DATASET_PATH,
    PRIORITIES,
    build_priority_pipeline,
    load_priority_dataset,
    load_priority_training_and_benchmark,
    train_and_evaluate_priority,
)


def test_priority_training_and_benchmark_are_frozen_and_separate() -> None:
    training_messages, training_priorities = load_priority_dataset(
        DEFAULT_TRAINING_DATASET_PATH
    )
    benchmark_messages, benchmark_priorities = load_priority_dataset(
        DEFAULT_BENCHMARK_DATASET_PATH
    )

    assert len(training_messages) == 1_340
    assert Counter(training_priorities) == Counter(
        {priority: 335 for priority in PRIORITIES}
    )
    assert len(benchmark_messages) == 80
    assert Counter(benchmark_priorities) == Counter(
        {priority: 20 for priority in PRIORITIES}
    )
    assert set(training_priorities) == set(PRIORITIES)
    assert set(benchmark_priorities) == set(PRIORITIES)
    assert all(message.strip() for message in training_messages + benchmark_messages)
    assert len(set(training_messages)) == 1_340
    assert len(set(benchmark_messages)) == 80
    assert set(training_messages).isdisjoint(benchmark_messages)


def test_priority_loader_rejects_data_leakage() -> None:
    with pytest.raises(ValueError, match="must not share messages"):
        load_priority_training_and_benchmark(
            DEFAULT_BENCHMARK_DATASET_PATH,
            DEFAULT_BENCHMARK_DATASET_PATH,
        )


def test_priority_benchmark_is_frozen() -> None:
    benchmark_digest = sha256(DEFAULT_BENCHMARK_DATASET_PATH.read_bytes()).hexdigest()

    assert benchmark_digest == (
        "3d7711cb7e289f10866715131ce87b6bda2a5734a5acdcee88bad0456a6c1d9d"
    )


def test_priority_pipeline_uses_the_required_configuration() -> None:
    pipeline = build_priority_pipeline()
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


def test_priority_training_saves_a_separate_loadable_artifact(tmp_path) -> None:
    model_path = tmp_path / "priority_classifier.joblib"

    train_and_evaluate_priority(
        DEFAULT_TRAINING_DATASET_PATH,
        DEFAULT_BENCHMARK_DATASET_PATH,
        model_path,
    )
    saved_pipeline = joblib.load(model_path)

    assert model_path.exists()
    assert isinstance(saved_pipeline, Pipeline)
    assert set(saved_pipeline.classes_) == set(PRIORITIES)
    assert hasattr(saved_pipeline, "predict_proba")
    assert DEFAULT_MODEL_PATH.exists()
    assert INTENT_MODEL_PATH.exists()
    assert DEFAULT_MODEL_PATH.name == "priority_classifier.joblib"
    assert INTENT_MODEL_PATH.name == "intent_classifier.joblib"
    assert DEFAULT_MODEL_PATH != INTENT_MODEL_PATH
