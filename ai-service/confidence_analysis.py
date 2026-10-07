from collections.abc import Sequence
from dataclasses import dataclass
from pathlib import Path
from statistics import mean

import joblib
from sklearn.pipeline import Pipeline

from evaluate_priority_challenge import CHALLENGE_BENCHMARK_PATH
from train_model import (
    DEFAULT_BENCHMARK_DATASET_PATH as INTENT_BENCHMARK_PATH,
    DEFAULT_MODEL_PATH as INTENT_MODEL_PATH,
    INTENTS,
    load_dataset,
)
from train_priority_model import (
    DEFAULT_MODEL_PATH as PRIORITY_MODEL_PATH,
    PRIORITIES,
    load_priority_dataset,
)


THRESHOLDS = (0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.60)


@dataclass(frozen=True)
class ConfidencePrediction:
    message: str
    actual_label: str
    predicted_label: str
    confidence: float

    @property
    def is_correct(self) -> bool:
        return self.actual_label == self.predicted_label


@dataclass(frozen=True)
class ConfidenceSummary:
    correct_count: int
    incorrect_count: int
    average_correct_confidence: float | None
    average_incorrect_confidence: float | None
    minimum_correct_confidence: float | None
    maximum_correct_confidence: float | None


@dataclass(frozen=True)
class ThresholdResult:
    threshold: float
    total_reviewed: int
    incorrect_caught: int
    incorrect_passed: int
    correct_reviewed: int
    caught_mistake_ids: tuple[str, ...]


@dataclass(frozen=True)
class CombinedThresholdResult:
    threshold: float
    total_reviewed: int
    target_mistakes_caught: int
    target_mistakes_passed: int
    correct_target_predictions_reviewed: int


def load_saved_pipeline(
    model_path: Path,
    expected_classes: Sequence[str],
    model_name: str,
) -> Pipeline:
    model = joblib.load(model_path)

    if not isinstance(model, Pipeline):
        raise ValueError(f"{model_name} artifact must contain a fitted pipeline")
    if not hasattr(model, "predict_proba"):
        raise ValueError(f"{model_name} model must support predict_proba")
    if set(str(label) for label in model.classes_) != set(expected_classes):
        raise ValueError(f"{model_name} model has unexpected classes")

    return model


def predicted_class_confidences(
    model: Pipeline,
    messages: Sequence[str],
) -> tuple[list[str], list[float]]:
    predictions = model.predict(messages)
    probability_rows = model.predict_proba(messages)
    model_classes = [str(label) for label in model.classes_]
    predicted_labels: list[str] = []
    confidences: list[float] = []

    for prediction, probabilities in zip(predictions, probability_rows, strict=True):
        predicted_label = str(prediction)
        predicted_index = model_classes.index(predicted_label)
        predicted_labels.append(predicted_label)
        confidences.append(float(probabilities[predicted_index]))

    return predicted_labels, confidences


def analyze_model(
    model: Pipeline,
    messages: Sequence[str],
    actual_labels: Sequence[str],
) -> list[ConfidencePrediction]:
    predicted_labels, confidences = predicted_class_confidences(model, messages)

    return [
        ConfidencePrediction(message, actual, predicted, confidence)
        for message, actual, predicted, confidence in zip(
            messages,
            actual_labels,
            predicted_labels,
            confidences,
            strict=True,
        )
    ]


def summarize_confidence(
    results: Sequence[ConfidencePrediction],
) -> ConfidenceSummary:
    correct_confidences = [result.confidence for result in results if result.is_correct]
    incorrect_confidences = [
        result.confidence for result in results if not result.is_correct
    ]

    return ConfidenceSummary(
        correct_count=len(correct_confidences),
        incorrect_count=len(incorrect_confidences),
        average_correct_confidence=(
            mean(correct_confidences) if correct_confidences else None
        ),
        average_incorrect_confidence=(
            mean(incorrect_confidences) if incorrect_confidences else None
        ),
        minimum_correct_confidence=(
            min(correct_confidences) if correct_confidences else None
        ),
        maximum_correct_confidence=(
            max(correct_confidences) if correct_confidences else None
        ),
    )


