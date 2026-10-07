from functools import lru_cache
from pathlib import Path
from typing import Literal

import joblib
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, ConfigDict, Field
from sklearn.pipeline import Pipeline


SUPPORTED_INTENTS = frozenset({"sales", "support", "billing", "refund", "cancellation"})
SUPPORTED_PRIORITIES = frozenset({"low", "normal", "high", "urgent"})
MODELS_DIR = Path(__file__).resolve().parents[1] / "models"
INTENT_MODEL_PATH = MODELS_DIR / "intent_classifier.joblib"
PRIORITY_MODEL_PATH = MODELS_DIR / "priority_classifier.joblib"


class ModelUnavailableError(RuntimeError):
    pass


class HealthResponse(BaseModel):
    status: Literal["healthy"]


class AnalyzeRequest(BaseModel):
    model_config = ConfigDict(str_strip_whitespace=True, extra="forbid")

    message: str = Field(min_length=1, max_length=5_000)


class AnalyzeResponse(BaseModel):
    intent: str
    intent_confidence: float = Field(ge=0.0, le=1.0)
    priority: str
    priority_confidence: float = Field(ge=0.0, le=1.0)


app = FastAPI(
    title="RequestPilot AI Service",
    description="RequestPilot service boundary for inquiry intent and priority classification.",
    version="0.3.0",
)


def load_validated_model(
    model_path: Path,
    expected_classes: frozenset[str],
    model_name: str,
) -> Pipeline:
    try:
        model = joblib.load(model_path)
    except Exception as error:
        raise ModelUnavailableError(
            f"{model_name} classification model is unavailable."
        ) from error

    if not isinstance(model, Pipeline):
        raise ModelUnavailableError(f"{model_name} classification model is unavailable.")

    try:
        model_classes = {str(model_class) for model_class in model.classes_}
    except AttributeError as error:
        raise ModelUnavailableError(
            f"{model_name} classification model is unavailable."
        ) from error

    if model_classes != expected_classes or not hasattr(model, "predict_proba"):
        raise ModelUnavailableError(f"{model_name} classification model is unavailable.")

    return model


@lru_cache(maxsize=1)
def load_intent_model() -> Pipeline:
    return load_validated_model(INTENT_MODEL_PATH, SUPPORTED_INTENTS, "Intent")


@lru_cache(maxsize=1)
def load_priority_model() -> Pipeline:
    return load_validated_model(PRIORITY_MODEL_PATH, SUPPORTED_PRIORITIES, "Priority")


def get_intent_model() -> Pipeline:
    try:
        return load_intent_model()
    except ModelUnavailableError as error:
        raise HTTPException(status_code=503, detail=str(error)) from error


def get_priority_model() -> Pipeline:
    try:
        return load_priority_model()
    except ModelUnavailableError as error:
        raise HTTPException(status_code=503, detail=str(error)) from error


def predict_with_confidence(model: Pipeline, message: str) -> tuple[str, float]:
    prediction = str(model.predict([message])[0])
    probabilities = model.predict_proba([message])[0]
    model_classes = [str(model_class) for model_class in model.classes_]
    predicted_index = model_classes.index(prediction)

    return prediction, float(probabilities[predicted_index])


@app.get("/health", response_model=HealthResponse)
def health() -> HealthResponse:
    return HealthResponse(status="healthy")


@app.post("/analyze", response_model=AnalyzeResponse)
def analyze(request: AnalyzeRequest) -> AnalyzeResponse:
    intent_model = get_intent_model()
    priority_model = get_priority_model()
    predicted_intent, intent_confidence = predict_with_confidence(
        intent_model,
        request.message,
    )
    predicted_priority, priority_confidence = predict_with_confidence(
        priority_model,
        request.message,
    )

    return AnalyzeResponse(
        intent=predicted_intent,
        intent_confidence=intent_confidence,
        priority=predicted_priority,
        priority_confidence=priority_confidence,
    )
