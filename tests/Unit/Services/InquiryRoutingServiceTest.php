<?php

namespace Tests\Unit\Services;

use App\Data\AiAnalysis;
use App\Services\InquiryRoutingService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InquiryRoutingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.ai.intent_confidence_threshold' => 0.30,
            'services.ai.priority_confidence_threshold' => 0.35,
        ]);
    }

    #[DataProvider('priorityStatuses')]
    public function test_priority_is_translated_to_the_expected_status(
        string $priority,
        string $expectedStatus,
    ): void {
        $analysis = new AiAnalysis(
            intent: 'support',
            intentConfidence: 0.82,
            priority: $priority,
            priorityConfidence: 0.91,
        );

        $status = (new InquiryRoutingService)->statusFor($analysis);

        $this->assertSame($expectedStatus, $status);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function priorityStatuses(): array
    {
        return [
            'low remains pending' => ['low', 'pending'],
            'normal remains pending' => ['normal', 'pending'],
            'high needs attention' => ['high', 'needs_attention'],
            'urgent is escalated' => ['urgent', 'escalated'],
        ];
    }

    #[DataProvider('intentTeams')]
    public function test_intent_is_translated_to_the_expected_team(
        string $intent,
        string $expectedTeam,
    ): void {
        $analysis = new AiAnalysis(
            intent: $intent,
            intentConfidence: 0.82,
            priority: 'normal',
            priorityConfidence: 0.91,
        );

        $team = (new InquiryRoutingService)->teamFor($analysis);

        $this->assertSame($expectedTeam, $team);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function intentTeams(): array
    {
        return [
            'sales goes to sales' => ['sales', 'sales'],
            'support goes to support' => ['support', 'support'],
            'billing goes to finance' => ['billing', 'finance'],
            'refund goes to finance' => ['refund', 'finance'],
            'cancellation goes to retention' => ['cancellation', 'retention'],
        ];
    }

    #[DataProvider('reviewScenarios')]
    public function test_confidence_determines_whether_review_is_required(
        float $intentConfidence,
        float $priorityConfidence,
        bool $expectedReview,
    ): void {
        $analysis = new AiAnalysis(
            intent: 'support',
            intentConfidence: $intentConfidence,
            priority: 'urgent',
            priorityConfidence: $priorityConfidence,
        );

        $requiresReview = (new InquiryRoutingService)->requiresReview($analysis);

        $this->assertSame($expectedReview, $requiresReview);
    }

    /**
     * @return array<string, array{float, float, bool}>
     */
    public static function reviewScenarios(): array
    {
        return [
            'intent below its threshold' => [0.29, 0.62, true],
            'priority below its threshold' => [0.82, 0.34, true],
            'both below their thresholds' => [0.29, 0.34, true],
            'both equal their thresholds' => [0.30, 0.35, false],
            'both above their thresholds' => [0.31, 0.36, false],
        ];
    }
}
