<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\InquiryDetail;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_detail_page_displays_the_complete_inquiry(): void
    {
        $message = 'The duplicate annual charge has reduced our available operating funds. Please return the complete payment to the original card without changing our subscription.';
        $inquiry = $this->createInquiry([
            'name' => 'Avery Northwind',
            'email' => 'avery@northwind.example',
            'message' => $message,
            'intent' => 'refund',
            'intent_confidence' => 0.6172,
            'priority' => 'high',
            'priority_confidence' => 0.93456,
            'assigned_team' => 'finance',
            'status' => 'needs_attention',
            'requires_review' => true,
            'created_at' => '2026-09-24 15:45:00',
        ]);

        $this->get(route('admin.inquiries.show', $inquiry))
            ->assertOk()
            ->assertSeeLivewire(InquiryDetail::class)
            ->assertSee('Avery Northwind')
            ->assertSee('avery@northwind.example')
            ->assertSee('Sep 24, 2026 at 3:45 PM')
            ->assertSee($message)
            ->assertSee('AI Analysis')
            ->assertSee('Refund')
            ->assertSee('61.7%')
            ->assertSee('High')
            ->assertSee('93.5%')
            ->assertSee('Laravel Routing')
            ->assertSee('Finance')
            ->assertSee('Needs Attention')
            ->assertSee('Review required')
            ->assertSee('Back to inquiries');
    }

    public function test_inquiries_listing_links_to_the_correct_detail_route(): void
    {
        $inquiry = $this->createInquiry(['name' => 'Linked Customer']);

        $this->get(route('admin.inquiries'))
            ->assertOk()
            ->assertSee('aria-label="View inquiry from Linked Customer"', false)
            ->assertSee('href="'.route('admin.inquiries.show', $inquiry).'"', false);
    }

    public function test_nonexistent_inquiry_returns_not_found(): void
    {
        $this->get(route('admin.inquiries.show', ['inquiry' => 999999]))
            ->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createInquiry(array $overrides = []): Inquiry
    {
        $createdAt = $overrides['created_at'] ?? now();
        unset($overrides['created_at']);

        $inquiry = Inquiry::query()->create(array_replace([
            'name' => 'Customer Name',
            'email' => 'customer@example.com',
            'message' => 'A complete customer message for the inquiry detail page.',
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