def evaluate_thresholds(
    results: Sequence[ConfidencePrediction],
    mistake_prefix: str,
) -> list[ThresholdResult]:
    mistakes = [result for result in results if not result.is_correct]
    mistake_ids = {
        result: f"{mistake_prefix}{index}"
        for index, result in enumerate(mistakes, start=1)
    }
    threshold_results: list[ThresholdResult] = []

    for threshold in THRESHOLDS:
        reviewed = [result for result in results if result.confidence < threshold]
        caught = [result for result in reviewed if not result.is_correct]
        threshold_results.append(
            ThresholdResult(
                threshold=threshold,
                total_reviewed=len(reviewed),
                incorrect_caught=len(caught),
                incorrect_passed=len(mistakes) - len(caught),
                correct_reviewed=sum(result.is_correct for result in reviewed),
                caught_mistake_ids=tuple(mistake_ids[result] for result in caught),
            )
        )

    return threshold_results


def evaluate_combined_thresholds(
    target_results: Sequence[ConfidencePrediction],
    other_model_confidences: Sequence[float],
) -> list[CombinedThresholdResult]:
    if len(target_results) != len(other_model_confidences):
        raise ValueError("Each message must have confidence from both models")

    mistake_count = sum(not result.is_correct for result in target_results)
    combined_results: list[CombinedThresholdResult] = []

    for threshold in THRESHOLDS:
        reviewed = [
            result
            for result, other_confidence in zip(
                target_results,
                other_model_confidences,
                strict=True,
            )
            if result.confidence < threshold or other_confidence < threshold
        ]
        caught = sum(not result.is_correct for result in reviewed)
        combined_results.append(
            CombinedThresholdResult(
                threshold=threshold,
                total_reviewed=len(reviewed),
                target_mistakes_caught=caught,
                target_mistakes_passed=mistake_count - caught,
                correct_target_predictions_reviewed=sum(
                    result.is_correct for result in reviewed
                ),
            )
        )

    return combined_results


def print_predictions(
    title: str,
    results: Sequence[ConfidencePrediction],
) -> None:
    print(f"\n{title} — every prediction:")

    for index, result in enumerate(results, start=1):
        outcome = "CORRECT" if result.is_correct else "INCORRECT"
        print(
            f"{index:02}. [{outcome}] actual={result.actual_label} "
            f"predicted={result.predicted_label} confidence={result.confidence:.6f}"
        )
        print(f"    {result.message}")


def print_summary(
    title: str,
    results: Sequence[ConfidencePrediction],
    summary: ConfidenceSummary,
    mistake_prefix: str,
) -> None:
    print(f"\n{title} — confidence summary:")
    print(f"Correct: {summary.correct_count}/{len(results)}")
    print(f"Incorrect: {summary.incorrect_count}/{len(results)}")
    average_correct = (
        f"{summary.average_correct_confidence:.6f}"
        if summary.average_correct_confidence is not None
        else "— (no correct predictions)"
    )
    average_incorrect = (
        f"{summary.average_incorrect_confidence:.6f}"
        if summary.average_incorrect_confidence is not None
        else "— (no incorrect predictions)"
    )
    correct_range = (
        f"{summary.minimum_correct_confidence:.6f}–"
        f"{summary.maximum_correct_confidence:.6f}"
        if summary.minimum_correct_confidence is not None
        and summary.maximum_correct_confidence is not None
        else "— (no correct predictions)"
    )
    print(f"Average confidence (correct): {average_correct}")
    print(f"Average confidence (incorrect): {average_incorrect}")
    print(f"Correct confidence range: {correct_range}")

    mistakes = [result for result in results if not result.is_correct]
    print(f"\n{title} — mistakes ({len(mistakes)}):")
    for index, result in enumerate(mistakes, start=1):
        print(
            f"{mistake_prefix}{index}: actual={result.actual_label} "
            f"predicted={result.predicted_label} confidence={result.confidence:.6f}"
        )
        print(f"    {result.message}")


