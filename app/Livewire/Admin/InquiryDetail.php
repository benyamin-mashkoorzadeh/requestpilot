<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use App\Services\InquiryReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

final class InquiryDetail extends Component
{
    private const INTENTS = ['sales', 'support', 'billing', 'refund', 'cancellation'];

    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public Inquiry $inquiry;

    public string $reviewedIntent = '';

    public string $reviewedPriority = '';

    public function mount(Inquiry $inquiry): void
    {
        $this->inquiry = $inquiry;
        $this->reviewedIntent = $inquiry->reviewed_intent ?? $inquiry->intent ?? '';
        $this->reviewedPriority = $inquiry->reviewed_priority ?? $inquiry->priority ?? '';
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'reviewedIntent' => ['required', 'string', Rule::in(self::INTENTS)],
            'reviewedPriority' => ['required', 'string', Rule::in(self::PRIORITIES)],
        ];
    }

    public function saveReview(InquiryReviewService $reviewService): void
    {
        if (! $this->inquiry->requires_review || $this->inquiry->reviewed_at !== null) {
            $this->addError('review', 'This inquiry has already been reviewed.');

            return;
        }

        $validated = $this->validate();

        $this->inquiry = $reviewService->review(
            $this->inquiry,
            $validated['reviewedIntent'],
            $validated['reviewedPriority'],
        );

        session()->flash('review_saved', 'Human review saved. Routing has been updated.');
    }

    public function render(): View
    {
        return view('livewire.admin.inquiry-detail', [
            'intentConfidence' => $this->formatConfidence($this->inquiry->intent_confidence),
            'priorityConfidence' => $this->formatConfidence($this->inquiry->priority_confidence),
            'intentOptions' => self::INTENTS,
            'priorityOptions' => self::PRIORITIES,
        ]);
    }

    private function formatConfidence(?float $confidence): string
    {
        if ($confidence === null) {
            return 'Not available';
        }

        return number_format($confidence * 100, 1).'%';
    }
}
