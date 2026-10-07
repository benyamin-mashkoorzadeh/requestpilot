from collections import Counter
from hashlib import sha256

from evaluate_priority_challenge import CHALLENGE_BENCHMARK_PATH
from train_priority_model import (
    PRIORITIES,
    load_priority_dataset,
    load_priority_training_and_benchmark,
)


def test_priority_challenge_is_balanced_valid_and_unique() -> None:
    challenge_messages, challenge_priorities = load_priority_dataset(
        CHALLENGE_BENCHMARK_PATH
    )

    assert len(challenge_messages) == 40
    assert len(set(challenge_messages)) == 40
    assert Counter(challenge_priorities) == Counter(
        {priority: 10 for priority in PRIORITIES}
    )
    assert set(challenge_priorities) == set(PRIORITIES)


def test_priority_challenge_has_no_exact_dataset_overlap() -> None:
    challenge_messages, _ = load_priority_dataset(CHALLENGE_BENCHMARK_PATH)
    training_messages, _, benchmark_messages, _ = (
        load_priority_training_and_benchmark()
    )

    assert set(challenge_messages).isdisjoint(training_messages)
    assert set(challenge_messages).isdisjoint(benchmark_messages)


def test_priority_challenge_is_frozen() -> None:
    challenge_digest = sha256(CHALLENGE_BENCHMARK_PATH.read_bytes()).hexdigest()

    assert challenge_digest == (
        "ed3a4e1e30aa5ff2ddfb1877ecc85f03287cf9199713342dcb6dae05dc24b55d"
    )
