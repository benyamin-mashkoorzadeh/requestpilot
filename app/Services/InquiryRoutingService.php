<?php

namespace App\Services;

use App\Data\AiAnalysis;

final class InquiryRoutingService
{
    public function statusFor(AiAnalysis $analysis): string
    {
        return $this->statusForPriority($analysis->priority);
    }

    public function statusForPriority(string $priority): string
    {
        return match ($priority) {
            'low', 'normal' => 'pending',
            'high' => 'needs_attention',
            'urgent' => 'escalated',
        };
    }

    public function teamFor(AiAnalysis $analysis): string
    {
        return $this->teamForIntent($analysis->intent);
    }

    public function teamForIntent(string $intent): string
    {
        return match ($intent) {
            'sales' => 'sales',
            'support' => 'support',
            'billing', 'refund' => 'finance',
            'cancellation' => 'retention',
        };
    }

    public function requiresReview(AiAnalysis $analysis): bool
    {
        return $analysis->intentConfidence < (float) config('services.ai.intent_confidence_threshold')
            || $analysis->priorityConfidence < (float) config('services.ai.priority_confidence_threshold');
    }
}
