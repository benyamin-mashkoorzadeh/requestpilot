<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\HumanReview;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HumanReviewTest extends TestCase
{
    use RefreshDatabase;

    private int $inquirySequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_human_review_page_loads_successfully(): void
    {
        $this->get(route('admin.review'))
            ->assertOk()
            ->assertSee('Review queue')
            ->assertSeeLivewire(HumanReview::class);
    }

    public function test_queue_contains_only_inquiries_requiring_review(): void
    {
        $required = $this->createInquiry(['requires_review' => true]);
        $this->createInquiry(['requires_review' => false]);

        Livewire::test(HumanReview::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->pluck('id')->all() === [$required->id]);
    }

    public function test_review_inquiries_are_ordered_newest_first(): void
    {
        $oldest = $this->createInquiry(['created_at' => now()->subDays(2)]);
        $newest = $this->createInquiry(['created_at' => now()]);
        $middle = $this->createInquiry(['created_at' => now()->subDay()]);

        Livewire::test(HumanReview::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->pluck('id')->all() === [
                $newest->id,
                $middle->id,
                $oldest->id,
            ]);
    }

    public function test_confidences_are_formatted_as_percentages(): void
    {
        $this->createInquiry([
            'intent_confidence' => 0.2757,
            'priority_confidence' => 0.62,
        ]);

        Livewire::test(HumanReview::class)
            ->assertSee('27.6%')
            ->assertSee('62.0%');
    }

    public function test_search_matches_review_inquiries(): void
    {
        $target = $this->createInquiry(['message' => 'A warehouse scanner stopped syncing overnight.']);
        $this->createInquiry(['message' => 'Please return the duplicate payment.']);

        Livewire::test(HumanReview::class)
            ->set('search', 'scanner stopped syncing')
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$target->id]);
    }

    public function test_each_intent_filter_returns_only_its_intent(): void
    {
        $inquiries = collect(['sales', 'support', 'billing', 'refund', 'cancellation'])
            ->mapWithKeys(fn (string $intent): array => [
                $intent => $this->createInquiry(['intent' => $intent]),
            ]);
        $component = Livewire::test(HumanReview::class);

        foreach ($inquiries as $intent => $inquiry) {
            $component->set('intent', $intent)
                ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$inquiry->id]);
        }
    }

    public function test_each_priority_filter_returns_only_its_priority(): void
    {
        $inquiries = collect(['low', 'normal', 'high', 'urgent'])
            ->mapWithKeys(fn (string $priority): array => [
                $priority => $this->createInquiry(['priority' => $priority]),
            ]);
        $component = Livewire::test(HumanReview::class);

        foreach ($inquiries as $priority => $inquiry) {
            $component->set('priority', $priority)
                ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$inquiry->id]);
        }
    }

    public function test_each_assigned_team_filter_returns_only_its_team(): void
    {
        $inquiries = collect(['sales', 'support', 'finance', 'retention'])
            ->mapWithKeys(fn (string $team): array => [
                $team => $this->createInquiry(['assigned_team' => $team]),
            ]);
        $component = Livewire::test(HumanReview::class);

        foreach ($inquiries as $team => $inquiry) {
            $component->set('team', $team)
                ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$inquiry->id]);
        }
    }

    public function test_each_status_filter_returns_only_its_status(): void
    {
        $inquiries = collect(['pending', 'needs_attention', 'escalated'])
            ->mapWithKeys(fn (string $status): array => [
                $status => $this->createInquiry(['status' => $status]),
            ]);
        $component = Livewire::test(HumanReview::class);

        foreach ($inquiries as $status => $inquiry) {
            $component->set('status', $status)
                ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$inquiry->id]);
        }
    }

    public function test_search_and_all_filters_work_together(): void
    {
        $target = $this->createInquiry([
            'name' => 'Avery Exactreview',
            'intent' => 'refund',
            'priority' => 'high',
            'assigned_team' => 'finance',
            'status' => 'needs_attention',
        ]);
        $this->createInquiry([
            'name' => 'Morgan Exactreview',
            'intent' => 'support',
            'priority' => 'high',
            'assigned_team' => 'support',
            'status' => 'needs_attention',
        ]);

        Livewire::test(HumanReview::class)
            ->set('search', 'Exactreview')
            ->set('intent', 'refund')
            ->set('priority', 'high')
            ->set('team', 'finance')
            ->set('status', 'needs_attention')
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$target->id]);
    }

    public function test_clear_filters_resets_every_filter(): void
    {
        $this->createInquiry();
        $this->createInquiry([
            'intent' => 'refund',
            'priority' => 'urgent',
            'assigned_team' => 'finance',
            'status' => 'escalated',
        ]);

        Livewire::test(HumanReview::class)
            ->set('search', 'Customer')
            ->set('intent', 'refund')
            ->set('priority', 'urgent')
            ->set('team', 'finance')
            ->set('status', 'escalated')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('intent', '')
            ->assertSet('priority', '')
            ->assertSet('team', '')
            ->assertSet('status', '')
            ->assertViewHas('inquiries', fn ($results): bool => $results->total() === 2);
    }

    public function test_review_queue_is_paginated_ten_per_page(): void
    {
        foreach (range(1, 12) as $minutesAgo) {
            $this->createInquiry(['created_at' => now()->subMinutes($minutesAgo)]);
        }

        Livewire::test(HumanReview::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->count() === 10
                && $inquiries->total() === 12)
            ->call('nextPage')
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->count() === 2
                && $inquiries->currentPage() === 2);
    }

    public function test_search_and_each_filter_reset_pagination(): void
    {
        foreach (range(1, 12) as $index) {
            $this->createInquiry();
        }

        $component = Livewire::test(HumanReview::class);
        $changes = [
            'search' => 'Customer',
            'intent' => 'support',
            'priority' => 'normal',
            'team' => 'support',
            'status' => 'pending',
        ];

        foreach ($changes as $property => $value) {
            $component->call('setPage', 2)
                ->assertSet('paginators.page', 2)
                ->set($property, $value)
                ->assertSet('paginators.page', 1)
                ->call('clearFilters');
        }
    }

    public function test_empty_review_queue_state_is_displayed(): void
    {
        $this->createInquiry(['requires_review' => false]);

        Livewire::test(HumanReview::class)
            ->assertSee('No inquiries currently require human review')
            ->assertDontSee('No review inquiries match these filters');
    }

    public function test_no_results_state_is_displayed_for_unmatched_filters(): void
    {
        $this->createInquiry();

        Livewire::test(HumanReview::class)
            ->set('search', 'this-does-not-exist')
            ->assertSee('No review inquiries match these filters')
            ->assertSee('Clear all filters');
    }

    public function test_view_action_links_to_the_existing_inquiry_detail_route(): void
    {
        $inquiry = $this->createInquiry(['name' => 'Review Link Customer']);

        $this->get(route('admin.review'))
            ->assertOk()
            ->assertSee('aria-label="View review inquiry from Review Link Customer"', false)
            ->assertSee('href="'.route('admin.inquiries.show', $inquiry).'"', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createInquiry(array $overrides = []): Inquiry
    {
        $this->inquirySequence++;
        $createdAt = $overrides['created_at'] ?? now();
        unset($overrides['created_at']);

        $inquiry = Inquiry::query()->create(array_replace([
            'name' => 'Customer '.$this->inquirySequence,
            'email' => 'customer'.$this->inquirySequence.'@example.com',
            'message' => 'A realistic customer inquiry for human-review testing.',
            'status' => 'pending',
            'assigned_team' => 'support',
            'requires_review' => true,
            'intent' => 'support',
            'intent_confidence' => 0.27,
            'priority' => 'normal',
            'priority_confidence' => 0.74,
        ], $overrides));

        $inquiry->timestamps = false;
        $inquiry->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->saveQuietly();
        $inquiry->timestamps = true;

        return $inquiry;
    }
}
