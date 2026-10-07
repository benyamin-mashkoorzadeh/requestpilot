<div>
    <section class="rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="review-filters-heading">
        <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="review-filters-heading" class="font-bold tracking-tight text-slate-950">Review queue</h2>
                    <p class="mt-1 text-sm text-slate-600">Find confidence-flagged inquiries by customer or routing outcome.</p>
                </div>
                <button
                    type="button"
                    wire:click="clearFilters"
                    @disabled(!$hasActiveFilters)
                    class="inline-flex items-center rounded-lg border border-slate-400 bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-indigo-500 hover:text-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300/60 disabled:cursor-not-allowed disabled:opacity-45"
                >
                    Clear filters
                </button>
            </div>
        </div>

        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3 xl:grid-cols-6">
            <div class="sm:col-span-2 lg:col-span-2 xl:col-span-2">
                <label for="review-search" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Search</label>
                <div class="relative mt-2">
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m20 20-4-4" />
                    </svg>
                    <input
                        id="review-search"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Name, email, or message"
                        class="block w-full rounded-xl border border-slate-400 bg-slate-200 py-2.5 pl-10 pr-3 text-sm text-slate-950 shadow-sm outline-none placeholder:text-slate-500 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60"
                    >
                </div>
            </div>

            <div>
                <label for="review-intent" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Intent</label>
                <select id="review-intent" wire:model.live="intent" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                    <option value="">All intents</option>
                    @foreach ($intentOptions as $option)
                        <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="review-priority" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Priority</label>
                <select id="review-priority" wire:model.live="priority" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                    <option value="">All priorities</option>
                    @foreach ($priorityOptions as $option)
                        <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="review-team" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Assigned team</label>
                <select id="review-team" wire:model.live="team" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                    <option value="">All teams</option>
                    @foreach ($teamOptions as $option)
                        <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="review-status" class="block text-xs font-bold uppercase tracking-wide text-slate-700">Status</label>
                <select id="review-status" wire:model.live="status" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                    <option value="">All statuses</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="review-queue-heading">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 px-5 py-4 sm:px-6">
            <div>
                <h2 id="review-queue-heading" class="font-bold tracking-tight text-slate-950">Needs human review</h2>
                <p class="mt-1 text-sm text-slate-600">
                    {{ number_format($inquiries->total()) }} {{ \Illuminate\Support\Str::plural('inquiry', $inquiries->total()) }}
                </p>
            </div>
            <span wire:loading.delay class="text-sm font-semibold text-indigo-700">Updating…</span>
        </div>

        @if (!$hasAnyReviewInquiries)
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-900/25">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-bold text-slate-900">No inquiries currently require human review</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">
                    Inquiries flagged by the configured confidence thresholds will appear here.
                </p>
            </div>
        @elseif ($inquiries->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-slate-300 text-slate-700 ring-1 ring-slate-400">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="m20 20-4-4" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-bold text-slate-900">No review inquiries match these filters</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">
                    Try a different search term or remove one or more filters.
                </p>
                <button type="button" wire:click="clearFilters" class="mt-5 text-sm font-semibold text-indigo-700 hover:text-indigo-600">
                    Clear all filters
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-[1420px] w-full text-left text-sm">
                    <caption class="sr-only">Inquiries requiring human review, ordered newest first</caption>
                    <thead class="bg-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-4 py-3">Intent</th>
                            <th scope="col" class="px-4 py-3">Intent confidence</th>
                            <th scope="col" class="px-4 py-3">Priority</th>
                            <th scope="col" class="px-4 py-3">Priority confidence</th>
                            <th scope="col" class="px-4 py-3">Assigned team</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Created</th>
                            <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300">
                        @foreach ($inquiries as $inquiry)
                            @php
                                $intentIsLow = $inquiry->intent_confidence !== null
                                    && $inquiry->intent_confidence < $intentConfidenceThreshold;
                                $priorityIsLow = $inquiry->priority_confidence !== null
                                    && $inquiry->priority_confidence < $priorityConfidenceThreshold;
                            @endphp
                            <tr wire:key="review-inquiry-{{ $inquiry->id }}" class="hover:bg-slate-200/80">
                                <td class="px-6 py-4">
                                    <p class="max-w-52 truncate font-semibold text-slate-900">{{ $inquiry->name }}</p>
                                    <p class="mt-0.5 max-w-52 truncate text-xs text-slate-600">{{ $inquiry->email }}</p>
                                </td>
                                <td class="px-4 py-4"><x-admin.badge type="intent" :value="$inquiry->intent" /></td>
                                <td class="px-4 py-4">
                                    <span
                                        @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset',
                                            'bg-rose-600 text-white ring-rose-700/30' => $intentIsLow,
                                            'bg-slate-300 text-slate-800 ring-slate-400/70' => !$intentIsLow,
                                        ])
                                        @if ($intentIsLow) title="Below the configured intent-confidence threshold" @endif
                                    >
                                        @if ($intentIsLow)<span class="sr-only">Low confidence: </span>@endif
                                        {{ $inquiry->intent_confidence === null ? 'Not available' : number_format($inquiry->intent_confidence * 100, 1).'%' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4"><x-admin.badge type="priority" :value="$inquiry->priority" /></td>
                                <td class="px-4 py-4">
                                    <span
                                        @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset',
                                            'bg-rose-600 text-white ring-rose-700/30' => $priorityIsLow,
                                            'bg-slate-300 text-slate-800 ring-slate-400/70' => !$priorityIsLow,
                                        ])
                                        @if ($priorityIsLow) title="Below the configured priority-confidence threshold" @endif
                                    >
                                        @if ($priorityIsLow)<span class="sr-only">Low confidence: </span>@endif
                                        {{ $inquiry->priority_confidence === null ? 'Not available' : number_format($inquiry->priority_confidence * 100, 1).'%' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-medium text-slate-800">
                                        {{ $inquiry->assigned_team ? \Illuminate\Support\Str::headline($inquiry->assigned_team) : 'Not assigned' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4"><x-admin.badge type="status" :value="$inquiry->status" /></td>
                                <td class="px-6 py-4 text-right">
                                    <time datetime="{{ $inquiry->created_at->toIso8601String() }}" class="whitespace-nowrap font-medium text-slate-800">
                                        {{ $inquiry->created_at->format('M j, Y') }}
                                    </time>
                                    <p class="mt-0.5 whitespace-nowrap text-xs text-slate-600">{{ $inquiry->created_at->format('g:i A') }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        href="{{ route('admin.inquiries.show', $inquiry) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-500 bg-indigo-600 px-3 py-2 text-xs font-bold text-white shadow-sm shadow-indigo-900/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300/60"
                                        aria-label="View review inquiry from {{ $inquiry->name }}"
                                    >
                                        View
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-300 px-5 py-4 sm:px-6">
                {{ $inquiries->onEachSide(1)->links('components.admin.pagination') }}
            </div>
        @endif
    </section>
</div>
