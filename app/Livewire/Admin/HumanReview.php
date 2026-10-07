<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class HumanReview extends Component
{
    use WithPagination;

    private const INTENTS = ['sales', 'support', 'billing', 'refund', 'cancellation'];

    private const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    private const TEAMS = ['sales', 'support', 'finance', 'retention'];

    private const STATUSES = ['pending', 'needs_attention', 'escalated'];

    private const PER_PAGE = 10;

    public string $search = '';

    public string $intent = '';

    public string $priority = '';

    public string $team = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedIntent(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function updatedTeam(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'intent', 'priority', 'team', 'status');
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);
        $hasActiveFilters = $search !== ''
            || $this->intent !== ''
            || $this->priority !== ''
            || $this->team !== ''
            || $this->status !== '';

        $query = Inquiry::query()
            ->where('requires_review', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when(in_array($this->intent, self::INTENTS, true), function ($query) {
                $query->where('intent', $this->intent);
            })
            ->when(in_array($this->priority, self::PRIORITIES, true), function ($query) {
                $query->where('priority', $this->priority);
            })
            ->when(in_array($this->team, self::TEAMS, true), function ($query) {
                $query->where('assigned_team', $this->team);
            })
            ->when(in_array($this->status, self::STATUSES, true), function ($query) {
                $query->where('status', $this->status);
            })
            ->select([
                'id',
                'name',
                'email',
                'intent',
                'intent_confidence',
                'priority',
                'priority_confidence',
                'assigned_team',
                'status',
                'created_at',
            ])
            ->latest()
            ->orderByDesc('id');

        $inquiries = $query->paginate(self::PER_PAGE);
        $hasAnyReviewInquiries = $inquiries->total() > 0
            || ($hasActiveFilters && Inquiry::query()->where('requires_review', true)->exists());

        return view('livewire.admin.human-review', [
            'inquiries' => $inquiries,
            'hasActiveFilters' => $hasActiveFilters,
            'hasAnyReviewInquiries' => $hasAnyReviewInquiries,
            'intentOptions' => self::INTENTS,
            'priorityOptions' => self::PRIORITIES,
            'teamOptions' => self::TEAMS,
            'statusOptions' => self::STATUSES,
            'intentConfidenceThreshold' => (float) config('services.ai.intent_confidence_threshold'),
            'priorityConfidenceThreshold' => (float) config('services.ai.priority_confidence_threshold'),
        ]);
    }
}
