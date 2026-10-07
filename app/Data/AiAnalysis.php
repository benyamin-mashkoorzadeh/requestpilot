<?php

namespace App\Data;

final readonly class AiAnalysis
{
    public function __construct(
        public string $intent,
        public float $intentConfidence,
        public string $priority,
        public float $priorityConfidence,
    ) {}

    /**
     * @return array{
     *     intent: string,
     *     intent_confidence: float,
     *     priority: string,
     *     priority_confidence: float
     * }
     */
    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'intent_confidence' => $this->intentConfidence,
            'priority' => $this->priority,
            'priority_confidence' => $this->priorityConfidence,
        ];
    }
}
