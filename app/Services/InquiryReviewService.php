<?php

namespace App\Services;

use App\Models\Inquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class InquiryReviewService
{
    public function __construct(
        private InquiryRoutingService $routingService,
    ) {}

    /**
     * @throws ValidationException
     */
    public function review(
        Inquiry $inquiry,
        string $reviewedIntent,
        string $reviewedPriority,
    ): Inquiry {
        return DB::transaction(function () use ($inquiry, $reviewedIntent, $reviewedPriority) {
            $lockedInquiry = Inquiry::query()
                ->lockForUpdate()
                ->findOrFail($inquiry->getKey());

            if (! $lockedInquiry->requires_review || $lockedInquiry->reviewed_at !== null) {
                throw ValidationException::withMessages([
                    'review' => 'This inquiry has already been reviewed.',
                ]);
            }

            $lockedInquiry->update([
                'reviewed_intent' => $reviewedIntent,
                'reviewed_priority' => $reviewedPriority,
                'reviewed_at' => now(),
                'requires_review' => false,
                'assigned_team' => $this->routingService->teamForIntent($reviewedIntent),
                'status' => $this->routingService->statusForPriority($reviewedPriority),
            ]);

            return $lockedInquiry;
        });
    }
}
