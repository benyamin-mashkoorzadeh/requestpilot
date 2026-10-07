<div>
    @if (session('review_saved'))
        <div role="status" class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-500/60 bg-emerald-100 px-4 py-3 text-sm font-semibold text-emerald-900 shadow-sm">
            <svg class="mt-0.5 size-5 shrink-0 text-emerald-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.478-9.817a.75.75 0 0 1 1.052-.143Z" clip-rule="evenodd" />
            </svg>
            {{ session('review_saved') }}
        </div>
    @endif

    <a href="{{ route('admin.inquiries') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:text-indigo-600 focus:outline-none focus:ring-4 focus:ring-indigo-300/60">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
        </svg>
        Back to inquiries
    </a>

    <section class="mt-5 overflow-hidden rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="customer-heading">
        <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">Customer</p>
            <h2 id="customer-heading" class="mt-1 text-xl font-bold tracking-tight text-slate-950">{{ $inquiry->name }}</h2>
        </div>
        <dl class="grid gap-px bg-slate-300 sm:grid-cols-2">
            <div class="bg-slate-100 px-5 py-4 sm:px-6">
                <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Email</dt>
                <dd class="mt-1.5 break-all text-sm font-semibold text-slate-900">
                    <a href="mailto:{{ $inquiry->email }}" class="hover:text-indigo-700">{{ $inquiry->email }}</a>
                </dd>
            </div>
            <div class="bg-slate-100 px-5 py-4 sm:px-6">
                <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Received</dt>
                <dd class="mt-1.5 text-sm font-semibold text-slate-900">
                    <time datetime="{{ $inquiry->created_at->toIso8601String() }}">
                        {{ $inquiry->created_at->format('M j, Y \a\t g:i A') }}
                    </time>
                </dd>
            </div>
        </dl>
    </section>

    <section class="mt-6 rounded-2xl border border-slate-400/70 bg-slate-100 p-5 shadow-md shadow-slate-900/10 sm:p-6" aria-labelledby="message-heading">
        <div class="flex items-center gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-cyan-600 text-white shadow-sm shadow-cyan-900/25">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h7m-9 9 3.4-3H19a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z" />
                </svg>
            </span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-cyan-700">Original request</p>
                <h2 id="message-heading" class="mt-0.5 font-bold tracking-tight text-slate-950">Customer message</h2>
            </div>
        </div>
        <p class="mt-5 whitespace-pre-wrap break-words text-[15px] leading-7 text-slate-800">{{ $inquiry->message }}</p>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="overflow-hidden rounded-2xl border border-indigo-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="analysis-heading">
            <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 4.5 10.7 7l2.8 1.2-2.8 1.2-1.2 2.6-1.2-2.6-2.8-1.2L8.3 7l1.2-2.5ZM17 11l.9 2.1L20 14l-2.1.9L17 17l-.9-2.1L14 14l2.1-.9L17 11ZM6 15l.7 1.3L8 17l-1.3.7L6 19l-.7-1.3L4 17l1.3-.7L6 15Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-700">Model output</p>
                        <h2 id="analysis-heading" class="mt-0.5 font-bold tracking-tight text-slate-950">AI Analysis</h2>
                        <p class="mt-1 text-sm text-slate-600">Original predictions preserved exactly as returned by the intent and priority classifiers.</p>
                    </div>
                </div>
            </div>

            <dl class="divide-y divide-slate-300 px-5 sm:px-6">
                <div class="flex items-center justify-between gap-6 py-4">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Intent</dt>
                        <dd class="mt-2"><x-admin.badge type="intent" :value="$inquiry->intent" /></dd>
                    </div>
                    <div class="text-right">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Intent confidence</dt>
                        <dd class="mt-1 text-xl font-bold tracking-tight text-slate-950">{{ $intentConfidence }}</dd>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-6 py-4">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Priority</dt>
                        <dd class="mt-2"><x-admin.badge type="priority" :value="$inquiry->priority" /></dd>
                    </div>
                    <div class="text-right">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Priority confidence</dt>
                        <dd class="mt-1 text-xl font-bold tracking-tight text-slate-950">{{ $priorityConfidence }}</dd>
                    </div>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-violet-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="routing-heading">
            <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white shadow-sm shadow-violet-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h7a3 3 0 0 1 3 3v9m0 0-3-3m3 3 3-3M20 6h-3a3 3 0 0 0-3 3" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-violet-700">Application decision</p>
                        <h2 id="routing-heading" class="mt-0.5 font-bold tracking-tight text-slate-950">Laravel Routing</h2>
                        <p class="mt-1 text-sm text-slate-600">Business rules applied after Laravel validated the model response.</p>
                    </div>
                </div>
            </div>

            <dl class="divide-y divide-slate-300 px-5 sm:px-6">
                <div class="flex items-center justify-between gap-6 py-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Assigned team</dt>
                    <dd class="text-sm font-bold text-slate-900">
                        {{ $inquiry->assigned_team ? \Illuminate\Support\Str::headline($inquiry->assigned_team) : 'Not assigned' }}
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-6 py-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Status</dt>
                    <dd><x-admin.badge type="status" :value="$inquiry->status" /></dd>
                </div>
                <div class="flex items-center justify-between gap-6 py-4">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Human review</dt>
                    <dd><x-admin.badge type="review" :value="$inquiry->requires_review" /></dd>
                </div>
            </dl>
        </section>
    </div>

    @if ($inquiry->requires_review)
        <section class="mt-6 overflow-hidden rounded-2xl border border-amber-500/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="human-review-heading">
            <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
                <div class="flex items-start gap-3">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-amber-950 shadow-sm shadow-amber-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.25c0 4.63 3.2 8.95 7.5 9.75 4.3-.8 7.5-5.12 7.5-9.75V6L12 3Zm-3 9 2 2 4-4" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">Final classification</p>
                        <h2 id="human-review-heading" class="mt-0.5 font-bold tracking-tight text-slate-950">Human Review</h2>
                        <p class="mt-1 text-sm text-slate-600">Confirm the model output or select the correct classifications before saving.</p>
                    </div>
                </div>
            </div>

            @error('review')
                <div role="alert" class="mx-5 mt-5 rounded-xl border border-red-500/60 bg-red-100 px-4 py-3 text-sm font-semibold text-red-900 sm:mx-6">
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit="saveReview" class="p-5 sm:p-6" novalidate>
                <div class="grid gap-5 lg:grid-cols-2">
                    <div class="rounded-xl border border-slate-400 bg-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">AI intent</p>
                                <div class="mt-2"><x-admin.badge type="intent" :value="$inquiry->intent" /></div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Confidence</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">{{ $intentConfidence }}</p>
                            </div>
                        </div>
                        <label for="reviewed-intent" class="mt-5 block text-sm font-bold text-slate-900">Final intent</label>
                        <select id="reviewed-intent" wire:model="reviewedIntent" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-100 px-3 py-2.5 text-sm text-slate-950 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                            @foreach ($intentOptions as $option)
                                <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                            @endforeach
                        </select>
                        @error('reviewedIntent')
                            <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-slate-400 bg-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">AI priority</p>
                                <div class="mt-2"><x-admin.badge type="priority" :value="$inquiry->priority" /></div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Confidence</p>
                                <p class="mt-1 text-lg font-bold text-slate-950">{{ $priorityConfidence }}</p>
                            </div>
                        </div>
                        <label for="reviewed-priority" class="mt-5 block text-sm font-bold text-slate-900">Final priority</label>
                        <select id="reviewed-priority" wire:model="reviewedPriority" class="mt-2 block w-full rounded-xl border border-slate-400 bg-slate-100 px-3 py-2.5 text-sm text-slate-950 shadow-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-300/60">
                            @foreach ($priorityOptions as $option)
                                <option value="{{ $option }}">{{ \Illuminate\Support\Str::headline($option) }}</option>
                            @endforeach
                        </select>
                        @error('reviewedPriority')
                            <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveReview" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-indigo-900/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300/60 disabled:cursor-wait disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveReview">Save review</span>
                        <span wire:loading wire:target="saveReview">Saving…</span>
                    </button>
                </div>
            </form>
        </section>
    @elseif ($inquiry->reviewed_at !== null)
        <section class="mt-6 overflow-hidden rounded-2xl border border-emerald-500/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="completed-review-heading">
            <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Final classification</p>
                <h2 id="completed-review-heading" class="mt-1 font-bold tracking-tight text-slate-950">Human Review</h2>
                <p class="mt-1 text-sm text-slate-600">The original model output remains above; these are the final human classifications.</p>
            </div>
            <dl class="grid gap-px bg-slate-300 sm:grid-cols-3">
                <div class="bg-slate-100 px-5 py-4 sm:px-6">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Final intent</dt>
                    <dd class="mt-2"><x-admin.badge type="intent" :value="$inquiry->reviewed_intent" /></dd>
                </div>
                <div class="bg-slate-100 px-5 py-4 sm:px-6">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Final priority</dt>
                    <dd class="mt-2"><x-admin.badge type="priority" :value="$inquiry->reviewed_priority" /></dd>
                </div>
                <div class="bg-slate-100 px-5 py-4 sm:px-6">
                    <dt class="text-xs font-bold uppercase tracking-wide text-slate-600">Reviewed</dt>
                    <dd class="mt-1.5 text-sm font-bold text-slate-900">
                        <time datetime="{{ $inquiry->reviewed_at->toIso8601String() }}">
                            {{ $inquiry->reviewed_at->format('M j, Y \a\t g:i A') }}
                        </time>
                    </dd>
                </div>
            </dl>
        </section>
    @endif
</div>
