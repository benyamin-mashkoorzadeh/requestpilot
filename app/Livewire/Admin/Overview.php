<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

final class Overview extends Component
{
    private const INTENTS = ['sales', 'support', 'billing', 'refund', 'cancellation'];

    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    private const RECENT_INQUIRY_LIMIT = 10;

    private const RECENT_CORRECTION_LIMIT = 5;

    public function render(): View
    {
        $metrics = Inquiry::query()
            ->selectRaw('COUNT(*) as total_inquiries')
            ->selectRaw('COALESCE(SUM(requires_review = 1), 0) as needs_review')
            ->selectRaw('COALESCE(SUM(reviewed_at IS NOT NULL), 0) as completed_reviews')
            ->selectRaw('COALESCE(SUM(status = ?), 0) as escalated_inquiries', ['escalated'])
            ->selectRaw('COALESCE(SUM(reviewed_at IS NOT NULL AND reviewed_intent <> intent), 0) as intent_corrections')
            ->selectRaw('COALESCE(SUM(reviewed_at IS NOT NULL AND reviewed_priority <> priority), 0) as priority_corrections')
            ->selectRaw('COALESCE(SUM(reviewed_at IS NOT NULL AND reviewed_intent = intent AND reviewed_priority = priority), 0) as fully_agreed_reviews')
            ->firstOrFail();

        $totalInquiries = (int) $metrics->total_inquiries;
        $completedReviews = (int) $metrics->completed_reviews;
        $fullyAgreedReviews = (int) $metrics->fully_agreed_reviews;

        $intentCounts = Inquiry::query()
            ->whereIn('intent', self::INTENTS)
            ->select('intent')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('intent')
            ->pluck('aggregate', 'intent');

        $priorityCounts = Inquiry::query()
            ->whereIn('priority', self::PRIORITIES)
            ->select('priority')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        return view('livewire.admin.overview', [
            'totalInquiries' => $totalInquiries,
            'reviewInquiries' => (int) $metrics->needs_review,
            'completedReviews' => $completedReviews,
            'escalatedInquiries' => (int) $metrics->escalated_inquiries,
            'intentCorrections' => (int) $metrics->intent_corrections,
            'priorityCorrections' => (int) $metrics->priority_corrections,
            'fullyAgreedReviews' => $fullyAgreedReviews,
            'humanAgreementRate' => $completedReviews === 0
                ? null
                : round(($fullyAgreedReviews / $completedReviews) * 100, 1),
            'intentDistribution' => $this->distribution(self::INTENTS, $intentCounts, $totalInquiries),
            'priorityDistribution' => $this->distribution(self::PRIORITIES, $priorityCounts, $totalInquiries),
            'correctedInquiries' => Inquiry::query()
                ->whereNotNull('reviewed_at')
                ->where(function ($query): void {
                    $query->whereColumn('reviewed_intent', '<>', 'intent')
                        ->orWhereColumn('reviewed_priority', '<>', 'priority');
                })
                ->latest('reviewed_at')
                ->orderByDesc('id')
                ->limit(self::RECENT_CORRECTION_LIMIT)
                ->get(),
            'recentInquiries' => Inquiry::query()
                ->latest()
                ->limit(self::RECENT_INQUIRY_LIMIT)
                ->get(),
        ]);
    }

    /**
     * @param  list<string>  $labels
     * @param  Collection<string, int|string>  $counts
     * @return list<array{label: string, count: int, percentage: float}>
     */
    private function distribution(array $labels, Collection $counts, int $total): array
    {
        return array_map(function (string $label) use ($counts, $total): array {
            $count = (int) $counts->get($label, 0);

            return [
                'label' => $label,
                'count' => $count,
                'percentage' => $total === 0 ? 0.0 : round(($count / $total) * 100, 1),
            ];
        }, $labels);
    }
}
