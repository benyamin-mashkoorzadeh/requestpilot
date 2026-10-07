import csv
from collections import Counter
from dataclasses import dataclass
from pathlib import Path

from sklearn.metrics import accuracy_score, classification_report, confusion_matrix

from confidence_analysis import analyze_model, load_saved_pipeline
from train_model import DEFAULT_MODEL_PATH as INTENT_MODEL_PATH
from train_model import INTENTS
from train_priority_model import DEFAULT_MODEL_PATH as PRIORITY_MODEL_PATH
from train_priority_model import PRIORITIES


ROBUSTNESS_BENCHMARK_PATH = (
    Path(__file__).resolve().parent / "data" / "robustness_benchmark.csv"
)


@dataclass(frozen=True)
class RobustnessExample:
    message: str
    intent: str
    priority: str


def load_robustness_benchmark(
    dataset_path: Path = ROBUSTNESS_BENCHMARK_PATH,
) -> list[RobustnessExample]:
    examples: list[RobustnessExample] = []

    with dataset_path.open(encoding="utf-8", newline="") as dataset_file:
        reader = csv.DictReader(dataset_file)

        if reader.fieldnames != ["text", "intent", "priority"]:
            raise ValueError(
                "Robustness columns must be exactly: text,intent,priority"
            )

        for row_number, row in enumerate(reader, start=2):
            message = row["text"].strip()
            intent = row["intent"].strip()
            priority = row["priority"].strip()

            if not message:
                raise ValueError(f"Robustness row {row_number} has an empty message")
            if intent not in INTENTS:
                raise ValueError(f"Robustness row {row_number} has an invalid intent")
            if priority not in PRIORITIES:
                raise ValueError(f"Robustness row {row_number} has an invalid priority")

            examples.append(RobustnessExample(message, intent, priority))

    return examples


def print_evaluation(
    title: str,
    expected_labels: list[str],
    actual_labels: list[str],
    predicted_labels: list[str],
    confidences: list[float],
    messages: list[str],
) -> None:
    accuracy = accuracy_score(actual_labels, predicted_labels)
    print(f"\n{title}")
    print(f"Correct: {sum(a == p for a, p in zip(actual_labels, predicted_labels, strict=True))}/{len(actual_labels)}")
    print(f"Accuracy: {accuracy:.3f}")
    print(
        classification_report(
            actual_labels,
            predicted_labels,
            labels=expected_labels,
            digits=3,
            zero_division=0,
        )
    )

    mistakes = [
        (message, actual, predicted, confidence)
        for message, actual, predicted, confidence in zip(
            messages,
            actual_labels,
            predicted_labels,
            confidences,
            strict=True,
        )
        if actual != predicted
    ]
    print(f"Mistakes ({len(mistakes)}):")
    for index, (message, actual, predicted, confidence) in enumerate(
        mistakes, start=1
    ):
        print(
            f"{index:02}. actual={actual} predicted={predicted} "
            f"confidence={confidence:.6f}"
        )
        print(f"    {message}")

    matrix = confusion_matrix(
        actual_labels,
        predicted_labels,
        labels=expected_labels,
    )
    print("Confusion matrix (rows = actual, columns = predicted):")
    print("labels:", " ".join(expected_labels))
    for label, row in zip(expected_labels, matrix.tolist(), strict=True):
        print(label, *row)


def evaluate_robustness() -> None:
    examples = load_robustness_benchmark()
    messages = [example.message for example in examples]
    actual_intents = [example.intent for example in examples]
    actual_priorities = [example.priority for example in examples]

    intent_model = load_saved_pipeline(INTENT_MODEL_PATH, INTENTS, "Intent")
    priority_model = load_saved_pipeline(PRIORITY_MODEL_PATH, PRIORITIES, "Priority")
    intent_results = analyze_model(intent_model, messages, actual_intents)
    priority_results = analyze_model(priority_model, messages, actual_priorities)

    print(f"Robustness examples: {len(examples)}")
    print(f"Examples per intent: {dict(Counter(actual_intents))}")
    print(f"Examples per priority: {dict(Counter(actual_priorities))}")
    print_evaluation(
        "Intent robustness evaluation",
        list(INTENTS),
        actual_intents,
        [result.predicted_label for result in intent_results],
        [result.confidence for result in intent_results],
        messages,
    )
    print_evaluation(
        "Priority robustness evaluation",
        list(PRIORITIES),
        actual_priorities,
        [result.predicted_label for result in priority_results],
        [result.confidence for result in priority_results],
        messages,
    )


if __name__ == "__main__":
    evaluate_robustness()
