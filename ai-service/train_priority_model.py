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
PRIORITIES = ("low", "normal", "high", "urgent")

BASE_DIR = Path(__file__).resolve().parent
DEFAULT_TRAINING_DATASET_PATH = BASE_DIR / "data" / "priority_training.csv"
DEFAULT_BENCHMARK_DATASET_PATH = BASE_DIR / "data" / "priority_benchmark.csv"
DEFAULT_MODEL_PATH = BASE_DIR / "models" / "priority_classifier.joblib"


@dataclass(frozen=True)
class PriorityPrediction:
    message: str
    actual_priority: str
    predicted_priority: str
    confidence: float

    @property
    def is_correct(self) -> bool:
        return self.actual_priority == self.predicted_priority


def load_priority_dataset(dataset_path: Path) -> tuple[list[str], list[str]]:
    messages: list[str] = []
    priorities: list[str] = []

    with dataset_path.open(encoding="utf-8", newline="") as dataset_file:
        reader = csv.DictReader(dataset_file)

        if reader.fieldnames != ["text", "priority"]:
            raise ValueError("Dataset columns must be exactly: text,priority")

        for row_number, row in enumerate(reader, start=2):
            message = row["text"].strip()
            priority = row["priority"].strip()

            if not message or not priority:
                raise ValueError(f"Dataset row {row_number} contains an empty value")
            if priority not in PRIORITIES:
                raise ValueError(f"Dataset row {row_number} has an invalid priority")

            messages.append(message)
            priorities.append(priority)

    if set(priorities) != set(PRIORITIES):
        raise ValueError(
            f"Dataset must contain exactly these priorities: {', '.join(PRIORITIES)}"
        )

    return messages, priorities


def load_priority_training_and_benchmark(
    training_dataset_path: Path = DEFAULT_TRAINING_DATASET_PATH,
    benchmark_dataset_path: Path = DEFAULT_BENCHMARK_DATASET_PATH,
) -> tuple[list[str], list[str], list[str], list[str]]:
    training_messages, training_priorities = load_priority_dataset(
        training_dataset_path
    )
    benchmark_messages, benchmark_priorities = load_priority_dataset(
        benchmark_dataset_path
    )

    if set(training_messages) & set(benchmark_messages):
        raise ValueError(
            "Priority training and benchmark datasets must not share messages"
        )

    return (
        training_messages,
        training_priorities,
        benchmark_messages,
        benchmark_priorities,
    )


def build_priority_pipeline() -> Pipeline:
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


def analyze_priority_predictions(
    pipeline: Pipeline,
    messages: Sequence[str],
    actual_priorities: Sequence[str],
) -> list[PriorityPrediction]:
    predictions = pipeline.predict(messages)
    probability_rows = pipeline.predict_proba(messages)
    model_priorities = [str(priority) for priority in pipeline.classes_]
    results: list[PriorityPrediction] = []

    for message, actual_priority, prediction, probabilities in zip(
        messages,
        actual_priorities,
        predictions,
        probability_rows,
        strict=True,
    ):
        predicted_priority = str(prediction)
        predicted_index = model_priorities.index(predicted_priority)
        results.append(
            PriorityPrediction(
                message=message,
                actual_priority=actual_priority,
                predicted_priority=predicted_priority,
                confidence=float(probabilities[predicted_index]),
            )
        )

    return results


def build_priority_confusion_matrix(
    results: Sequence[PriorityPrediction],
) -> list[list[int]]:
    matrix = confusion_matrix(
        [result.actual_priority for result in results],
        [result.predicted_priority for result in results],
        labels=PRIORITIES,
    )

    return matrix.tolist()


def print_priority_error_analysis(results: Sequence[PriorityPrediction]) -> None:
    mistakes = [result for result in results if not result.is_correct]
    print(f"\nMisclassified priority examples ({len(mistakes)}):")

    for index, result in enumerate(mistakes, start=1):
        print(f"\n{index:02}.")
        print(f"    Message: {result.message}")
        print(f"    Actual: {result.actual_priority}")
        print(f"    Predicted: {result.predicted_priority}")
        print(f"    Confidence: {result.confidence:.3f}")

    matrix = build_priority_confusion_matrix(results)
    column_width = max(len(priority) for priority in PRIORITIES) + 2
    row_label_width = len("actual \\ predicted") + 2

    print("\nConfusion matrix (rows = actual, columns = predicted):")
    print("actual \\ predicted".ljust(row_label_width), end="")
    print("".join(priority.rjust(column_width) for priority in PRIORITIES))

    for priority, row in zip(PRIORITIES, matrix, strict=True):
        print(priority.ljust(row_label_width), end="")
        print("".join(str(value).rjust(column_width) for value in row))


def train_and_evaluate_priority(
    training_dataset_path: Path = DEFAULT_TRAINING_DATASET_PATH,
    benchmark_dataset_path: Path = DEFAULT_BENCHMARK_DATASET_PATH,
    model_path: Path = DEFAULT_MODEL_PATH,
) -> tuple[Pipeline, float]:
    (
        training_messages,
        training_priorities,
        benchmark_messages,
        benchmark_priorities,
    ) = load_priority_training_and_benchmark(
        training_dataset_path,
        benchmark_dataset_path,
    )

    pipeline = build_priority_pipeline()
    pipeline.fit(training_messages, training_priorities)

    results = analyze_priority_predictions(
        pipeline,
        benchmark_messages,
        benchmark_priorities,
    )
    predictions = [result.predicted_priority for result in results]
    accuracy = accuracy_score(benchmark_priorities, predictions)
    correct_count = sum(result.is_correct for result in results)

    print(f"Training examples: {len(training_messages)}")
    print(f"Training examples per priority: {dict(Counter(training_priorities))}")
    print(f"Benchmark examples: {len(benchmark_messages)}")
    print(f"Benchmark examples per priority: {dict(Counter(benchmark_priorities))}")
    print(f"Correct: {correct_count}/{len(results)}")
    print(f"Incorrect: {len(results) - correct_count}/{len(results)}")
    print(f"Accuracy: {accuracy:.3f}\n")
    print("Per-priority evaluation:")
    print(
        classification_report(
            benchmark_priorities,
            predictions,
            labels=PRIORITIES,
            digits=3,
            zero_division=0,
        )
    )
    print_priority_error_analysis(results)

    model_path.parent.mkdir(parents=True, exist_ok=True)
    joblib.dump(pipeline, model_path)
    print(f"Priority model saved to: {model_path}")

    return pipeline, accuracy


if __name__ == "__main__":
    train_and_evaluate_priority()
