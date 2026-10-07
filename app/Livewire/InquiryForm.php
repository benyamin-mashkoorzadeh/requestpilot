<?php

namespace App\Livewire;

use App\Exceptions\AiAnalysisException;
use App\Models\Inquiry;
use App\Services\AiAnalysisService;
use App\Services\InquiryRoutingService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InquiryForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $message = '';

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function submit(
        AiAnalysisService $analysisService,
        InquiryRoutingService $routingService,
    ): void {
        $validated = $this->validate();

        try {
            $analysis = $analysisService->analyze($validated['message']);
        } catch (AiAnalysisException) {
            $this->addError('analysis', 'We could not process your inquiry right now. Please try again.');

            return;
        }

        Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'status' => $routingService->statusFor($analysis),
            'assigned_team' => $routingService->teamFor($analysis),
            'requires_review' => $routingService->requiresReview($analysis),
            ...$analysis->toArray(),
        ]);

        $this->reset('name', 'email', 'message');

        session()->flash('success', 'Thanks — your inquiry has been received.');
    }

    public function render(): View
    {
        return view('livewire.inquiry-form');
    }
}
