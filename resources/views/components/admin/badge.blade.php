@props([
    'type',
    'value',
])

@php
    $displayValue = match ($type) {
        'review' => $value ? 'Review required' : 'No review',
        default => $value ? \Illuminate\Support\Str::headline($value) : 'Not set',
    };

    $classes = match (true) {
        $type === 'intent' && $value === 'sales' => 'bg-indigo-600 text-white ring-indigo-700/30',
        $type === 'intent' && $value === 'support' => 'bg-cyan-600 text-white ring-cyan-700/30',
        $type === 'intent' && $value === 'billing' => 'bg-amber-500 text-amber-950 ring-amber-700/30',
        $type === 'intent' && $value === 'refund' => 'bg-rose-600 text-white ring-rose-700/30',
        $type === 'intent' && $value === 'cancellation' => 'bg-slate-600 text-white ring-slate-700/30',
        $type === 'priority' && $value === 'urgent' => 'bg-red-600 text-white ring-red-700/30',
        $type === 'priority' && $value === 'high' => 'bg-orange-500 text-orange-950 ring-orange-700/30',
        $type === 'priority' && $value === 'normal' => 'bg-emerald-600 text-white ring-emerald-700/30',
        $type === 'priority' && $value === 'low' => 'bg-slate-500 text-white ring-slate-700/30',
        $type === 'status' && $value === 'escalated' => 'bg-rose-600 text-white ring-rose-700/30',
        $type === 'status' && $value === 'needs_attention' => 'bg-orange-500 text-orange-950 ring-orange-700/30',
        $type === 'status' && $value === 'pending' => 'bg-blue-600 text-white ring-blue-700/30',
        $type === 'review' && $value => 'bg-violet-600 text-white ring-violet-700/30',
        $type === 'review' && !$value => 'bg-slate-300 text-slate-700 ring-slate-400/70',
        default => 'bg-slate-500 text-white ring-slate-700/30',
    };
@endphp

<span {{ $attributes->class(['inline-flex items-center whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $classes]) }}>
    {{ $displayValue }}
</span>
