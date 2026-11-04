
<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ps', 'fa']) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') | {{ config('app.name', 'Grocery Shop') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    <div class="min-h-screen">

        {{-- Mobile Sidebar Overlay --}}
        <div
            id="sidebarOverlay"
            class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
            onclick="toggleSidebar()"
        ></div>

        {{-- Sidebar --}}
        <aside
            id="sidebar"
            class="fixed inset-y-0 start-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-900 text-white transition-transform duration-300 rtl:translate-x-full lg:translate-x-0"
        >

            {{-- Brand --}}
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500 text-xl font-bold">
                    G
                </div>

                <div>
                    <h1 class="text-lg font-bold">Grocery Shop</h1>
                    <p class="text-xs text-slate-400">Management System</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6">

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Main Menu
                </p>

                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white"
                >
                    <span>▦</span>
                    Dashboard
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>▣</span>
                    Products
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>▤</span>
                    Categories
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>⇩</span>
                    Purchases
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>⇧</span>
                    Sales
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>♙</span>
                    Suppliers
                </a>

                <div class="my-5 border-t border-white/10"></div>

                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    Management
                </p>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>▧</span>
                    Expenses
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>▥</span>
                    Reports
                </a>

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-slate-300 transition hover:bg-white/10 hover:text-white">
                    <span>⚙</span>
                    Settings
                </a>

            </nav>

            {{-- Sidebar Footer --}}
            <div class="border-t border-white/10 p-4">
                <p class="text-xs text-slate-400">
                    Grocery Shop Management
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Version 1.0
                </p>
            </div>

        </aside>

        {{-- Main Area --}}
        <div class="min-h-screen lg:ms-72">

            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6 lg:px-8">

                <div class="flex items-center gap-4">

                    {{-- Mobile Menu Button --}}
                    <button
                        type="button"
                        onclick="toggleSidebar()"
                        class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                        aria-label="Toggle sidebar"
                    >
                        ☰
                    </button>

                    <div>
                        <h2 class="text-lg font-bold text-slate-800">
                            @yield('page-heading', 'Dashboard')
                        </h2>

                        <p class="hidden text-xs text-slate-500 sm:block">
                            Welcome to your management panel
                        </p>
                    </div>

                </div>

                {{-- User Menu --}}
                <div class="flex items-center gap-3">

                    <div class="hidden text-end sm:block">
                        <p class="text-sm font-semibold text-slate-800">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            Administrator
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-600"
                        >
                            Logout
                        </button>
                    </form>

                </div>

            </header>

            {{-- Page Content --}}
            <main class="p-4 sm:p-6 lg:p-8">

                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')

            </main>

        </div>

    </div>

    {{-- Mobile Sidebar Script --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('rtl:translate-x-full');

            overlay.classList.toggle('hidden');
        }
    </script>

</body>
</html>
