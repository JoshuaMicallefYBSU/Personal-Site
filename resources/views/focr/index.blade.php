<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
    <head>
        <meta charset="utf-8">

        <script>
            (function () {
                var stored = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (stored === 'dark' || (!stored && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Request a Movie / Show</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                    },
                },
            };
        </script>
    </head>
    <body class="bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 font-sans">

        <header class="border-b border-zinc-200/80 bg-white/80 backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-950/80">
            <div class="mx-auto flex h-16 max-w-3xl items-center justify-between px-6">
                <span class="font-bold tracking-tight text-zinc-900 dark:text-white">Freedom of Cinema Request (FOCR)</span>
                <button id="theme-toggle" type="button" aria-label="Toggle theme" class="rounded-full p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">
                    🌓
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-14">
            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">Freedom of Cinema Request (FOCR)</h1>
                <p class="mt-3 text-zinc-600 dark:text-zinc-400">Can't find something? Let me know and I'll grab it for ya.</p>
            </div>

            @if (session('focr_status') === 'success')
                <p class="mx-auto mt-8 max-w-xl rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-400">
                    Thanks — your request has been submitted.
                </p>
            @endif

            <form method="POST" action="{{ route('focr.store') }}" class="mx-auto mt-8 max-w-xl space-y-4 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/40">
                @csrf

                <div>
                    <label for="title" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Show Name</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
                        <select name="type" id="type" required
                                class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select one&hellip;</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="release_year" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Release Year</label>
                        <input type="number" name="release_year" id="release_year" value="{{ old('release_year') }}" required
                               min="1878" max="{{ date('Y') + 5 }}"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('release_year')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="episodes-section" class="hidden rounded-lg border border-zinc-200 p-3.5 dark:border-zinc-700">
                    <label class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="all_episodes" id="all_episodes" value="1" {{ old('all_episodes') ? 'checked' : '' }}
                               class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900">
                        Request all seasons/episodes
                    </label>

                    <div id="episodes-input-wrap" class="mt-3">
                        <label for="episodes" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Which seasons/episodes?</label>
                        <input type="text" name="episodes" id="episodes" value="{{ old('episodes') }}" placeholder="e.g. Season 1-2, or S3E4-E10"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @error('episodes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Your Name <span class="font-normal text-zinc-400 dark:text-zinc-500">(optional — leave blank to stay anonymous)</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                           class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-lg bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-500/30">
                    Submit Request
                </button>
            </form>

            <div class="mx-auto mt-12 max-w-xl">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Requested so far</h2>

                @if ($requests->isEmpty())
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-500">No requests yet — be the first!</p>
                @else
                    <ul class="mt-3 divide-y divide-zinc-200 rounded-2xl border border-zinc-200 bg-white dark:divide-zinc-800 dark:border-zinc-800 dark:bg-zinc-900/40">
                        @foreach ($requests as $movieRequest)
                            <li class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $movieRequest->title }} <span class="text-zinc-400 dark:text-zinc-500">({{ $movieRequest->release_year }})</span></p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-500">Requested by {{ $movieRequest->name ?? 'Anonymooose' }}</p>
                                    @if ($movieRequest->type !== 'Movie')
                                        <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $movieRequest->all_episodes ? 'All seasons/episodes' : $movieRequest->episodes }}</p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1.5">
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">{{ $movieRequest->type }}</span>
                                    @if ($movieRequest->status === \App\Models\MovieRequest::STATUS_AVAILABLE)
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Available</span>
                                    @else
                                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">Requested</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </main>

        <footer class="py-10 text-center text-sm text-zinc-500 dark:text-zinc-500">
            <p>&copy; {{ date('Y') }} Joshua Micallef.</p>
        </footer>

        <script>
            document.getElementById('theme-toggle')?.addEventListener('click', function () {
                var isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });

            (function () {
                var typeSelect = document.getElementById('type');
                var episodesSection = document.getElementById('episodes-section');
                var allEpisodesCheckbox = document.getElementById('all_episodes');
                var episodesInputWrap = document.getElementById('episodes-input-wrap');
                var episodesInput = document.getElementById('episodes');

                function isEpisodic() {
                    return typeSelect.value === 'Series' || typeSelect.value === 'TV Show';
                }

                function update() {
                    var episodic = isEpisodic();
                    episodesSection.classList.toggle('hidden', !episodic);
                    episodesInputWrap.classList.toggle('hidden', allEpisodesCheckbox.checked);
                    episodesInput.required = episodic && !allEpisodesCheckbox.checked;
                }

                typeSelect?.addEventListener('change', update);
                allEpisodesCheckbox?.addEventListener('change', update);
                update();
            })();
        </script>
    </body>
</html>
