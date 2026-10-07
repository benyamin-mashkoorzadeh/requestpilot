from collections import Counter

from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

from evaluate_robustness import (
    ROBUSTNESS_BENCHMARK_PATH,
    load_robustness_benchmark,
)
from evaluate_priority_challenge import CHALLENGE_BENCHMARK_PATH
from train_model import (
    DEFAULT_BENCHMARK_DATASET_PATH as INTENT_BENCHMARK_PATH,
)
from train_model import DEFAULT_TRAINING_DATASET_PATH as INTENT_TRAINING_PATH
from train_model import INTENTS, load_dataset
from train_priority_model import (
    DEFAULT_BENCHMARK_DATASET_PATH as PRIORITY_BENCHMARK_PATH,
)
from train_priority_model import (
    DEFAULT_TRAINING_DATASET_PATH as PRIORITY_TRAINING_PATH,
)
from train_priority_model import PRIORITIES, load_priority_dataset


def test_robustness_benchmark_is_balanced_valid_and_unique() -> None:
    examples = load_robustness_benchmark()
    messages = [example.message for example in examples]

    assert ROBUSTNESS_BENCHMARK_PATH.exists()
    assert len(examples) == 20
    assert len(set(messages)) == 20
    assert Counter(example.intent for example in examples) == Counter(
        {intent: 4 for intent in INTENTS}
    )
    assert Counter(example.priority for example in examples) == Counter(
        {priority: 5 for priority in PRIORITIES}
    )


def test_robustness_benchmark_has_no_exact_overlap_with_existing_data() -> None:
    robustness_messages = {
        example.message for example in load_robustness_benchmark()
    }
    intent_training, _ = load_dataset(INTENT_TRAINING_PATH)
    intent_benchmark, _ = load_dataset(INTENT_BENCHMARK_PATH)
    priority_training, _ = load_priority_dataset(PRIORITY_TRAINING_PATH)
    priority_benchmark, _ = load_priority_dataset(PRIORITY_BENCHMARK_PATH)
    challenge_benchmark, _ = load_priority_dataset(CHALLENGE_BENCHMARK_PATH)

    existing_messages = set(
        intent_training
        + intent_benchmark
        + priority_training
        + priority_benchmark
        + challenge_benchmark
    )

    assert robustness_messages.isdisjoint(existing_messages)


def test_robustness_benchmark_has_no_near_duplicates_in_existing_data() -> None:
    robustness_messages = [
        example.message for example in load_robustness_benchmark()
    ]
    intent_training, _ = load_dataset(INTENT_TRAINING_PATH)
    intent_benchmark, _ = load_dataset(INTENT_BENCHMARK_PATH)
    priority_training, _ = load_priority_dataset(PRIORITY_TRAINING_PATH)
    priority_benchmark, _ = load_priority_dataset(PRIORITY_BENCHMARK_PATH)
    challenge_benchmark, _ = load_priority_dataset(CHALLENGE_BENCHMARK_PATH)
    existing_messages = (
        intent_training
        + intent_benchmark
        + priority_training
        + priority_benchmark
        + challenge_benchmark
    )

    features = TfidfVectorizer(
        analyzer="char_wb",
        ngram_range=(3, 5),
    ).fit_transform(robustness_messages + existing_messages)
    similarities = cosine_similarity(
        features[: len(robustness_messages)],
        features[len(robustness_messages) :],
    )

    assert similarities.max() < 0.70
