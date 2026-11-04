
@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-heading', 'Dashboard')

@section('content')

    {{-- Welcome Section --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">
            Welcome back, {{ auth()->user()->name }}!
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Here is an overview of your grocery shop.
        </p>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Sales --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">Today's Sales</p>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-xl text-emerald-600">
                    ↑
                </div>
            </div>

            <h2 class="mt-5 text-2xl font-bold text-slate-800">
                0 AFN
            </h2>

            <p class="mt-2 text-xs text-slate-400">
                Total completed sales today
            </p>
        </div>

        {{-- Total Purchases --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">Today's Purchases</p>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-xl text-blue-600">
                    ↓
                </div>
            </div>

            <h2 class="mt-5 text-2xl font-bold text-slate-800">
                0 AFN
            </h2>

            <p class="mt-2 text-xs text-slate-400">
                Total completed purchases today
            </p>
        </div>

        {{-- Cash Balance --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">Cash Balance</p>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-xl text-amber-600">
                    $
                </div>
            </div>

            <h2 class="mt-5 text-2xl font-bold text-slate-800">
                0 AFN
            </h2>

            <p class="mt-2 text-xs text-slate-400">
                Current cash balance
            </p>
        </div>

        {{-- Low Stock --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">Low Stock Products</p>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-xl text-red-600">
                    !
                </div>
            </div>

            <h2 class="mt-5 text-2xl font-bold text-slate-800">
                0
            </h2>

            <p class="mt-2 text-xs text-slate-400">
                Products below minimum stock
            </p>
        </div>

    </div>

    {{-- Secondary Statistics --}}
    <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">

        {{-- Inventory --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">
                Inventory Overview
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Current stock information
            </p>

            <div class="mt-6 flex items-center justify-between border-b border-slate-100 py-4">
                <span class="text-sm text-slate-600">Total Products</span>
                <span class="font-bold text-slate-800">0</span>
            </div>

            <div class="flex items-center justify-between border-b border-slate-100 py-4">
                <span class="text-sm text-slate-600">Total Categories</span>
                <span class="font-bold text-slate-800">0</span>
            </div>

            <div class="flex items-center justify-between py-4">
                <span class="text-sm text-slate-600">Inventory Value</span>
                <span class="font-bold text-emerald-600">0 AFN</span>
            </div>
        </div>

        {{-- Financial Overview --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">
                Financial Overview
            </h3>

            <p class="mt-1 text-sm text-slate-500">
                Today's financial summary
            </p>

            <div class="mt-6 flex items-center justify-between border-b border-slate-100 py-4">
                <span class="text-sm text-slate-600">Payments Received</span>
                <span class="font-bold text-emerald-600">0 AFN</span>
            </div>

            <div class="flex items-center justify-between border-b border-slate-100 py-4">
                <span class="text-sm text-slate-600">Expenses</span>
                <span class="font-bold text-red-600">0 AFN</span>
            </div>

            <div class="flex items-center justify-between py-4">
                <span class="text-sm text-slate-600">Customer Receivables</span>
                <span class="font-bold text-slate-800">0 AFN</span>
            </div>
        </div>

    </div>

    {{-- Recent Activity --}}
    <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-800">
                    Recent Activity
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Latest shop transactions
                </p>
            </div>

            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-500">
                No transactions yet
            </span>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[600px] text-start text-sm">

                <thead>
                    <tr class="border-b border-slate-200 text-slate-500">
                        <th class="px-4 py-3 text-start font-medium">Type</th>
                        <th class="px-4 py-3 text-start font-medium">Reference</th>
                        <th class="px-4 py-3 text-start font-medium">Amount</th>
                        <th class="px-4 py-3 text-start font-medium">Date</th>
                        <th class="px-4 py-3 text-start font-medium">Status</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
                            No recent activity available.
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

    </div>

@endsection
