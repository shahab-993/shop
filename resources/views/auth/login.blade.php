
<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ps', 'fa']) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Grocery Shop</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f1f5f3] antialiased">

    <main class="flex min-h-screen items-center justify-center px-5 py-10">

        {{-- Login Card --}}
        <div class="w-full max-w-[460px] overflow-hidden rounded-[28px] border border-white bg-white shadow-[0_25px_70px_rgba(15,23,42,0.10)]">

            {{-- Top Brand Section --}}
            <div class="px-8 pb-7 pt-10 text-center sm:px-12">

                {{-- Logo --}}
                <div class="mx-auto flex h-[76px] w-[76px] items-center justify-center rounded-[24px] bg-emerald-600 text-white shadow-lg shadow-emerald-600/20">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-10 w-10"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.7"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 3h2l2.4 12.2a2 2 0 002 1.6h8.8a2 2 0 002-1.6L22 8H6"
                        />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>

                </div>

                <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-900">
                    Grocery Shop
                </h1>

                <p class="mt-1 text-sm font-medium text-slate-400">
                    Management System
                </p>

                <div class="mx-auto mt-7 h-1 w-12 rounded-full bg-emerald-500"></div>

            </div>

            {{-- Form Section --}}
            <div class="px-8 pb-9 sm:px-12">

                <div class="mb-7 text-center">

                    <h2 class="text-2xl font-bold text-slate-800">
                        Welcome Back!
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Sign in to access your admin dashboard.
                    </p>

                </div>

                {{-- Status Message --}}
                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">

                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">

                    @csrf

                    {{-- Username --}}
                    <div>

                        <label
                            for="username"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Username
                        </label>

                        <div class="relative">

                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400">

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m12-13a4 4 0 11-8 0 4 4 0 018 0zm4 13v-2a4 4 0 00-3-3.87"
                                    />
                                </svg>

                            </div>

                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="{{ old('username') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your username"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pe-4 ps-12 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                            >

                        </div>

                    </div>

                    {{-- Password --}}
                    <div>

                        <div class="mb-2 flex items-center justify-between">

                            <label
                                for="password"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Password
                            </label>

                        </div>

                        <div class="relative">

                            <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-4 text-slate-400">

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <rect x="4" y="10" width="16" height="11" rx="2" />
                                    <path stroke-linecap="round" d="M8 10V7a4 4 0 118 0v3" />
                                </svg>

                            </div>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3.5 pe-12 ps-12 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                            >

                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute inset-y-0 end-0 flex items-center pe-4 text-slate-400 transition hover:text-emerald-600"
                                aria-label="Show or hide password"
                            >
                                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>

                        </div>

                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between pt-1">

                        <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">

                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >

                            Remember me

                        </label>

                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-4 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition duration-200 hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-xl active:translate-y-0"
                    >

                        Sign In

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7l7 7-7 7" />
                        </svg>

                    </button>

                </form>

                {{-- Footer --}}
                <div class="mt-8 border-t border-slate-100 pt-6 text-center">

                    <div class="flex items-center justify-center gap-2 text-xs text-slate-400">

                        <svg
                            class="h-4 w-4 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
                        </svg>

                        Secure Administrator Access

                    </div>

                    <p class="mt-4 text-xs text-slate-400">
                        © {{ date('Y') }} Grocery Shop. All rights reserved.
                    </p>

                </div>

            </div>

        </div>

    </main>

    {{-- Password Visibility --}}
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (password.type === 'password') {
                password.type = 'text';

                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A10.5 10.5 0 0112 5c6 0 9.5 7 9.5 7a15 15 0 01-3 3.8M6.2 6.2C3.9 7.8 2.5 12 2.5 12s3.5 7 9.5 7c1 0 2-.2 2.8-.5"
                    />
                `;
            } else {
                password.type = 'password';

                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"
                    />
                    <circle cx="12" cy="12" r="3" />
                `;
            }
        }
    </script>

</body>
</html>
