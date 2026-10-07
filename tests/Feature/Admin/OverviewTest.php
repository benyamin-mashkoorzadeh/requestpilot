<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Overview;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OverviewTest extends TestCase
{
    use RefreshDatabase;

    private int $inquirySequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_overview_loads_successfully(): void
    {
        $this->get(route('admin.overview'))
            ->assertOk()
            ->assertSee('Overview')
            ->assertSee('Total inquiries')
            ->assertSeeLivewire(Overview::class);
    }

    public function test_kpis_are_calculated_from_inquiry_records(): void
    {
        $this->travelTo('2026-09-24 12:00:00');

        $this->createInquiry([
            'requires_review' => true,
            'status' => 'pending',
        ]);
        $this->createInquiry([
            'requires_review' => false,
            'status' => 'escalated',
            'reviewed_intent' => 'support',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now()->subHour(),
        ]);
        $this->createInquiry([
            'requires_review' => false,
            'status' => 'escalated',
            'reviewed_intent' => 'billing',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now()->subHours(2),
        ]);
        $this->createInquiry();

        Livewire::test(Overview::class)
            ->assertViewHas('totalInquiries', 4)
            ->assertViewHas('reviewInquiries', 1)
            ->assertViewHas('completedReviews', 2)
            ->assertViewHas('escalatedInquiries', 2);
    }

    public function test_ai_performance_metrics_and_human_agreement_rate_are_calculated_from_completed_reviews(): void
    {
        $this->createInquiry([
            'intent' => 'support',
            'priority' => 'normal',
            'reviewed_intent' => 'support',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now()->subMinutes(3),
        ]);
        $this->createInquiry([
            'intent' => 'support',
            'priority' => 'normal',
            'reviewed_intent' => 'billing',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now()->subMinutes(2),
        ]);
        $this->createInquiry([
            'intent' => 'support',
            'priority' => 'normal',
            'reviewed_intent' => 'support',
            'reviewed_priority' => 'urgent',
            'reviewed_at' => now()->subMinute(),
        ]);

        Livewire::test(Overview::class)
            ->assertViewHas('completedReviews', 3)
            ->assertViewHas('intentCorrections', 1)
            ->assertViewHas('priorityCorrections', 1)
            ->assertViewHas('fullyAgreedReviews', 1)
            ->assertViewHas('humanAgreementRate', 33.3)
            ->assertSee('Human agreement rate')
            ->assertSee('33.3%');
    }

    public function test_human_agreement_rate_has_a_safe_empty_state_when_no_reviews_are_complete(): void
    {
        $this->createInquiry();

        Livewire::test(Overview::class)
            ->assertViewHas('completedReviews', 0)
            ->assertViewHas('humanAgreementRate', null)
            ->assertSee('Human agreement rate')
            ->assertSee('—');
    }

    public function test_original_ai_intent_and_priority_distributions_include_counts_and_percentages(): void
    {
        $this->createInquiry(['intent' => 'sales', 'priority' => 'low']);
        $this->createInquiry(['intent' => 'sales', 'priority' => 'normal']);
        $this->createInquiry(['intent' => 'support', 'priority' => 'normal']);
        $this->createInquiry(['intent' => 'billing', 'priority' => 'high']);

        Livewire::test(Overview::class)
            ->assertViewHas('intentDistribution', [
                ['label' => 'sales', 'count' => 2, 'percentage' => 50.0],
                ['label' => 'support', 'count' => 1, 'percentage' => 25.0],
                ['label' => 'billing', 'count' => 1, 'percentage' => 25.0],
                ['label' => 'refund', 'count' => 0, 'percentage' => 0.0],
                ['label' => 'cancellation', 'count' => 0, 'percentage' => 0.0],
            ])
            ->assertViewHas('priorityDistribution', [
                ['label' => 'low', 'count' => 1, 'percentage' => 25.0],
                ['label' => 'normal', 'count' => 2, 'percentage' => 50.0],
                ['label' => 'high', 'count' => 1, 'percentage' => 25.0],
                ['label' => 'urgent', 'count' => 0, 'percentage' => 0.0],
            ]);
    }

    public function test_latest_corrections_show_only_changed_reviews_newest_first_and_limit_to_five(): void
    {
        $this->travelTo('2026-09-24 12:00:00');

        $correctedInquiries = collect();

        for ($index = 1; $index <= 7; $index++) {
            $correctedInquiries->push($this->createInquiry([
                'intent' => 'support',
                'reviewed_intent' => 'billing',
                'reviewed_priority' => 'normal',
                'reviewed_at' => now()->subMinutes(8 - $index),
            ]));
        }

        $unchanged = $this->createInquiry([
            'reviewed_intent' => 'support',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now(),
        ]);

        Livewire::test(Overview::class)
            ->assertViewHas('correctedInquiries', function ($inquiries) use ($correctedInquiries, $unchanged): bool {
                return $inquiries->count() === 5
                    && $inquiries->pluck('id')->all() === $correctedInquiries->reverse()->take(5)->pluck('id')->all()
                    && ! $inquiries->contains('id', $unchanged->id);
            });
    }

    public function test_latest_correction_links_to_the_existing_inquiry_detail_page(): void
    {
        $inquiry = $this->createInquiry([
            'intent' => 'support',
            'reviewed_intent' => 'sales',
            'reviewed_priority' => 'normal',
            'reviewed_at' => now(),
        ]);

        Livewire::test(Overview::class)
            ->assertSeeHtml('href="'.route('admin.inquiries.show', $inquiry).'"');
    }

    public function test_recent_inquiries_are_newest_first_and_limited_to_ten(): void
    {
        $this->travelTo('2026-09-24 12:00:00');

        for ($index = 11; $index >= 0; $index--) {
            $this->createInquiry(['created_at' => now()->subMinutes($index)]);
        }

        Livewire::test(Overview::class)
            ->assertViewHas('recentInquiries', function ($inquiries): bool {
                return $inquiries->count() === 10
                    && $inquiries->pluck('name')->all() === [
                        'Customer 12',
                        'Customer 11',
                        'Customer 10',
                        'Customer 9',
                        'Customer 8',
                        'Customer 7',
                        'Customer 6',
                        'Customer 5',
                        'Customer 4',
                        'Customer 3',
                    ];
            });
    }

    public function test_empty_overview_shows_a_useful_empty_state(): void
    {
        $this->get(route('admin.overview'))
            ->assertOk()
            ->assertSee('No inquiries yet')
            ->assertSee('New customer inquiries will appear here');
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
            'message' => 'A realistic customer inquiry for dashboard testing.',
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
