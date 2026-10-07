# RequestPilot AI Service

This is the analysis-only Python service boundary for RequestPilot. FastAPI uses two separately trained scikit-learn pipelines to predict an inquiry's intent and priority. Business decisions and actions belong to Laravel.

## Setup

From the repository root:

```bash
cd ai-service
python3 -m venv .venv
source .venv/bin/activate
python -m pip install --upgrade pip
python -m pip install -r requirements-dev.txt
```

On Windows PowerShell, activate the environment with:

```powershell
.venv\Scripts\Activate.ps1
```

## Run the service

Both `models/intent_classifier.joblib` and `models/priority_classifier.joblib` must exist before starting the service. The API never trains during a request.

```bash
uvicorn app.main:app --reload --port 8001
```

The service is then available at `http://127.0.0.1:8001`. FastAPI's interactive API documentation is available at `http://127.0.0.1:8001/docs`.

Check service health:

```bash
curl http://127.0.0.1:8001/health
```

Call the analysis endpoint:

```bash
curl -X POST http://127.0.0.1:8001/analyze \
    -H "Content-Type: application/json" \
    -d '{"message":"I need help with my account."}'
```

The first valid `/analyze` request loads `models/intent_classifier.joblib` and `models/priority_classifier.joblib` independently. Both pipelines are cached in memory and reused by later requests. If either artifact is missing, corrupt, incompatible, or contains unexpected classes, `/analyze` returns HTTP `503 Service Unavailable`; `/health` remains available.

The response contract is:

```json
{
    "intent": "support",
    "intent_confidence": 0.82,
    "priority": "high",
    "priority_confidence": 0.76
}
```

Each confidence comes from the corresponding model's predicted-class probability. The Python response contains no routing, escalation, or automation decision.

## Priority dataset

Priority classification has its own data, training script, and model artifact. The original 400-example dataset was split once with `random_state=42`, a 20% benchmark size, and stratification by priority:

- `data/priority_training.csv`: 1,340 training examples, with 335 per priority.
- `data/priority_benchmark.csv`: 80 frozen benchmark examples, with 20 per priority.

The four labels mean:

- `low`: informational or otherwise non-urgent work with no meaningful time pressure.
- `normal`: an ordinary customer request that should follow the standard handling process.
- `high`: meaningful customer or business impact that warrants faster attention.
- `urgent`: immediate or severe impact, such as a widespread outage, inability to work, a security-sensitive incident, or an imminent critical deadline.

Priority is intentionally separate from intent. Sales, support, billing, refund, and cancellation messages can appear at different priority levels depending on timing and impact. Train and evaluate the separate model with:

```bash
python train_priority_model.py
```

This writes `models/priority_classifier.joblib`. The priority trainer and API use message text only; the priority model does not receive the intent prediction as a feature.

### Independent priority challenge

`data/priority_challenge_benchmark.csv` contains 40 independently authored evaluation examples, with 10 per priority. It is never loaded by the priority training workflow. Evaluate the existing saved model without retraining it with:

```bash
python evaluate_priority_challenge.py
```

The evaluator prints every prediction, a mistakes-only section, predicted-class confidence, the lowest-confidence correct predictions, per-priority metrics, and a confusion matrix. The initial 320-example model produced 29/40 correct (`0.725` accuracy). Expanding training to 1,200 examples produced 34/40 correct (`0.850` accuracy). A 1,300-example iteration produced 36/40 (`0.900` accuracy). The current 1,340-example dataset adds focused impact-boundary cases and produces 39/40 correct (`0.975` accuracy), while the structurally related frozen benchmark remains 80/80.

## Train the intent classifier

The model data is deliberately separated into two human-readable CSV files:

- `data/training.csv` contains 1,400 training examples: 280 for each of `sales`, `support`, `billing`, `refund`, and `cancellation`.
- `data/benchmark.csv` contains the 30 examples held out by the original Step 3B/3D split: 6 per intent. This benchmark is fixed and is never used for fitting the model.

The training loader rejects any exact message shared by the two files. This guards against accidentally teaching the model an answer that it will later be evaluated on.

With the virtual environment active, train and evaluate the model:

```bash
python train_model.py
```

The script trains on every row in `data/training.csv` and evaluates only against the unchanged `data/benchmark.csv`. It does not re-split the expanded dataset. It prints accuracy and per-intent precision, recall, and F1 scores, then writes the fitted pipeline to:

```text
models/intent_classifier.joblib
```

The artifact is generated locally and ignored by Git because it can be reproduced from the dataset and training script. FastAPI loads this artifact for inference but never retrains it while handling requests.

The same command also prints an error-analysis report containing all 30 held-out predictions, a mistakes-only section, each predicted class confidence, and a confusion matrix whose rows are actual intents and columns are predicted intents.

### Shortcut robustness evaluation

`data/robustness_benchmark.csv` is a separate 20-example evaluation-only set. It is balanced across both intent and priority labels and includes misleading urgency words, calm severe incidents, cross-intent cancellation/refund language, and severe-sounding issues with working fallbacks. It is never loaded by either training workflow.

Evaluate both saved pipelines against it without retraining:

```bash
python evaluate_robustness.py
```

The evaluator prints accuracy, per-class precision/recall/F1, every mistake with predicted-class confidence, and confusion matrices for both classifiers. Keep its results separate from the frozen intent and priority benchmarks because it is a small diagnostic probe rather than a replacement benchmark.

### How the model works

**TF-IDF** turns the message text into numbers. It gives more weight to words and short phrases that are useful in a message but not common across every message. Common English filler words are removed so the model can focus on more informative language.

**Logistic Regression** learns a set of weights from those TF-IDF features. For a new message, those weights produce a score for each intent, and the intent with the strongest score becomes the prediction. There are no hand-written keyword routing rules.

Separating training data from the frozen benchmark keeps evaluation honest and comparable over time. The model learns from the training file only. The benchmark remains unseen during fitting, so a metric change reflects the training-data change against the same questions rather than an easier or harder random split.

### Inference

Inference means using the already-fitted pipelines on a new message. The API passes the original message independently to both models. For each model, `pipeline.predict()` returns the most likely label and `pipeline.predict_proba()` returns one probability per entry in `pipeline.classes_`. The API finds the predicted label's class position and returns the probability at that same position as `intent_confidence` or `priority_confidence`.

## Analyze confidence

Evaluate how well predicted-class confidence separates correct predictions from mistakes without training or changing either model:

```bash
python confidence_analysis.py
```

The script loads the saved intent and priority artifacts, evaluates the frozen intent benchmark and independent priority challenge benchmark, and reports results for several hypothetical manual-review thresholds. It also simulates requiring both classifiers to meet each threshold on each dataset. Because the two datasets do not contain paired intent and priority labels, that combined view reports review volume and target-specific error capture—not joint model accuracy.

## Run the tests

With the virtual environment active:

```bash
python -m pytest
```
