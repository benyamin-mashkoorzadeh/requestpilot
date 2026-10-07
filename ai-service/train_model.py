import csv
from collections import Counter
from collections.abc import Sequence
from dataclasses import dataclass
from pathlib import Path

import joblib
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
from sklearn.pipeline import Pipeline


RANDOM_SEED = 42
INTENTS = ("sales", "support", "billing", "refund", "cancellation")

BASE_DIR = Path(__file__).resolve().parent
DEFAULT_TRAINING_DATASET_PATH = BASE_DIR / "data" / "training.csv"
DEFAULT_BENCHMARK_DATASET_PATH = BASE_DIR / "data" / "benchmark.csv"
DEFAULT_MODEL_PATH = BASE_DIR / "models" / "intent_classifier.joblib"


@dataclass(frozen=True)
class PredictionResult:
    message: str
    actual_intent: str
    predicted_intent: str
    confidence: float

    @property
    def is_correct(self) -> bool:
        return self.actual_intent == self.predicted_intent


def load_dataset(dataset_path: Path) -> tuple[list[str], list[str]]:
    texts: list[str] = []
    intents: list[str] = []

    with dataset_path.open(encoding="utf-8", newline="") as dataset_file:
        reader = csv.DictReader(dataset_file)

        if reader.fieldnames != ["text", "intent"]:
            raise ValueError("Dataset columns must be exactly: text,intent")

        for row_number, row in enumerate(reader, start=2):
            text = row["text"].strip()
            intent = row["intent"].strip()

            if not text or not intent:
                raise ValueError(f"Dataset row {row_number} contains an empty value")

            texts.append(text)
            intents.append(intent)

    if set(intents) != set(INTENTS):
        raise ValueError(f"Dataset must contain exactly these intents: {', '.join(INTENTS)}")

    return texts, intents


def load_training_and_benchmark(
    training_dataset_path: Path = DEFAULT_TRAINING_DATASET_PATH,
    benchmark_dataset_path: Path = DEFAULT_BENCHMARK_DATASET_PATH,
) -> tuple[list[str], list[str], list[str], list[str]]:
    training_texts, training_intents = load_dataset(training_dataset_path)
    benchmark_texts, benchmark_intents = load_dataset(benchmark_dataset_path)
    overlapping_messages = set(training_texts) & set(benchmark_texts)

    if overlapping_messages:
        raise ValueError(
            "Training and benchmark datasets must not contain the same messages"
        )

    return training_texts, training_intents, benchmark_texts, benchmark_intents


def build_pipeline() -> Pipeline:
    return Pipeline(
        steps=[
            (
                "tfidf",
                TfidfVectorizer(
                    lowercase=True,
                    ngram_range=(1, 2),
                    stop_words="english",
                    sublinear_tf=True,
                ),
            ),
            (
                "classifier",
                LogisticRegression(
                    max_iter=1_000,
                    random_state=RANDOM_SEED,
                ),
            ),
        ]
    )


def analyze_predictions(
    pipeline: Pipeline,
    messages: Sequence[str],
    actual_intents: Sequence[str],
) -> list[PredictionResult]:
    predictions = pipeline.predict(messages)
    probability_rows = pipeline.predict_proba(messages)
    model_intents = [str(intent) for intent in pipeline.classes_]
    results: list[PredictionResult] = []

    for message, actual_intent, prediction, probabilities in zip(
        messages,
        actual_intents,
        predictions,
        probability_rows,
        strict=True,
    ):
        predicted_intent = str(prediction)
        predicted_index = model_intents.index(predicted_intent)
        results.append(
            PredictionResult(
                message=message,
                actual_intent=actual_intent,
                predicted_intent=predicted_intent,
                confidence=float(probabilities[predicted_index]),
            )
        )

    return results


def build_confusion_matrix(results: Sequence[PredictionResult]) -> list[list[int]]:
    matrix = confusion_matrix(
        [result.actual_intent for result in results],
        [result.predicted_intent for result in results],
        labels=INTENTS,
    )

    return matrix.tolist()


def print_error_analysis(results: Sequence[PredictionResult]) -> None:
    print("\nAll benchmark predictions:")

    for index, result in enumerate(results, start=1):
        outcome = "CORRECT" if result.is_correct else "INCORRECT"
        print(f"\n{index:02}. [{outcome}]")
        print(f"    Message: {result.message}")
        print(f"    Actual: {result.actual_intent}")
        print(f"    Predicted: {result.predicted_intent}")
        print(f"    Confidence: {result.confidence:.3f}")

    mistakes = [result for result in results if not result.is_correct]
    print(f"\nMisclassified examples only ({len(mistakes)}):")

    for index, result in enumerate(mistakes, start=1):
        print(f"\n{index:02}.")
        print(f"    Message: {result.message}")
        print(f"    Actual: {result.actual_intent}")
        print(f"    Predicted: {result.predicted_intent}")
        print(f"    Confidence: {result.confidence:.3f}")

    matrix = build_confusion_matrix(results)
    column_width = max(len(intent) for intent in INTENTS) + 2
    row_label_width = len("actual \\ predicted") + 2

    print("\nConfusion matrix (rows = actual, columns = predicted):")
    print("actual \\ predicted".ljust(row_label_width), end="")
    print("".join(intent.rjust(column_width) for intent in INTENTS))

    for intent, row in zip(INTENTS, matrix, strict=True):
        print(intent.ljust(row_label_width), end="")
        print("".join(str(value).rjust(column_width) for value in row))


def train_and_evaluate(
    training_dataset_path: Path = DEFAULT_TRAINING_DATASET_PATH,
    benchmark_dataset_path: Path = DEFAULT_BENCHMARK_DATASET_PATH,
    model_path: Path = DEFAULT_MODEL_PATH,
) -> tuple[Pipeline, float]:
    (
        training_texts,
        training_intents,
        benchmark_texts,
        benchmark_intents,
    ) = load_training_and_benchmark(
        training_dataset_path,
        benchmark_dataset_path,
    )

    pipeline = build_pipeline()
    pipeline.fit(training_texts, training_intents)

    prediction_results = analyze_predictions(
        pipeline,
        benchmark_texts,
        benchmark_intents,
    )
    predictions = [result.predicted_intent for result in prediction_results]
    accuracy = accuracy_score(benchmark_intents, predictions)

    print(f"Training examples: {len(training_texts)}")
    print(f"Training examples per intent: {dict(Counter(training_intents))}")
    print(f"Benchmark examples: {len(benchmark_texts)}")
    print(f"Benchmark examples per intent: {dict(Counter(benchmark_intents))}")
    print(f"Accuracy: {accuracy:.3f}\n")
    print("Per-intent evaluation:")
    print(
        classification_report(
            benchmark_intents,
            predictions,
            labels=INTENTS,
            digits=3,
            zero_division=0,
        )
    )
    print_error_analysis(prediction_results)

    model_path.parent.mkdir(parents=True, exist_ok=True)
    joblib.dump(pipeline, model_path)
    print(f"Model saved to: {model_path}")

    return pipeline, accuracy


if __name__ == "__main__":
    train_and_evaluate()
