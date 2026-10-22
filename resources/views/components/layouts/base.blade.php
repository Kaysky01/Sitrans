<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Apply stored theme before paint (default is bright light theme) --}}
    <script>
        (function () {
            try {
                const stored = localStorage.getItem('sitrans-theme');
                if (stored === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased selection:bg-navy-500 selection:text-white dark:bg-espresso-950 dark:text-cream-100">
    {{ $slot }}
</body>
</html>
