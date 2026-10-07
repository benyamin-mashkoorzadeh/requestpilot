from collections import Counter

import joblib
import pytest
from fastapi.testclient import TestClient

from app import main
from app.main import (
    AnalyzeResponse,
    SUPPORTED_INTENTS,
    SUPPORTED_PRIORITIES,
    app,
    load_intent_model,
    load_priority_model,
    predict_with_confidence,
)


client = TestClient(app)


@pytest.fixture(autouse=True)
def clear_model_caches():
    load_intent_model.cache_clear()
    load_priority_model.cache_clear()
    yield
    load_intent_model.cache_clear()
    load_priority_model.cache_clear()


def test_health_endpoint() -> None:
    response = client.get("/health")

    assert response.status_code == 200
    assert response.json() == {"status": "healthy"}


def test_service_metadata_uses_requestpilot_name() -> None:
    assert app.title == "RequestPilot AI Service"
    assert "RequestPilot" in app.description


def test_analyze_uses_both_trained_models_and_their_confidences() -> None:
    message = "Our whole team is locked out and customer work has stopped."
    expected_intent, expected_intent_confidence = predict_with_confidence(
        load_intent_model(),
        message,
    )
    expected_priority, expected_priority_confidence = predict_with_confidence(
        load_priority_model(),
        message,
    )

    response = client.post("/analyze", json={"message": message})

    assert response.status_code == 200
    assert response.json() == {
        "intent": expected_intent,
        "intent_confidence": expected_intent_confidence,
        "priority": expected_priority,
        "priority_confidence": expected_priority_confidence,
    }


@pytest.mark.parametrize(
    ("message", "expected_intent"),
    [
        ("We need pricing for a team of 50 employees.", "sales"),
        ("I cannot sign in to my account after resetting my password.", "support"),
        ("Please send a copy of our latest invoice.", "billing"),
        ("I was charged by mistake and need the payment returned.", "refund"),
        ("Please stop the subscription from renewing next month.", "cancellation"),
    ],
)
def test_representative_intent_messages_can_be_analyzed(
    message: str,
    expected_intent: str,
) -> None:
    response = client.post("/analyze", json={"message": message})
    result = response.json()

    assert response.status_code == 200
    assert result["intent"] == expected_intent
    assert result["intent"] in SUPPORTED_INTENTS
    assert result["priority"] in SUPPORTED_PRIORITIES
    assert 0.0 <= result["intent_confidence"] <= 1.0
    assert 0.0 <= result["priority_confidence"] <= 1.0


@pytest.mark.parametrize(
    ("message", "expected_priority"),
    [
        (
            "This is only for planning next year; nothing currently depends on it.",
            "low",
        ),
        (
            "Several staff are blocked, but another team can cover until tomorrow.",
            "high",
        ),
        (
            "Every employee is locked out and all live operations have stopped.",
            "urgent",
        ),
    ],
)
def test_priority_is_predicted_instead_of_always_normal(
    message: str,
    expected_priority: str,
) -> None:
    response = client.post("/analyze", json={"message": message})

    assert response.status_code == 200
    assert response.json()["priority"] == expected_priority
    assert response.json()["priority"] != "normal"


def test_analyze_rejects_empty_or_missing_messages() -> None:
    invalid_payloads = [
        {"message": ""},
        {"message": "   "},
        {},
    ]

    for payload in invalid_payloads:
        response = client.post("/analyze", json=payload)

        assert response.status_code == 422


def test_analyze_response_matches_the_explicit_schema() -> None:
    response = client.post("/analyze", json={"message": "Please review my request."})
    result = AnalyzeResponse.model_validate(response.json())
    expected_fields = {
        "intent",
        "intent_confidence",
        "priority",
        "priority_confidence",
    }

    assert set(result.model_dump()) == expected_fields
    assert "confidence" not in response.json()
    assert "category" not in response.json()
    assert "suggested_action" not in response.json()

    openapi = client.get("/openapi.json").json()
    response_schema = openapi["components"]["schemas"]["AnalyzeResponse"]

    assert set(response_schema["required"]) == expected_fields


def test_intent_and_priority_artifacts_load_separately() -> None:
    intent_model = load_intent_model()
    priority_model = load_priority_model()

    assert intent_model is not priority_model
    assert {str(label) for label in intent_model.classes_} == SUPPORTED_INTENTS
    assert {str(label) for label in priority_model.classes_} == SUPPORTED_PRIORITIES
    assert main.INTENT_MODEL_PATH != main.PRIORITY_MODEL_PATH


def test_both_models_are_loaded_from_disk_only_once(monkeypatch) -> None:
    real_joblib_load = main.joblib.load
    load_counts: Counter = Counter()

    def counting_load(model_path):
        load_counts[model_path] += 1
        return real_joblib_load(model_path)

    monkeypatch.setattr(main.joblib, "load", counting_load)

    assert load_intent_model() is load_intent_model()
    assert load_priority_model() is load_priority_model()
    assert load_counts == Counter(
        {
            main.INTENT_MODEL_PATH: 1,
            main.PRIORITY_MODEL_PATH: 1,
        }
    )


def test_missing_intent_artifact_returns_service_unavailable(
    monkeypatch,
    tmp_path,
) -> None:
    monkeypatch.setattr(
        main,
        "INTENT_MODEL_PATH",
        tmp_path / "missing-intent-model.joblib",
    )

    response = client.post(
        "/analyze",
        json={"message": "We need pricing for a larger team."},
    )

    assert response.status_code == 503
    assert response.json() == {
        "detail": "Intent classification model is unavailable."
    }


def test_missing_priority_artifact_returns_service_unavailable(
    monkeypatch,
    tmp_path,
) -> None:
    monkeypatch.setattr(
        main,
        "PRIORITY_MODEL_PATH",
        tmp_path / "missing-priority-model.joblib",
    )

    response = client.post(
        "/analyze",
        json={"message": "Everyone is locked out and cannot work."},
    )

    assert response.status_code == 503
    assert response.json() == {
        "detail": "Priority classification model is unavailable."
    }


def test_invalid_artifact_returns_service_unavailable(monkeypatch, tmp_path) -> None:
    invalid_model_path = tmp_path / "invalid-priority-model.joblib"
    joblib.dump({"not": "a fitted pipeline"}, invalid_model_path)
    monkeypatch.setattr(main, "PRIORITY_MODEL_PATH", invalid_model_path)

    response = client.post(
        "/analyze",
        json={"message": "Please review this account issue."},
    )

    assert response.status_code == 503
    assert response.json() == {
        "detail": "Priority classification model is unavailable."
    }
