from pathlib import Path

import joblib
from sklearn.metrics import accuracy_score, classification_report
from sklearn.pipeline import Pipeline

from train_priority_model import (
    DEFAULT_MODEL_PATH,
    PRIORITIES,
    PriorityPrediction,
    analyze_priority_predictions,
    build_priority_confusion_matrix,
    load_priority_dataset,
)


CHALLENGE_BENCHMARK_PATH = (
    Path(__file__).resolve().parent / "data" / "priority_challenge_benchmark.csv"
)


def load_priority_model(model_path: Path = DEFAULT_MODEL_PATH) -> Pipeline:
    model = joblib.load(model_path)

    if not isinstance(model, Pipeline):
        raise ValueError("Priority model artifact must contain a fitted pipeline")
    if set(model.classes_) != set(PRIORITIES) or not hasattr(model, "predict_proba"):
        raise ValueError("Priority model artifact has an unexpected prediction contract")

    return model


def print_predictions(results: list[PriorityPrediction]) -> None:
    print("\nAll challenge predictions:")

    for index, result in enumerate(results, start=1):
        outcome = "CORRECT" if result.is_correct else "INCORRECT"
        print(f"\n{index:02}. [{outcome}]")
        print(f"    Message: {result.message}")
        print(f"    Actual: {result.actual_priority}")
        print(f"    Predicted: {result.predicted_priority}")
        print(f"    Confidence: {result.confidence:.3f}")


def print_mistakes(results: list[PriorityPrediction]) -> None:
    mistakes = [result for result in results if not result.is_correct]
    print(f"\nChallenge mistakes only ({len(mistakes)}):")

    for index, result in enumerate(mistakes, start=1):
        print(f"\n{index:02}.")
        print(f"    Message: {result.message}")
        print(f"    Actual: {result.actual_priority}")
        print(f"    Predicted: {result.predicted_priority}")
        print(f"    Confidence: {result.confidence:.3f}")


def print_lowest_confidence_correct(
    results: list[PriorityPrediction],
    limit: int = 5,
) -> None:
    correct_results = sorted(
        (result for result in results if result.is_correct),
        key=lambda result: result.confidence,
    )
    print(f"\nLowest-confidence correct predictions ({min(limit, len(correct_results))}):")

    for index, result in enumerate(correct_results[:limit], start=1):
        print(f"\n{index:02}.")
        print(f"    Message: {result.message}")
        print(f"    Priority: {result.predicted_priority}")
        print(f"    Confidence: {result.confidence:.3f}")


def print_confusion_matrix(results: list[PriorityPrediction]) -> None:
    matrix = build_priority_confusion_matrix(results)
    column_width = max(len(priority) for priority in PRIORITIES) + 2
    row_label_width = len("actual \\ predicted") + 2

    print("\nConfusion matrix (rows = actual, columns = predicted):")
    print("actual \\ predicted".ljust(row_label_width), end="")
    print("".join(priority.rjust(column_width) for priority in PRIORITIES))

    for priority, row in zip(PRIORITIES, matrix, strict=True):
        print(priority.ljust(row_label_width), end="")
        print("".join(str(value).rjust(column_width) for value in row))


def evaluate_priority_challenge(
    benchmark_path: Path = CHALLENGE_BENCHMARK_PATH,
    model_path: Path = DEFAULT_MODEL_PATH,
) -> tuple[list[PriorityPrediction], float]:
    messages, priorities = load_priority_dataset(benchmark_path)
    model = load_priority_model(model_path)
    results = analyze_priority_predictions(model, messages, priorities)
    predictions = [result.predicted_priority for result in results]
    correct_count = sum(result.is_correct for result in results)
    accuracy = accuracy_score(priorities, predictions)

    print(f"Challenge examples: {len(messages)}")
    print(f"Correct: {correct_count}/{len(results)}")
    print(f"Incorrect: {len(results) - correct_count}/{len(results)}")
    print(f"Accuracy: {accuracy:.3f}\n")
    print("Per-priority challenge evaluation:")
    print(
        classification_report(
            priorities,
            predictions,
            labels=PRIORITIES,
            digits=3,
            zero_division=0,
        )
    )
    print_predictions(results)
    print_mistakes(results)
    print_lowest_confidence_correct(results)
    print_confusion_matrix(results)

    return results, accuracy


if __name__ == "__main__":
    evaluate_priority_challenge()
