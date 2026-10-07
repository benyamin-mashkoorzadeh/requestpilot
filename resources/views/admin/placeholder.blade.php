<x-layouts.admin :title="$title" :subtitle="$subtitle">
    <section class="rounded-2xl border border-slate-400/70 bg-slate-100 px-6 py-14 text-center shadow-md shadow-slate-900/10 sm:px-10">
        <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-900/25">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M5.5 20h13a2 2 0 0 0 1.73-3L13.73 5.75a2 2 0 0 0-3.46 0L3.77 17a2 2 0 0 0 1.73 3Z" />
            </svg>
        </span>
        <p class="mt-5 text-sm font-semibold text-indigo-700">Planned for a later step</p>
        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-950">{{ $title }} is not available yet</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">
            This navigation destination is reserved for the upcoming inquiry-management workflow. No placeholder data or actions have been added.
        </p>
        <a href="{{ route('admin.overview') }}" class="mt-6 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-indigo-900/20 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">
            Return to overview
        </a>
    </section>
</x-layouts.admin>
