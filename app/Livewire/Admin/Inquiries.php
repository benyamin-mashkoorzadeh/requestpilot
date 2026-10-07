<?php

namespace App\Livewire\Admin;

use App\Models\Inquiry;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

final class Inquiries extends Component
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

    public string $review = '';

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

    public function updatedReview(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'intent', 'priority', 'team', 'status', 'review');
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);
        $hasActiveFilters = $search !== ''
            || $this->intent !== ''
            || $this->priority !== ''
            || $this->team !== ''
            || $this->status !== ''
            || $this->review !== '';

        $query = Inquiry::query()
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
            ->when($this->review === 'required', function ($query) {
                $query->where('requires_review', true);
            })
            ->when($this->review === 'not_required', function ($query) {
                $query->where('requires_review', false);
            })
            ->select([
                'id',
                'name',
                'email',
                'intent',
                'priority',
                'assigned_team',
                'status',
                'requires_review',
                'created_at',
            ])
            ->latest()
            ->orderByDesc('id');

        $inquiries = $query->paginate(self::PER_PAGE);
        $hasAnyInquiries = $inquiries->total() > 0
            || ($hasActiveFilters && Inquiry::query()->exists());

        return view('livewire.admin.inquiries', [
            'inquiries' => $inquiries,
            'hasActiveFilters' => $hasActiveFilters,
            'hasAnyInquiries' => $hasAnyInquiries,
            'intentOptions' => self::INTENTS,
            'priorityOptions' => self::PRIORITIES,
            'teamOptions' => self::TEAMS,
            'statusOptions' => self::STATUSES,
        ]);
    }
}
