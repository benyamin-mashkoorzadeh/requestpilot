<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>RequestPilot — Customer inquiries, clearly captured</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
        <main class="relative isolate min-h-screen overflow-hidden">
            <div class="absolute inset-x-0 top-0 -z-10 h-96 bg-gradient-to-b from-indigo-500/20 to-transparent"></div>
            <div class="absolute -left-32 top-48 -z-10 size-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

            <div class="mx-auto grid min-h-screen max-w-6xl items-center gap-12 px-6 py-16 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                <section class="text-white">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-indigo-200">
                        <span class="size-2 rounded-full bg-emerald-400"></span>
                        Inquiry intake is open
                    </div>

                    <p class="mt-8 text-sm font-semibold uppercase tracking-[0.22em] text-indigo-300">RequestPilot</p>
                    <h1 class="mt-4 max-w-xl text-4xl font-bold tracking-tight sm:text-5xl">
                        Start with the request that matters.
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">
                        RequestPilot is built to intelligently process customer requests. This first step captures each inquiry clearly and securely before processing begins.
                    </p>

                    <div class="mt-10 grid max-w-lg grid-cols-3 gap-4 text-sm text-slate-300">
                        <div class="border-l border-indigo-400/50 pl-4">Clear input</div>
                        <div class="border-l border-indigo-400/50 pl-4">Validated data</div>
                        <div class="border-l border-indigo-400/50 pl-4">Ready to process</div>
                    </div>
                </section>

                <section class="rounded-3xl bg-white p-6 shadow-2xl shadow-black/30 ring-1 ring-white/10 sm:p-9">
                    <div class="mb-7">
                        <p class="text-sm font-semibold text-indigo-600">Contact us</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">How can we help?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Share the details of your request and we’ll make sure it is captured correctly.
                        </p>
                    </div>

                    <livewire:inquiry-form />
                </section>
            </div>
        </main>

        @livewireScripts
    </body>
</html>
