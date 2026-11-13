<div class="space-y-8 max-w-6xl mx-auto">
    <!-- Header with CSV Export Actions -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Academic Research & Evaluation Panel</h2>
            <p class="text-xs text-slate-500 mt-1">
                Quantitative metrics, confusion matrix, system latency benchmarks, and System Usability Scale (SUS) analysis for thesis/skripsi publication.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.research.export-verifications') }}" class="inline-flex items-center px-3.5 py-2 border border-slate-300 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition shadow-xs">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Verifications CSV
            </a>
            <a href="{{ route('admin.research.export-transactions') }}" class="inline-flex items-center px-3.5 py-2 border border-slate-300 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition shadow-xs">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Transactions CSV
            </a>
        </div>
    </div>

    <!-- 1. Classification & Confusion Matrix -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 uppercase tracking-wider">
            1. Classification & Accuracy Performance (Confusion Matrix)
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                <span class="text-[11px] font-bold text-emerald-800 uppercase block">Accuracy</span>
                <span class="text-2xl font-extrabold text-emerald-700">{{ $accuracy }}%</span>
            </div>
            <div class="p-4 bg-blue-50 rounded-xl border border-blue-200 text-center">
                <span class="text-[11px] font-bold text-blue-800 uppercase block">Precision</span>
                <span class="text-2xl font-extrabold text-blue-700">{{ $precision }}%</span>
            </div>
            <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-200 text-center">
                <span class="text-[11px] font-bold text-indigo-800 uppercase block">Recall</span>
                <span class="text-2xl font-extrabold text-indigo-700">{{ $recall }}%</span>
            </div>
            <div class="p-4 bg-purple-50 rounded-xl border border-purple-200 text-center">
                <span class="text-[11px] font-bold text-purple-800 uppercase block">F1-Score</span>
                <span class="text-2xl font-extrabold text-purple-700">{{ $f1Score }}%</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-center border border-slate-200 rounded-lg">
                <thead class="bg-slate-50 font-bold text-slate-700 uppercase">
                    <tr>
                        <th class="p-3 border">Class (Total: {{ $tp + $tn }})</th>
                        <th class="p-3 border">Predicted Authentic (VALID)</th>
                        <th class="p-3 border">Predicted Non-Authentic (MODIFIED / UNVERIFIABLE)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="p-3 font-bold bg-slate-50 border text-left">Actual Authentic Documents</td>
                        <td class="p-3 border bg-emerald-50 text-emerald-800 font-bold">
                            TP = {{ $tp }}
                        </td>
                        <td class="p-3 border text-slate-400">
                            FN = {{ $fn }}
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold bg-slate-50 border text-left">Actual Tampered / Unregistered Documents</td>
                        <td class="p-3 border text-slate-400">
                            FP = {{ $fp }}
                        </td>
                        <td class="p-3 border bg-blue-50 text-blue-800 font-bold">
                            TN = {{ $tn }} ({{ $modifiedCount }} Modified, {{ $unverifiableCount }} Unverifiable)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 italic">
            * Note: Because SHA-256 is collision-resistant and smart contracts enforce deterministic equality, FP and FN are guaranteed to be 0 for standard digital files.
        </p>
    </div>

    <!-- 2. System Latency & Gas Performance -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Latency Metrics -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 uppercase tracking-wider">
                2. Processing Latency Benchmarks
            </h3>
            <div class="space-y-4 text-xs">
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Average Verification Time:</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ round($avgProcessingTime, 1) }} ms</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Fastest Verification (Min):</span>
                    <span class="font-mono font-bold text-emerald-700 text-sm">{{ $minProcessingTime }} ms</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Slowest Verification (Max):</span>
                    <span class="font-mono font-bold text-slate-700 text-sm">{{ $maxProcessingTime }} ms</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Total Evaluated Verification Queries:</span>
                    <span class="font-mono font-bold text-blue-700 text-sm">{{ $totalVerifications }} trials</span>
                </div>
            </div>
        </div>

        <!-- Gas Metrics -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4 uppercase tracking-wider">
                3. Blockchain Gas & Overhead
            </h3>
            <div class="space-y-4 text-xs">
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Total Cumulative Gas Consumed:</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ number_format($totalGasUsed) }} units</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Average Gas per Registration:</span>
                    <span class="font-mono font-bold text-blue-700 text-sm">{{ number_format($avgGasUsed) }} units</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Total On-Chain Transactions:</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ $totalTransactions }} transactions</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                    <span class="text-slate-600 font-medium">Theoretical Cost @ 20 Gwei & $2,500/ETH:</span>
                    <span class="font-mono font-bold text-emerald-700 text-sm">~${{ round(($avgGasUsed * 20 * 1e-9) * 2500, 4) }} / doc</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. System Usability Scale (SUS) Evaluator -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-3 border-b border-slate-100 mb-6 gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    4. System Usability Scale (SUS) Evaluator
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    10 standardized questionnaire items (1 = Strongly Disagree, 5 = Strongly Agree). Score calculated using Bangor et al. methodology.
                </p>
            </div>

            @if ($calculatedSusScore !== null)
                <div class="bg-blue-50 border border-blue-200 px-4 py-2 rounded-lg text-right">
                    <span class="text-[10px] text-blue-700 uppercase font-bold block">Calculated SUS Score</span>
                    <span class="text-2xl font-black text-blue-900">{{ number_format($calculatedSusScore, 1) }} / 100</span>
                    <span class="text-xs font-semibold text-blue-700 block">{{ $susGrade }}</span>
                </div>
            @endif
        </div>

        @php
            $susQuestions = [
                1 => 'I think that I would like to use this system frequently.',
                2 => 'I found the system unnecessarily complex.',
                3 => 'I thought the system was easy to use.',
                4 => 'I think that I would need the support of a technical person to be able to use this system.',
                5 => 'I found the various functions in this system were well integrated.',
                6 => 'I thought there was too much inconsistency in this system.',
                7 => 'I would imagine that most people would learn to use this system very quickly.',
                8 => 'I found the system very cumbersome to use.',
                9 => 'I felt very confident using the system.',
                10 => 'I needed to learn a lot of things before I could get going with this system.',
            ];
        @endphp

        <div class="space-y-4">
            @foreach ($susQuestions as $qNum => $qText)
                <div class="p-3 bg-slate-50 rounded-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                    <span class="font-medium text-slate-800 max-w-xl">
                        <strong class="text-slate-900">{{ $qNum }}.</strong> {{ $qText }}
                    </span>
                    <div class="flex items-center space-x-2">
                        @for ($score = 1; $score <= 5; $score++)
                            <label class="flex flex-col items-center cursor-pointer">
                                <input type="radio" wire:model.live="susAnswers.{{ $qNum }}" value="{{ $score }}" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-[10px] text-slate-500 mt-0.5">{{ $score }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
