<x-filament-panels::page>
    <div class="flex items-center justify-end gap-4 mb-6">
        <div class="w-[200px]">
            {{ $this->form }}
        </div>
    </div>

    @php
        $data = $this->reportData;
    @endphp

    <div class="space-y-6">
        <!-- Hero: Net Position -->
        <div
            class="mb-6 rounded-xl shadow-sm bg-white dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden relative">
            <div class="absolute top-0 left-0 w-2 h-full {{ $data['net'] >= 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
            <div class="p-6 md:p-8 flex flex-col md:flex-row items-center md:justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div
                        class="p-3 rounded-full {{ $data['net'] >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-950/50 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400' }}">
                        @if($data['net'] >= 0)
                            <x-heroicon-o-arrow-trending-up class="w-10 h-10" />
                        @else
                            <x-heroicon-o-arrow-trending-down class="w-10 h-10" />
                        @endif
                    </div>
                    <div>
                        <h2 class="text-xl font-medium text-gray-500 dark:text-gray-400">Net Cash Flow
                            ({{ $data['year'] }})</h2>
                        <div class="text-sm text-gray-400 mt-1">Total Inflows <span class="mx-1 text-gray-300">|</span>
                            Total Outflows</div>
                    </div>
                </div>

                <div
                    class="text-4xl md:text-5xl font-bold {{ $data['net'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    <span
                        class="text-2xl text-gray-400 font-medium align-top mr-1">KES</span>{{ number_format($data['net']) }}
                </div>
            </div>
        </div>

        <!-- Main Grid: Inflows vs Outflows -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Inflows Card -->
            <div class="bg-white rounded-xl shadow-md dark:bg-gray-800 overflow-hidden">
                <div class="p-6 bg-green-50 dark:bg-green-900/10 border-b border-green-100 dark:border-green-800/30">
                    <h3 class="text-xl font-semibold text-green-700 dark:text-green-400">Total Inflows</h3>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">
                        KES {{ number_format($data['inflows']) }}
                    </p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 text-lg">
                        <span>Total Contributions</span>
                        <span class="font-semibold text-gray-900 dark:text-white">KES
                            {{ number_format($data['total_contributions']) }}</span>
                    </div>
                    <!-- Contribution Breakdown -->
                    <div class="pl-4 space-y-2 border-l-2 border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Shares</span>
                            <span>{{ number_format($data['shares']) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Welfare</span>
                            <span>{{ number_format($data['welfare']) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Penalties</span>
                            <span>{{ number_format($data['penalty']) }}</span>
                        </div>
                    </div>

                    <div
                        class="border-t border-gray-100 dark:border-gray-700 pt-4 flex justify-between items-center text-gray-600 dark:text-gray-300 text-lg">
                        <span>Loan Repayments</span>
                        <span class="font-semibold text-gray-900 dark:text-white">KES
                            {{ number_format($data['loans_repaid']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Outflows Card -->
            <div class="bg-white rounded-xl shadow-md dark:bg-gray-800 overflow-hidden">
                <div class="p-6 bg-red-50 dark:bg-red-900/10 border-b border-red-100 dark:border-red-800/30">
                    <h3 class="text-xl font-semibold text-red-700 dark:text-red-400">Total Outflows</h3>
                    <p class="text-4xl font-bold text-gray-900 dark:text-white mt-2">
                        KES {{ number_format($data['outflows']) }}
                    </p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 text-lg">
                        <span>Loans Disbursed</span>
                        <span class="font-semibold text-gray-900 dark:text-white">KES
                            {{ number_format($data['loans_disbursed']) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600 dark:text-gray-300 text-lg">
                        <span>Expenditures</span>
                        <span class="font-semibold text-gray-900 dark:text-white">KES
                            {{ number_format($data['expenditures']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Cash Reconciliation & Float -->
        <div class="bg-white rounded-xl shadow-md dark:bg-gray-800 overflow-hidden">
            <div
                class="p-6 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Operational Cash Reconciliation
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Analysis of funds withdrawn by Treasurer</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 min-w-0">
                    <div class="text-left sm:text-right">
                        <span
                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Withdrawn</span>
                        <span class="block text-xl font-bold text-gray-900 dark:text-white mt-1">KES
                            {{ number_format($data['withdrawals']) }}</span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span
                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Spent</span>
                        <span class="block text-xl font-bold text-red-600 dark:text-red-400 mt-1">KES
                            {{ number_format($data['expenditures']) }}</span>
                    </div>
                    <div class="text-left sm:text-right">
                        <span
                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Returned</span>
                        <span class="block text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">KES
                            {{ number_format($data['cash_returns']) }}</span>
                    </div>
                    <div class="text-left sm:text-right pl-4 border-l border-gray-200 dark:border-gray-600">
                        <span
                            class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cash
                            at Hand</span>
                        <span
                            class="block text-xl font-bold {{ $data['cash_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                            KES {{ number_format($data['cash_balance']) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Float Analysis -->
            <div
                class="bg-gray-50 dark:bg-gray-700/30 p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <x-heroicon-o-scale class="w-5 h-5" />
                    <span>Treasurer Float Target: <span class="font-semibold">KES
                            {{ number_format($data['float_target']) }}</span></span>
                </div>

                @php
                    $variance = $data['cash_balance'] - $data['float_target'];
                    $statusColor = $variance == 0 ? 'text-green-600' : ($variance > 0 ? 'text-orange-600' : 'text-red-600');
                    $statusText = $variance == 0 ? 'Balanced' : ($variance > 0 ? 'Over Float (Needs Deposit)' : 'Under Float (Reimburse)');
                @endphp

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Variance:</span>
                    <span class="font-bold {{ $statusColor }}">
                        {{ $variance > 0 ? '+' : '' }}{{ number_format($variance) }} ({{ $statusText }})
                    </span>
                </div>
            </div>
        </div>

        <!-- MGR Pass-through Information -->
        <div class="bg-white rounded-xl shadow-md dark:bg-gray-800 overflow-hidden">
            <div
                class="p-6 bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800/30 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div
                        class="p-2 bg-white dark:bg-primary-900 rounded-full shadow-sm ring-1 ring-primary-100 dark:ring-primary-800 shrink-0">
                        <x-heroicon-o-arrow-path class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-primary-900 dark:text-primary-100">Merry Go Round
                            (Pass-Through)</h3>
                        <p class="text-sm text-primary-600 dark:text-primary-400 mt-1">Collected and immediately
                            distributed to members. Excluded from net cash flow.</p>
                    </div>
                </div>

                <div class="text-right">
                    <span
                        class="block text-sm font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wider">Total
                        Collections</span>
                    <span class="block text-3xl font-bold text-primary-900 dark:text-white mt-1">KES
                        {{ number_format($data['mgr']) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>