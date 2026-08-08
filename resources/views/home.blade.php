<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">

        {{-- Set the theme before first paint so there's no light/dark flash on load. --}}
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
        <meta name="description" content="{{ $resume['name'] }} — {{ $resume['title'] }}. {{ $resume['tagline'] }}">

        <title>{{ $resume['name'] }} — {{ $resume['title'] }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        {{-- Tailwind Play CDN: no Node/npm build step required for this page. --}}
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
        <style>
            ::selection { background-color: #c7d2fe; color: #1e1b4b; }
            .dark ::selection { background-color: #6366f1; color: #ffffff; }

            /* Scroll-reveal: enhanced only once JS confirms it can run, so content is never hidden without it. */
            .reveal-init { opacity: 0; transform: translateY(20px); transition: opacity .7s ease, transform .7s ease; }
            .reveal-visible { opacity: 1 !important; transform: translateY(0) !important; }
            @media (prefers-reduced-motion: reduce) {
                .reveal-init { transition: none; opacity: 1; transform: none; }
            }

            .nav-link { position: relative; }
            .nav-link::after {
                content: ''; position: absolute; left: 0; right: 0; bottom: -4px; height: 2px;
                background-image: linear-gradient(to right, #6366f1, #a855f7);
                transform: scaleX(0); transform-origin: left; transition: transform .25s ease;
            }
            .nav-link:hover::after { transform: scaleX(1); }
        </style>
    </head>
    <body class="bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 font-sans">

        @php
            // Shared brand-colored hover treatment for social icon buttons, used in both the hero and contact sections.
            $brandHover = [
                'github' => 'hover:border-zinc-900 hover:bg-zinc-900 hover:text-white dark:hover:border-white dark:hover:bg-white dark:hover:text-zinc-900',
                'linkedin' => 'hover:border-[#0A66C2] hover:bg-[#0A66C2] hover:text-white',
                'email' => 'hover:border-indigo-600 hover:bg-indigo-600 hover:text-white',
            ];
            $projectColors = ['indigo', 'violet', 'emerald', 'rose'];
            $skillColors = ['indigo', 'violet', 'emerald'];
            $skillPill = [
                'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
                'violet' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
                'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
            ];
            $projectTag = [
                'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300',
                'violet' => 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300',
                'emerald' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                'rose' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
            ];
            $projectBar = [
                'indigo' => 'from-indigo-500 to-violet-400',
                'violet' => 'from-violet-500 to-fuchsia-400',
                'emerald' => 'from-emerald-500 to-teal-400',
                'rose' => 'from-rose-500 to-pink-400',
            ];
            $projectHoverBorder = [
                'indigo' => 'hover:border-indigo-300 dark:hover:border-indigo-700',
                'violet' => 'hover:border-violet-300 dark:hover:border-violet-700',
                'emerald' => 'hover:border-emerald-300 dark:hover:border-emerald-700',
                'rose' => 'hover:border-rose-300 dark:hover:border-rose-700',
            ];
        @endphp

        <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/80 backdrop-blur dark:border-zinc-800/80 dark:bg-zinc-950/80">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-6">
                <a href="#top" class="font-bold tracking-tight text-zinc-900 dark:text-white">{{ $resume['name'] }}</a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-zinc-600 md:flex dark:text-zinc-400">
                    <a href="#about" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">About</a>
                    <a href="#experience" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">Experience</a>
                    <a href="#skills" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">Skills</a>
                    <a href="#certifications" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">Certifications</a>
                    <a href="#projects" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">Dev Projects</a>
                    <a href="#contact" class="nav-link pb-1 transition-colors hover:text-zinc-900 dark:hover:text-white">Contact</a>
                </nav>

                <div class="flex items-center gap-1">
                    <button id="theme-toggle" type="button" aria-label="Toggle theme" class="rounded-full p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">
                        <x-icon name="sun" class="h-5 w-5 dark:hidden" />
                        <x-icon name="moon" class="hidden h-5 w-5 dark:block" />
                    </button>
                    <button id="menu-toggle" type="button" aria-label="Toggle menu" class="rounded-full p-2 text-zinc-600 transition-colors hover:bg-zinc-100 hover:text-zinc-900 md:hidden dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="hidden flex-col gap-4 border-t border-zinc-200 px-6 py-4 text-sm font-medium text-zinc-600 md:hidden dark:border-zinc-800 dark:text-zinc-400">
                <a href="#about" class="hover:text-zinc-900 dark:hover:text-white">About</a>
                <a href="#experience" class="hover:text-zinc-900 dark:hover:text-white">Experience</a>
                <a href="#skills" class="hover:text-zinc-900 dark:hover:text-white">Skills</a>
                <a href="#certifications" class="hover:text-zinc-900 dark:hover:text-white">Certifications</a>
                <a href="#projects" class="hover:text-zinc-900 dark:hover:text-white">Dev Projects</a>
                <a href="#contact" class="hover:text-zinc-900 dark:hover:text-white">Contact</a>
            </div>
        </header>

        <main id="top">

            {{-- Hero --}}
            <section class="relative overflow-hidden">
                <div class="pointer-events-none absolute inset-0 -z-10">
                    <div class="absolute top-[-10rem] left-1/2 h-[28rem] w-[28rem] -translate-x-[65%] rounded-full bg-indigo-300/30 blur-3xl dark:bg-indigo-600/20"></div>
                    <div class="absolute top-[-6rem] left-1/2 h-[24rem] w-[24rem] translate-x-[10%] rounded-full bg-fuchsia-300/25 blur-3xl dark:bg-fuchsia-600/15"></div>
                    <div class="absolute top-[6rem] left-1/2 h-[20rem] w-[20rem] -translate-x-[10%] rounded-full bg-amber-200/20 blur-3xl dark:bg-amber-500/10"></div>
                </div>

                <div class="mx-auto flex max-w-5xl flex-col items-center px-6 pt-24 pb-20 text-center">
                    <div class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-fuchsia-500 text-3xl font-bold text-white shadow-xl shadow-indigo-500/20 ring-4 ring-white dark:shadow-indigo-500/10 dark:ring-zinc-900">
                        @if ($hasAvatar)
                            <img src="{{ $resume['avatar'] }}" alt="{{ $resume['name'] }}" class="h-full w-full object-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </div>

                    <h1 class="mt-7 text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ $resume['name'] }}</h1>
                    <p class="mt-3 bg-gradient-to-r from-indigo-600 to-fuchsia-600 bg-clip-text text-lg font-semibold text-transparent dark:from-indigo-400 dark:to-fuchsia-400">{{ $resume['title'] }}</p>

                    @if (!empty($resume['location']))
                        <p class="mt-4 inline-flex items-center gap-1.5 rounded-full border border-zinc-200 px-3 py-1 text-xs font-medium text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
                            <x-icon name="map-pin" class="h-3.5 w-3.5" />
                            {{ $resume['location'] }}
                        </p>
                    @endif

                    <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                        @foreach ($resume['social'] as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link['label'] }}"
                               class="rounded-full border border-zinc-200 p-2.5 text-zinc-600 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:text-zinc-400 {{ $brandHover[$link['icon']] ?? '' }}">
                                <x-icon :name="$link['icon']" class="h-5 w-5" />
                            </a>
                        @endforeach

                        @if (!empty($resume['resume_url']))
                            <a href="{{ $resume['resume_url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-500/30">
                                <x-icon name="download" class="h-4 w-4" />
                                Download Resume
                            </a>
                        @endif
                    </div>

                    <a href="#about" aria-label="Scroll to content" class="mt-16 text-zinc-300 transition-colors hover:text-indigo-500 dark:text-zinc-700 dark:hover:text-indigo-400">
                        <x-icon name="chevron-down" class="h-6 w-6 animate-bounce" />
                    </a>
                </div>
            </section>

            {{-- About --}}
            <section id="about" class="mx-auto max-w-3xl scroll-mt-20 px-6 py-16">
                <x-section-heading eyebrow="01" title="About" color="indigo" />
                <p class="reveal text-lg leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $resume['about'] }}</p>
            </section>

            {{-- Experience --}}
            <section id="experience" class="scroll-mt-20 bg-gradient-to-b from-indigo-50/50 via-white to-white px-6 py-20 dark:from-indigo-950/10 dark:via-zinc-950 dark:to-zinc-950">
                <div class="mx-auto max-w-3xl">
                    <x-section-heading eyebrow="02" title="Experience" color="indigo" />

                    <ol class="reveal relative ml-3">
                        <div class="absolute top-0 left-0 h-full w-px bg-gradient-to-b from-indigo-400 via-violet-300 to-transparent dark:from-indigo-600 dark:via-violet-800"></div>

                        @foreach ($resume['experience'] as $job)
                            <li class="relative py-1 pb-10 pl-8 last:pb-0">
                                <span class="absolute top-2 -left-[7px] h-3.5 w-3.5 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 ring-4 ring-white shadow-sm dark:ring-zinc-950"></span>

                                <div class="rounded-2xl border border-transparent p-4 -m-4 transition-colors hover:border-zinc-200 hover:bg-white hover:shadow-sm dark:hover:border-zinc-800 dark:hover:bg-zinc-900/50">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $job['role'] }}</h3>
                                        <span class="text-sm whitespace-nowrap text-zinc-500 dark:text-zinc-500">{{ $job['period'] }}</span>
                                    </div>
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ $job['company'] }}@if (!empty($job['location'])) &middot; {{ $job['location'] }}@endif
                                    </p>

                                    @if (!empty($job['summary']))
                                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $job['summary'] }}</p>
                                    @endif

                                    @if (!empty($job['highlights']))
                                        <ul class="mt-3 space-y-1.5">
                                            @foreach ($job['highlights'] as $highlight)
                                                <li class="flex gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                    <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-500 dark:text-indigo-400" />
                                                    <span>{{ $highlight }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Skills --}}
            <section id="skills" class="mx-auto max-w-3xl scroll-mt-20 px-6 py-20">
                <x-section-heading eyebrow="03" title="Skills" color="violet" />

                <div class="reveal flex flex-col gap-7">
                    @foreach ($resume['skills'] as $category => $items)
                        @php $sc = $skillColors[$loop->index % count($skillColors)]; @endphp
                        <div>
                            <h3 class="mb-2.5 text-xs font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">{{ $category }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($items as $skill)
                                    <span class="rounded-full px-3 py-1 text-sm font-medium transition-transform hover:-translate-y-0.5 {{ $skillPill[$sc] }}">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Certifications --}}
            <section id="certifications" class="scroll-mt-20 bg-gradient-to-b from-amber-50/50 via-white to-white px-6 py-20 dark:from-amber-950/10 dark:via-zinc-950 dark:to-zinc-950">
                <div class="mx-auto max-w-3xl">
                    <x-section-heading eyebrow="04" title="Certifications" color="amber" />

                    <div class="reveal grid gap-4 sm:grid-cols-2">
                        @foreach ($resume['certifications'] as $cert)
                            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900/40 dark:hover:border-amber-700">
                                <div class="flex items-start gap-3">
                                    <span class="rounded-full bg-gradient-to-br from-amber-400 to-orange-500 p-2 text-white shadow-sm">
                                        <x-icon name="badge" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ $cert['name'] }}</h3>
                                        <p class="text-sm text-zinc-500 dark:text-zinc-500">{{ $cert['issuer'] }}</p>
                                        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-600">{{ $cert['date'] }}</p>
                                        @if (!empty($cert['url']))
                                            <a href="{{ $cert['url'] }}" target="_blank" rel="noopener noreferrer"
                                               class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-amber-600 hover:underline dark:text-amber-400">
                                                View credential
                                                <x-icon name="external" class="h-3.5 w-3.5" />
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Development Projects — the one section of the site specifically about the dev/hobby side,
                 set apart visually from the general professional resume above it. --}}
            <section id="projects" class="scroll-mt-20 bg-gradient-to-b from-violet-50/60 via-indigo-50/30 to-white px-6 py-20 dark:from-violet-950/10 dark:via-zinc-900/40 dark:to-zinc-950">
                <div class="mx-auto max-w-5xl">
                    <x-section-heading eyebrow="05 · DEVELOPMENT" title="Development Projects" color="violet" />

                    @if (!empty($resume['projects_intro']))
                        <p class="reveal mb-8 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $resume['projects_intro'] }}</p>
                    @endif

                    <div class="reveal grid gap-6 sm:grid-cols-2">
                        @foreach ($resume['projects'] as $project)
                            @php $pc = $projectColors[$loop->index % count($projectColors)]; @endphp
                            <div class="group flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl dark:border-zinc-800 dark:bg-zinc-950 {{ $projectHoverBorder[$pc] }}">
                                <div class="h-1.5 w-full bg-gradient-to-r {{ $projectBar[$pc] }}"></div>

                                <div class="flex flex-1 flex-col p-6">
                                    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $project['name'] }}</h3>
                                        @if (!empty($project['started']))
                                            <span class="inline-flex items-center gap-1 text-xs whitespace-nowrap text-zinc-500 dark:text-zinc-500">
                                                <x-icon name="calendar" class="h-3.5 w-3.5" />
                                                {{ $project['started'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if (!empty($project['tech']))
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($project['tech'] as $tech)
                                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $projectTag[$pc] }}">{{ $tech }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <p class="mt-3 flex-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $project['description'] }}</p>

                                    @if (!empty($project['url']) || !empty($project['repo']))
                                        <div class="mt-4 flex gap-4 text-sm font-medium">
                                            @if (!empty($project['url']))
                                                <a href="{{ $project['url'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 text-indigo-600 hover:underline dark:text-indigo-400">
                                                    <x-icon name="external" class="h-3.5 w-3.5" />
                                                    Website
                                                </a>
                                            @endif
                                            @if (!empty($project['repo']))
                                                <a href="{{ $project['repo'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 text-zinc-600 hover:underline dark:text-zinc-400">
                                                    <x-icon name="github" class="h-4 w-4" />
                                                    GitHub
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Contact --}}
            <section id="contact" class="scroll-mt-20 bg-gradient-to-b from-indigo-50/50 via-white to-white px-6 py-20 dark:from-indigo-950/10 dark:via-zinc-950 dark:to-zinc-950">
                <div class="reveal mx-auto max-w-xl text-center">
                    <x-section-heading eyebrow="06" title="Get in touch" color="rose" align="center" />
                    <p class="mx-auto max-w-md text-zinc-600 dark:text-zinc-400">
                        Have a role, a project, or just want to say hi? I'd love to hear from you.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        @foreach ($resume['social'] as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link['label'] }}"
                               class="rounded-full border border-zinc-200 p-2.5 text-zinc-600 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:text-zinc-400 {{ $brandHover[$link['icon']] ?? '' }}">
                                <x-icon :name="$link['icon']" class="h-5 w-5" />
                            </a>
                        @endforeach
                    </div>

                    @if (session('contact_status') === 'success')
                        <p class="mt-8 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-left text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-400">
                            Thanks — your message has been sent. I'll get back to you soon.
                        </p>
                    @endif

                    <form method="POST" action="{{ route('contact.send') }}" class="mt-8 space-y-4 rounded-2xl border border-zinc-200 bg-white p-6 text-left shadow-sm dark:border-zinc-800 dark:bg-zinc-900/40">
                        @csrf

                        {{-- Honeypot: hidden from real visitors, bots tend to fill every field they find. --}}
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label for="company">Company</label>
                            <input type="text" name="company" id="company" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <label for="name" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                   class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="message" class="mb-1 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Message</label>
                            <textarea name="message" id="message" rows="5" required
                                      class="w-full rounded-lg border border-zinc-300 bg-white px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 transition-colors focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full rounded-lg bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-500/30">
                            Send Message
                        </button>
                    </form>
                </div>
            </section>

        </main>

        <footer class="relative py-10 text-center text-sm text-zinc-500 dark:text-zinc-500">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-zinc-300 to-transparent dark:via-zinc-700"></div>
            <p>&copy; {{ date('Y') }} {{ $resume['name'] }}. Built with Laravel &amp; Tailwind CSS.</p>
            <a href="#top" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-zinc-400 transition-colors hover:text-indigo-500 dark:text-zinc-600 dark:hover:text-indigo-400">
                <x-icon name="arrow-up" class="h-3.5 w-3.5" />
                Back to top
            </a>
        </footer>

        <script>
            document.getElementById('theme-toggle')?.addEventListener('click', function () {
                var isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });

            var mobileMenu = document.getElementById('mobile-menu');
            document.getElementById('menu-toggle')?.addEventListener('click', function () {
                mobileMenu?.classList.toggle('hidden');
            });
            mobileMenu?.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () { mobileMenu.classList.add('hidden'); });
            });

            document.addEventListener('DOMContentLoaded', function () {
                if (!('IntersectionObserver' in window)) return;

                var revealEls = document.querySelectorAll('.reveal');
                revealEls.forEach(function (el) { el.classList.add('reveal-init'); });

                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });

                revealEls.forEach(function (el) { observer.observe(el); });
            });
        </script>
    </body>
</html>
