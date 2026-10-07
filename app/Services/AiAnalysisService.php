<?php

namespace App\Services;

use App\Data\AiAnalysis;
use App\Exceptions\AiAnalysisException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use UnexpectedValueException;

class AiAnalysisService
{
    private const INTENTS = ['sales', 'support', 'billing', 'refund', 'cancellation'];

    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    /**
     * @throws AiAnalysisException
     */
    public function analyze(string $message): AiAnalysis
    {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout((int) config('services.ai.timeout'))
                ->post($this->endpoint(), ['message' => $message]);

            $response->throw();

            $payload = $response->json();

            if (! is_array($payload) || ! $this->hasExactContract($payload)) {
                throw new UnexpectedValueException('AI analysis response has an invalid structure.');
            }

            if (! $this->isNumber($payload['intent_confidence']) || ! $this->isNumber($payload['priority_confidence'])) {
                throw new UnexpectedValueException('AI analysis confidence must be numeric.');
            }

            $validated = Validator::make($payload, [
                'intent' => ['required', 'string', Rule::in(self::INTENTS)],
                'intent_confidence' => ['required', 'numeric', 'between:0,1'],
                'priority' => ['required', 'string', Rule::in(self::PRIORITIES)],
                'priority_confidence' => ['required', 'numeric', 'between:0,1'],
            ])->validate();

            return new AiAnalysis(
                intent: $validated['intent'],
                intentConfidence: (float) $validated['intent_confidence'],
                priority: $validated['priority'],
                priorityConfidence: (float) $validated['priority_confidence'],
            );
        } catch (Throwable $exception) {
            Log::warning('AI inquiry analysis failed.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            throw new AiAnalysisException('The inquiry could not be analyzed.', previous: $exception);
        }
    }

    private function endpoint(): string
    {
        return rtrim((string) config('services.ai.url'), '/').'/analyze';
    }

    /**
     * @param  array<mixed>  $payload
     */
    private function hasExactContract(array $payload): bool
    {
        $expectedKeys = [
            'intent',
            'intent_confidence',
            'priority',
            'priority_confidence',
        ];

        return count($payload) === count($expectedKeys)
            && array_diff($expectedKeys, array_keys($payload)) === [];
    }

    private function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }
}
