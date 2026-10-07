<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Inquiries;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InquiriesTest extends TestCase
{
    use RefreshDatabase;

    private int $inquirySequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_inquiries_page_loads_successfully(): void
    {
        $this->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('Find inquiries')
            ->assertSeeLivewire(Inquiries::class);
    }

    public function test_inquiries_are_ordered_newest_first(): void
    {
        $oldest = $this->createInquiry(['created_at' => now()->subDays(2)]);
        $newest = $this->createInquiry(['created_at' => now()]);
        $middle = $this->createInquiry(['created_at' => now()->subDay()]);

        Livewire::test(Inquiries::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->pluck('id')->all() === [
                $newest->id,
                $middle->id,
                $oldest->id,
            ]);
    }

    public function test_inquiries_are_paginated_ten_per_page(): void
    {
        foreach (range(1, 12) as $minutesAgo) {
            $this->createInquiry(['created_at' => now()->subMinutes($minutesAgo)]);
        }

        Livewire::test(Inquiries::class)
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->count() === 10
                && $inquiries->total() === 12
                && $inquiries->currentPage() === 1)
            ->call('nextPage')
            ->assertViewHas('inquiries', fn ($inquiries): bool => $inquiries->count() === 2
                && $inquiries->currentPage() === 2);
    }

    public function test_search_matches_customer_name(): void
    {
        $target = $this->createInquiry(['name' => 'Avery Northwind']);
        $this->createInquiry(['name' => 'Morgan Contoso']);

        $this->assertSearchFindsOnly('Northwind', $target);
    }

    public function test_search_matches_customer_email(): void
    {
        $target = $this->createInquiry(['email' => 'billing-contact@northwind.example']);
        $this->createInquiry(['email' => 'hello@contoso.example']);

        $this->assertSearchFindsOnly('billing-contact@northwind', $target);
    }

    public function test_search_matches_inquiry_message(): void
    {
        $target = $this->createInquiry(['message' => 'The warehouse scanners stopped syncing overnight.']);
        $this->createInquiry(['message' => 'Please send a copy of our receipt.']);

        $this->assertSearchFindsOnly('scanners stopped syncing', $target);
    }

    public function test_each_intent_filter_returns_only_its_intent(): void
    {
        $inquiries = collect(['sales', 'support', 'billing', 'refund', 'cancellation'])
            ->mapWithKeys(fn (string $intent): array => [
                $intent => $this->createInquiry(['intent' => $intent]),
            ]);
        $component = Livewire::test(Inquiries::class);

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
        $component = Livewire::test(Inquiries::class);

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
        $component = Livewire::test(Inquiries::class);

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
        $component = Livewire::test(Inquiries::class);

        foreach ($inquiries as $status => $inquiry) {
            $component->set('status', $status)
                ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$inquiry->id]);
        }
    }

    public function test_review_filter_supports_required_and_not_required(): void
    {
        $required = $this->createInquiry(['requires_review' => true]);
        $notRequired = $this->createInquiry(['requires_review' => false]);

        Livewire::test(Inquiries::class)
            ->set('review', 'required')
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$required->id])
            ->set('review', 'not_required')
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$notRequired->id]);
    }

    public function test_search_and_all_filters_work_together(): void
    {
        $target = $this->createInquiry([
            'name' => 'Avery Exactmatch',
            'message' => 'Please reverse the duplicate annual payment.',
            'intent' => 'refund',
            'priority' => 'high',
            'assigned_team' => 'finance',
            'status' => 'needs_attention',
            'requires_review' => true,
        ]);
        $this->createInquiry([
            'name' => 'Morgan Exactmatch',
            'intent' => 'support',
            'priority' => 'high',
            'assigned_team' => 'support',
            'status' => 'needs_attention',
            'requires_review' => true,
        ]);

        Livewire::test(Inquiries::class)
            ->set('search', 'Exactmatch')
            ->set('intent', 'refund')
            ->set('priority', 'high')
            ->set('team', 'finance')
            ->set('status', 'needs_attention')
            ->set('review', 'required')
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$target->id]);
    }

    public function test_clear_filters_resets_every_filter_and_returns_all_inquiries(): void
    {
        $this->createInquiry();
        $this->createInquiry([
            'intent' => 'refund',
            'priority' => 'urgent',
            'assigned_team' => 'finance',
            'status' => 'escalated',
            'requires_review' => true,
        ]);

        Livewire::test(Inquiries::class)
            ->set('search', 'Customer')
            ->set('intent', 'refund')
            ->set('priority', 'urgent')
            ->set('team', 'finance')
            ->set('status', 'escalated')
            ->set('review', 'required')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('intent', '')
            ->assertSet('priority', '')
            ->assertSet('team', '')
            ->assertSet('status', '')
            ->assertSet('review', '')
            ->assertViewHas('inquiries', fn ($results): bool => $results->total() === 2);
    }

    public function test_search_and_each_filter_reset_pagination(): void
    {
        foreach (range(1, 12) as $index) {
            $this->createInquiry();
        }

        $component = Livewire::test(Inquiries::class);
        $changes = [
            'search' => 'Customer',
            'intent' => 'support',
            'priority' => 'normal',
            'team' => 'support',
            'status' => 'pending',
            'review' => 'not_required',
        ];

        foreach ($changes as $property => $value) {
            $component->call('setPage', 2)
                ->assertSet('paginators.page', 2)
                ->set($property, $value)
                ->assertSet('paginators.page', 1)
                ->call('clearFilters');
        }
    }

    public function test_empty_database_state_is_displayed(): void
    {
        Livewire::test(Inquiries::class)
            ->assertSee('No inquiries yet')
            ->assertDontSee('No matching inquiries');
    }

    public function test_no_results_state_is_displayed_for_unmatched_filters(): void
    {
        $this->createInquiry();

        Livewire::test(Inquiries::class)
            ->set('search', 'this-does-not-exist')
            ->assertSee('No matching inquiries')
            ->assertSee('Clear all filters');
    }

    private function assertSearchFindsOnly(string $search, Inquiry $target): void
    {
        Livewire::test(Inquiries::class)
            ->set('search', $search)
            ->assertViewHas('inquiries', fn ($results): bool => $results->pluck('id')->all() === [$target->id]);
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
            'message' => 'A realistic customer inquiry for filtering tests.',
            'status' => 'pending',
            'assigned_team' => 'support',
            'requires_review' => false,
            'intent' => 'support',
            'intent_confidence' => 0.82,
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