def print_threshold_table(
    title: str,
    threshold_results: Sequence[ThresholdResult],
) -> None:
    print(f"\n{title} — manual review when confidence < threshold:")
    print("threshold  reviewed  caught  passed  correct reviewed  caught mistake IDs")
    for result in threshold_results:
        caught_ids = ",".join(result.caught_mistake_ids) or "—"
        print(
            f"{result.threshold:>9.2f}"
            f"{result.total_reviewed:>10}"
            f"{result.incorrect_caught:>8}"
            f"{result.incorrect_passed:>8}"
            f"{result.correct_reviewed:>18}  {caught_ids}"
        )


def print_combined_table(
    intent_results: Sequence[CombinedThresholdResult],
    priority_results: Sequence[CombinedThresholdResult],
) -> None:
    print("\nBoth classifiers must meet threshold (review if either is below it):")
    print(
        "threshold  intent set: reviewed/caught/passed/correct reviewed"
        "  priority set: reviewed/caught/passed/correct reviewed"
    )
    for intent, priority in zip(intent_results, priority_results, strict=True):
        print(
            f"{intent.threshold:>9.2f}"
            f"  {intent.total_reviewed:>2}/30, "
            f"{intent.target_mistakes_caught}/{intent.target_mistakes_passed}/"
            f"{intent.correct_target_predictions_reviewed}"
            f"                         {priority.total_reviewed:>2}/40, "
            f"{priority.target_mistakes_caught}/{priority.target_mistakes_passed}/"
            f"{priority.correct_target_predictions_reviewed}"
        )

    print(
        "\nCombined-table caught/passed/correct values refer only to the labeled target "
        "for that dataset (intent on the intent benchmark; priority on the priority "
        "challenge). The datasets do not provide the other label, so joint prediction "
        "accuracy cannot be calculated."
    )


def run_confidence_analysis() -> None:
    intent_model = load_saved_pipeline(INTENT_MODEL_PATH, INTENTS, "Intent")
    priority_model = load_saved_pipeline(PRIORITY_MODEL_PATH, PRIORITIES, "Priority")

    intent_messages, intent_labels = load_dataset(INTENT_BENCHMARK_PATH)
    priority_messages, priority_labels = load_priority_dataset(
        CHALLENGE_BENCHMARK_PATH
    )

    intent_results = analyze_model(intent_model, intent_messages, intent_labels)
    priority_results = analyze_model(
        priority_model,
        priority_messages,
        priority_labels,
    )

    print_predictions("Intent frozen benchmark", intent_results)
    print_summary(
        "Intent frozen benchmark",
        intent_results,
        summarize_confidence(intent_results),
        "I",
    )
    print_threshold_table(
        "Intent frozen benchmark",
        evaluate_thresholds(intent_results, "I"),
    )

    print_predictions("Priority independent challenge", priority_results)
    print_summary(
        "Priority independent challenge",
        priority_results,
        summarize_confidence(priority_results),
        "P",
    )
    print_threshold_table(
        "Priority independent challenge",
        evaluate_thresholds(priority_results, "P"),
    )

    _, priority_confidences_on_intent_messages = predicted_class_confidences(
        priority_model,
        intent_messages,
    )
    _, intent_confidences_on_priority_messages = predicted_class_confidences(
        intent_model,
        priority_messages,
    )
    print_combined_table(
        evaluate_combined_thresholds(
            intent_results,
            priority_confidences_on_intent_messages,
        ),
        evaluate_combined_thresholds(
            priority_results,
            intent_confidences_on_priority_messages,
        ),
    )


if __name__ == "__main__":
    run_confidence_analysis()
