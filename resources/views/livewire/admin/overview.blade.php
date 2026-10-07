<div>
    <section aria-labelledby="overview-metrics">
        <h2 id="overview-metrics" class="sr-only">Inquiry overview metrics</h2>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-400/70 border-t-2 border-t-indigo-500 bg-slate-100 p-4 shadow-md shadow-slate-900/10 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Total inquiries</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ number_format($totalInquiries) }}</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h7m-9 9 3.4-3H19a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z" />
                        </svg>
                    </span>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-400/70 border-t-2 border-t-violet-500 bg-slate-100 p-4 shadow-md shadow-slate-900/10 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Needs review</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ number_format($reviewInquiries) }}</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white shadow-sm shadow-violet-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 11.5 11 13l3.5-4M12 3 4.5 6v5.25c0 4.63 3.2 8.95 7.5 9.75 4.3-.8 7.5-5.12 7.5-9.75V6L12 3Z" />
                        </svg>
                    </span>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-400/70 border-t-2 border-t-emerald-500 bg-slate-100 p-4 shadow-md shadow-slate-900/10 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Completed reviews</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ number_format($completedReviews) }}</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                        </svg>
                    </span>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-400/70 border-t-2 border-t-rose-500 bg-slate-100 p-4 shadow-md shadow-slate-900/10 sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-600">Escalated</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">{{ number_format($escalatedInquiries) }}</p>
                    </div>
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white shadow-sm shadow-rose-900/25">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3h.01M10.27 4.47 3.78 15.7A2 2 0 0 0 5.51 18.7h12.98a2 2 0 0 0 1.73-3L13.73 4.47a2 2 0 0 0-3.46 0Z" />
                        </svg>
                    </span>
                </div>
            </article>
        </div>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="ai-performance-heading">
        <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-700">Reviewed subset</p>
            <h2 id="ai-performance-heading" class="mt-1 font-bold tracking-tight text-slate-950">AI performance</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-600">
                Human outcomes for reviewed, low-confidence inquiries only. Agreement here is not model accuracy and is not representative of every inquiry.
            </p>
        </div>

        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-[minmax(0,2fr)_minmax(16rem,0.8fr)]">
            <div class="grid grid-cols-2 gap-3">
                <article class="rounded-xl border border-slate-300 bg-slate-200/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Reviewed inquiries</p>
                    <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($completedReviews) }}</p>
                </article>
                <article class="rounded-xl border border-slate-300 bg-slate-200/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Intent corrections</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-700">{{ number_format($intentCorrections) }}</p>
                </article>
                <article class="rounded-xl border border-slate-300 bg-slate-200/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Priority corrections</p>
                    <p class="mt-2 text-2xl font-bold text-orange-700">{{ number_format($priorityCorrections) }}</p>
                </article>
                <article class="rounded-xl border border-slate-300 bg-slate-200/70 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Fully agreed reviews</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($fullyAgreedReviews) }}</p>
                </article>
            </div>

            <article class="flex flex-col justify-between rounded-xl border border-indigo-500/50 bg-indigo-950 p-5 text-indigo-50 shadow-md shadow-indigo-950/20">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-200">Human agreement rate</p>
                    <p class="mt-3 text-4xl font-bold tracking-tight">
                        {{ $humanAgreementRate === null ? '—' : number_format($humanAgreementRate, 1).'%' }}
                    </p>
                </div>
                <p class="mt-5 text-xs leading-5 text-indigo-200">
                    Fully agreed reviews divided by completed reviews. This reflects the selected reviewed subset, not overall model accuracy.
                </p>
            </article>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-2" aria-label="Original AI prediction distributions">
        <article class="rounded-2xl border border-slate-400/70 bg-slate-100 p-5 shadow-md shadow-slate-900/10 sm:p-6">
            <div>
                <h2 class="font-bold tracking-tight text-slate-950">Intent distribution</h2>
                <p class="mt-1 text-sm text-slate-600">Original AI intent predictions across all inquiries.</p>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($intentDistribution as $item)
                    <div wire:key="intent-distribution-{{ $item['label'] }}">
                        <div class="mb-1.5 flex items-center justify-between gap-4 text-sm">
                            <span class="font-semibold text-slate-800">{{ \Illuminate\Support\Str::headline($item['label']) }}</span>
                            <span class="text-slate-600">{{ number_format($item['count']) }} <span class="text-slate-500">({{ number_format($item['percentage'], 1) }}%)</span></span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-300" role="progressbar" aria-label="{{ \Illuminate\Support\Str::headline($item['label']) }} intent" aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div @class([
                                'h-full rounded-full',
                                'bg-indigo-600' => $item['label'] === 'sales',
                                'bg-cyan-600' => $item['label'] === 'support',
                                'bg-amber-500' => $item['label'] === 'billing',
                                'bg-rose-600' => $item['label'] === 'refund',
                                'bg-slate-600' => $item['label'] === 'cancellation',
                            ]) style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="rounded-2xl border border-slate-400/70 bg-slate-100 p-5 shadow-md shadow-slate-900/10 sm:p-6">
            <div>
                <h2 class="font-bold tracking-tight text-slate-950">Priority distribution</h2>
                <p class="mt-1 text-sm text-slate-600">Original AI priority predictions across all inquiries.</p>
            </div>

            <div class="mt-5 space-y-4">
                @foreach ($priorityDistribution as $item)
                    <div wire:key="priority-distribution-{{ $item['label'] }}">
                        <div class="mb-1.5 flex items-center justify-between gap-4 text-sm">
                            <span class="font-semibold text-slate-800">{{ \Illuminate\Support\Str::headline($item['label']) }}</span>
                            <span class="text-slate-600">{{ number_format($item['count']) }} <span class="text-slate-500">({{ number_format($item['percentage'], 1) }}%)</span></span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-300" role="progressbar" aria-label="{{ \Illuminate\Support\Str::headline($item['label']) }} priority" aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div @class([
                                'h-full rounded-full',
                                'bg-slate-500' => $item['label'] === 'low',
                                'bg-emerald-600' => $item['label'] === 'normal',
                                'bg-orange-500' => $item['label'] === 'high',
                                'bg-red-600' => $item['label'] === 'urgent',
                            ]) style="width: {{ $item['percentage'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="latest-corrections-heading">
        <div class="border-b border-slate-300 px-5 py-4 sm:px-6">
            <h2 id="latest-corrections-heading" class="font-bold tracking-tight text-slate-950">Latest human corrections</h2>
            <p class="mt-1 text-sm text-slate-600">The five most recently reviewed inquiries where a human changed an AI prediction.</p>
        </div>

        @if ($correctedInquiries->isEmpty())
            <div class="px-6 py-10 text-center">
                <h3 class="text-sm font-bold text-slate-900">No corrections recorded yet</h3>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-slate-600">Completed reviews with changed intent or priority will appear here.</p>
            </div>
        @else
            <div class="divide-y divide-slate-300">
                @foreach ($correctedInquiries as $inquiry)
                    <a wire:key="correction-{{ $inquiry->id }}" href="{{ route('admin.inquiries.show', $inquiry) }}" class="grid gap-3 px-5 py-4 transition hover:bg-slate-200/80 sm:grid-cols-[minmax(0,0.8fr)_minmax(0,1.6fr)_auto] sm:items-center sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900">{{ $inquiry->name }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-600">{{ $inquiry->email }}</p>
                        </div>

                        <div class="flex flex-wrap gap-x-5 gap-y-2">
                            @if ($inquiry->reviewed_intent !== $inquiry->intent)
                                <div>
                                    <p class="mb-1 text-[0.68rem] font-bold uppercase tracking-wide text-slate-500">Intent: AI → human</p>
                                    <div class="flex items-center gap-1.5">
                                        <x-admin.badge type="intent" :value="$inquiry->intent" />
                                        <span class="text-slate-500" aria-hidden="true">→</span>
                                        <x-admin.badge type="intent" :value="$inquiry->reviewed_intent" />
                                    </div>
                                </div>
                            @endif

                            @if ($inquiry->reviewed_priority !== $inquiry->priority)
                                <div>
                                    <p class="mb-1 text-[0.68rem] font-bold uppercase tracking-wide text-slate-500">Priority: AI → human</p>
                                    <div class="flex items-center gap-1.5">
                                        <x-admin.badge type="priority" :value="$inquiry->priority" />
                                        <span class="text-slate-500" aria-hidden="true">→</span>
                                        <x-admin.badge type="priority" :value="$inquiry->reviewed_priority" />
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="sm:text-right">
                            <time datetime="{{ $inquiry->reviewed_at->toIso8601String() }}" class="whitespace-nowrap text-xs font-semibold text-slate-700">
                                {{ $inquiry->reviewed_at->format('M j, Y') }}
                            </time>
                            <p class="mt-0.5 whitespace-nowrap text-xs text-slate-500">{{ $inquiry->reviewed_at->format('g:i A') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-400/70 bg-slate-100 shadow-md shadow-slate-900/10" aria-labelledby="recent-inquiries-heading">
        <div class="flex items-center justify-between gap-4 border-b border-slate-300 px-5 py-4 sm:px-6">
            <div>
                <h2 id="recent-inquiries-heading" class="font-bold tracking-tight text-slate-950">Recent inquiries</h2>
                <p class="mt-1 text-sm text-slate-600">The latest customer requests and their routing outcomes.</p>
            </div>
            @if ($recentInquiries->isNotEmpty())
                <span class="hidden rounded-full bg-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-400/70 sm:inline-flex">
                    Latest {{ $recentInquiries->count() }}
                </span>
            @endif
        </div>

        @if ($recentInquiries->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-900/25">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h7m-9 9 3.4-3H19a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2v3Z" />
                    </svg>
                </span>
                <h3 class="mt-4 text-base font-bold text-slate-900">No inquiries yet</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-600">
                    New customer inquiries will appear here after they are analyzed and routed.
                </p>
                <a href="{{ url('/') }}" class="mt-5 inline-flex text-sm font-semibold text-indigo-700 hover:text-indigo-600">
                    View the public inquiry form
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-[1050px] w-full text-left text-sm">
                    <caption class="sr-only">Ten most recently created customer inquiries</caption>
                    <thead class="bg-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Customer</th>
                            <th scope="col" class="px-4 py-3">Intent</th>
                            <th scope="col" class="px-4 py-3">Priority</th>
                            <th scope="col" class="px-4 py-3">Assigned team</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">Review</th>
                            <th scope="col" class="px-6 py-3 text-right">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300">
                        @foreach ($recentInquiries as $inquiry)
                            <tr wire:key="inquiry-{{ $inquiry->id }}" class="hover:bg-slate-200/80">
                                <td class="px-6 py-4">
                                    <p class="max-w-52 truncate font-semibold text-slate-900">{{ $inquiry->name }}</p>
                                    <p class="mt-0.5 max-w-52 truncate text-xs text-slate-600">{{ $inquiry->email }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <x-admin.badge type="intent" :value="$inquiry->intent" />
                                </td>
                                <td class="px-4 py-4">
                                    <x-admin.badge type="priority" :value="$inquiry->priority" />
                                </td>
                                <td class="px-4 py-4">
                                    <span class="font-medium text-slate-800">
                                        {{ $inquiry->assigned_team ? \Illuminate\Support\Str::headline($inquiry->assigned_team) : 'Not assigned' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <x-admin.badge type="status" :value="$inquiry->status" />
                                </td>
                                <td class="px-4 py-4">
                                    <x-admin.badge type="review" :value="$inquiry->requires_review" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <time datetime="{{ $inquiry->created_at->toIso8601String() }}" class="whitespace-nowrap font-medium text-slate-800">
                                        {{ $inquiry->created_at->format('M j, Y') }}
                                    </time>
                                    <p class="mt-0.5 whitespace-nowrap text-xs text-slate-600">{{ $inquiry->created_at->format('g:i A') }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
