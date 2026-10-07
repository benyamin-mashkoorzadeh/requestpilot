<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\HumanReview;
use App\Livewire\Admin\InquiryDetail;
use App\Livewire\Admin\Overview;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class InquiryReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_form_appears_and_defaults_to_ai_predictions(): void
    {
        $inquiry = $this->createInquiry([
            'intent' => 'refund',
            'priority' => 'high',
        ]);

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->assertSet('reviewedIntent', 'refund')
            ->assertSet('reviewedPriority', 'high')
            ->assertSee('Human Review')
            ->assertSee('Save review');
    }

    public function test_valid_review_preserves_ai_output_and_updates_final_routing(): void
    {
        $this->travelTo('2026-09-25 10:30:00');
        $inquiry = $this->createInquiry([
            'intent' => 'refund',
            'intent_confidence' => 0.2757,
            'priority' => 'high',
            'priority_confidence' => 0.3412,
            'assigned_team' => 'finance',
            'status' => 'needs_attention',
        ]);

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->set('reviewedIntent', 'cancellation')
            ->set('reviewedPriority', 'urgent')
            ->call('saveReview')
            ->assertHasNoErrors()
            ->assertSee('Human review saved')
            ->assertSee('Final intent')
            ->assertDontSee('Save review');

        $inquiry->refresh();

        $this->assertSame('refund', $inquiry->intent);
        $this->assertSame(0.2757, $inquiry->intent_confidence);
        $this->assertSame('high', $inquiry->priority);
        $this->assertSame(0.3412, $inquiry->priority_confidence);
        $this->assertSame('cancellation', $inquiry->reviewed_intent);
        $this->assertSame('urgent', $inquiry->reviewed_priority);
        $this->assertTrue($inquiry->reviewed_at->equalTo(now()));
        $this->assertFalse($inquiry->requires_review);
        $this->assertSame('retention', $inquiry->assigned_team);
        $this->assertSame('escalated', $inquiry->status);
    }

    public function test_reviewed_inquiry_leaves_queue_and_overview_count_decreases(): void
    {
        $reviewed = $this->createInquiry(['name' => 'Reviewed Customer']);
        $remaining = $this->createInquiry(['name' => 'Remaining Customer']);

        Livewire::test(Overview::class)
            ->assertViewHas('reviewInquiries', 2);

        Livewire::test(InquiryDetail::class, ['inquiry' => $reviewed])
            ->call('saveReview')
            ->assertHasNoErrors();

        Livewire::test(HumanReview::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->pluck('id')->all() === [$remaining->id]);

        Livewire::test(Overview::class)
            ->assertViewHas('reviewInquiries', 1);
    }

    public function test_invalid_reviewed_intent_is_rejected_without_changes(): void
    {
        $inquiry = $this->createInquiry();

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->set('reviewedIntent', 'other')
            ->call('saveReview')
            ->assertHasErrors(['reviewedIntent']);

        $this->assertUnreviewed($inquiry);
    }

    public function test_invalid_reviewed_priority_is_rejected_without_changes(): void
    {
        $inquiry = $this->createInquiry();

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->set('reviewedPriority', 'critical')
            ->call('saveReview')
            ->assertHasErrors(['reviewedPriority']);

        $this->assertUnreviewed($inquiry);
    }

    public function test_completed_review_cannot_be_submitted_again(): void
    {
        $reviewedAt = now()->subHour();
        $inquiry = $this->createInquiry([
            'requires_review' => false,
            'reviewed_intent' => 'billing',
            'reviewed_priority' => 'normal',
            'reviewed_at' => $reviewedAt,
            'assigned_team' => 'finance',
            'status' => 'pending',
        ]);

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->assertSee('Final intent')
            ->assertDontSee('Save review')
            ->set('reviewedIntent', 'cancellation')
            ->set('reviewedPriority', 'urgent')
            ->call('saveReview')
            ->assertHasErrors(['review']);

        $inquiry->refresh();

        $this->assertSame('billing', $inquiry->reviewed_intent);
        $this->assertSame('normal', $inquiry->reviewed_priority);
        $this->assertSame(
            $reviewedAt->toDateTimeString(),
            $inquiry->reviewed_at->toDateTimeString(),
        );
        $this->assertSame('finance', $inquiry->assigned_team);
        $this->assertSame('pending', $inquiry->status);
    }

    public function test_review_database_update_runs_inside_a_transaction(): void
    {
        $inquiry = $this->createInquiry();
        $transactionLevelBeforeReview = DB::connection()->transactionLevel();
        $transactionLevelDuringUpdate = 0;

        Inquiry::updating(function () use (&$transactionLevelDuringUpdate): void {
            $transactionLevelDuringUpdate = DB::connection()->transactionLevel();
        });

        Livewire::test(InquiryDetail::class, ['inquiry' => $inquiry])
            ->call('saveReview')
            ->assertHasNoErrors();

        $this->assertGreaterThan(
            $transactionLevelBeforeReview,
            $transactionLevelDuringUpdate,
        );
    }

    private function assertUnreviewed(Inquiry $inquiry): void
    {
        $inquiry->refresh();

        $this->assertTrue($inquiry->requires_review);
        $this->assertNull($inquiry->reviewed_intent);
        $this->assertNull($inquiry->reviewed_priority);
        $this->assertNull($inquiry->reviewed_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createInquiry(array $overrides = []): Inquiry
    {
        return Inquiry::query()->create(array_replace([
            'name' => 'Review Customer',
            'email' => 'review@example.com',
            'message' => 'Please review this inquiry classification.',
            'status' => 'needs_attention',
            'assigned_team' => 'support',
            'requires_review' => true,
            'intent' => 'support',
            'intent_confidence' => 0.27,
            'priority' => 'high',
            'priority_confidence' => 0.62,
        ], $overrides));
    }
}
