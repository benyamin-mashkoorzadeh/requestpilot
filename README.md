# RequestPilot

RequestPilot is a portfolio-ready customer inquiry operations application built with Laravel, Livewire, Blade, Tailwind CSS, and a separate FastAPI machine-learning service.

Customers submit an inquiry through Laravel. The Python service independently predicts intent and priority, while Laravel remains responsible for validation, persistence, routing, confidence-based review decisions, and the admin experience.

## Architecture

- **Laravel + Livewire:** customer submission, business rules, MySQL persistence, and admin UI
- **Blade + Tailwind CSS:** responsive presentation
- **FastAPI:** analysis-only HTTP boundary
- **scikit-learn:** TF-IDF and Logistic Regression intent and priority classifiers
- **MySQL:** application source of truth

## Local setup

Install the Laravel and frontend dependencies:

```bash
composer install
npm install
```

Copy `.env.example` to `.env`, generate an application key, and configure the existing `requestpilot` MySQL database:

```bash
php artisan key:generate
```

Start Laravel and Vite in separate terminals:

```bash
php artisan serve
npm run dev
```

The AI service has its own setup instructions in [`ai-service/README.md`](ai-service/README.md). With its virtual environment active, run it on the URL configured by `AI_SERVICE_URL`:

```bash
cd ai-service
uvicorn app.main:app --reload --port 8001
```

## Verification

Run the Laravel, formatting, frontend, and Python checks with:

```bash
php artisan test
vendor/bin/pint
npm run build
cd ai-service && python -m pytest
```

The saved model artifacts are used for inference and are not retrained when API requests are handled.
