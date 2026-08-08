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
        <title>Request Removed</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: 'class' };
        </script>
    </head>
    <body class="flex min-h-screen items-center justify-center bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100 font-sans">
        <div class="mx-auto max-w-md px-6 text-center">
            <p class="mb-4 text-4xl">🗑️</p>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Request removed</h1>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">"{{ $title }}" has been deleted from the request list.</p>
            <a href="{{ route('focr.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-fuchsia-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-indigo-500/30">
                Back to requests
            </a>
        </div>
    </body>
</html>
