<div>
    @if (session('success'))
        <div
            role="status"
            class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
        >
            <svg class="mt-0.5 size-5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.478-9.817a.75.75 0 0 1 1.052-.143Z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @error('analysis')
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="submit" class="space-y-5" novalidate>
        <div>
            <label for="name" class="block text-sm font-medium text-slate-800">Name</label>
            <input
                id="name"
                type="text"
                wire:model="name"
                autocomplete="name"
                placeholder="Your name"
                @class([
                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4',
                    'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' => !$errors->has('name'),
                    'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('name'),
                ])
            >
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-800">Email</label>
            <input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                placeholder="you@example.com"
                @class([
                    'mt-2 block w-full rounded-xl border bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4',
                    'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' => !$errors->has('email'),
                    'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                ])
            >
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-4">
                <label for="message" class="block text-sm font-medium text-slate-800">Message</label>
                <span class="text-xs text-slate-500">Tell us how we can help</span>
            </div>
            <textarea
                id="message"
                wire:model="message"
                rows="6"
                placeholder="Describe your question or request..."
                @class([
                    'mt-2 block w-full resize-y rounded-xl border bg-white px-4 py-3 text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4',
                    'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100' => !$errors->has('message'),
                    'border-red-400 focus:border-red-500 focus:ring-red-100' => $errors->has('message'),
                ])
            ></textarea>
            @error('message')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200 disabled:cursor-wait disabled:opacity-70"
            wire:loading.attr="disabled"
            wire:target="submit"
        >
            <span wire:loading.remove wire:target="submit">Send inquiry</span>
            <span wire:loading wire:target="submit">Sending...</span>
        </button>
    </form>
</div>
