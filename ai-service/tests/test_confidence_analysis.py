from confidence_analysis import (
    THRESHOLDS,
    ConfidencePrediction,
    analyze_model,
    evaluate_combined_thresholds,
    evaluate_thresholds,
    load_saved_pipeline,
    predicted_class_confidences,
    summarize_confidence,
)
from evaluate_priority_challenge import CHALLENGE_BENCHMARK_PATH
from train_model import (
    DEFAULT_BENCHMARK_DATASET_PATH,
    DEFAULT_MODEL_PATH as INTENT_MODEL_PATH,
    INTENTS,
    load_dataset,
)
from train_priority_model import (
    DEFAULT_MODEL_PATH as PRIORITY_MODEL_PATH,
    PRIORITIES,
    load_priority_dataset,
)


def test_threshold_analysis_uses_strictly_below_and_accounts_for_every_result() -> None:
    results = [
        ConfidencePrediction("correct boundary", "a", "a", 0.30),
        ConfidencePrediction("mistake boundary", "a", "b", 0.30),
        ConfidencePrediction("lower mistake", "a", "b", 0.20),
        ConfidencePrediction("higher correct", "a", "a", 0.50),
    ]

    threshold_results = evaluate_thresholds(results, "M")
    at_thirty = threshold_results[THRESHOLDS.index(0.30)]
    at_forty = threshold_results[THRESHOLDS.index(0.40)]

    assert at_thirty.total_reviewed == 1
    assert at_thirty.incorrect_caught == 1
    assert at_thirty.incorrect_passed == 1
    assert at_thirty.correct_reviewed == 0
    assert at_thirty.caught_mistake_ids == ("M2",)

    assert at_forty.total_reviewed == 3
    assert at_forty.incorrect_caught == 2
    assert at_forty.incorrect_passed == 0
    assert at_forty.correct_reviewed == 1
    assert at_forty.caught_mistake_ids == ("M1", "M2")


def test_combined_gate_reviews_when_either_model_is_below_threshold() -> None:
    target_results = [
        ConfidencePrediction("correct", "a", "a", 0.70),
        ConfidencePrediction("mistake", "a", "b", 0.20),
    ]
    other_model_confidences = [0.10, 0.80]

    combined_results = evaluate_combined_thresholds(
        target_results,
        other_model_confidences,
    )
    at_fifty = combined_results[THRESHOLDS.index(0.50)]

    assert at_fifty.total_reviewed == 2
    assert at_fifty.target_mistakes_caught == 1
    assert at_fifty.target_mistakes_passed == 0
    assert at_fifty.correct_target_predictions_reviewed == 1


def test_confidence_summary_handles_a_benchmark_with_no_mistakes() -> None:
    summary = summarize_confidence(
        [
            ConfidencePrediction("first", "a", "a", 0.60),
            ConfidencePrediction("second", "b", "b", 0.80),
        ]
    )

    assert summary.correct_count == 2
    assert summary.incorrect_count == 0
    assert summary.average_correct_confidence == 0.70
    assert summary.average_incorrect_confidence is None
    assert summary.minimum_correct_confidence == 0.60
    assert summary.maximum_correct_confidence == 0.80


def test_saved_models_analyze_the_frozen_evaluation_datasets() -> None:
    intent_model = load_saved_pipeline(INTENT_MODEL_PATH, INTENTS, "Intent")
    priority_model = load_saved_pipeline(PRIORITY_MODEL_PATH, PRIORITIES, "Priority")
    intent_messages, intent_labels = load_dataset(DEFAULT_BENCHMARK_DATASET_PATH)
    priority_messages, priority_labels = load_priority_dataset(
        CHALLENGE_BENCHMARK_PATH
    )

    intent_results = analyze_model(intent_model, intent_messages, intent_labels)
    priority_results = analyze_model(
        priority_model,
        priority_messages,
        priority_labels,
    )
    _, intent_confidences = predicted_class_confidences(
        intent_model,
        priority_messages,
    )
    _, priority_confidences = predicted_class_confidences(
        priority_model,
        intent_messages,
    )

    assert len(intent_results) == 30
    assert len(priority_results) == 40
    assert len(intent_confidences) == 40
    assert len(priority_confidences) == 30
    assert all(0 <= result.confidence <= 1 for result in intent_results)
    assert all(0 <= result.confidence <= 1 for result in priority_results)
