<x-layouts.admin title="Operational Dashboard">
    <div class="space-y-6">
        <!-- Blockchain Service Status Banner -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg {{ ($blockchainHealth['rpcConnected'] ?? false) ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }} flex items-center justify-center font-bold">
                    @if ($blockchainHealth['rpcConnected'] ?? false)
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @endif
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        Blockchain Service Status:
                        <span class="{{ ($blockchainHealth['rpcConnected'] ?? false) ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ ($blockchainHealth['rpcConnected'] ?? false) ? 'Active & Synchronized' : 'Offline / Standby' }}
                        </span>
                    </h3>
                    <div class="text-xs text-slate-500 flex flex-wrap gap-x-4 mt-0.5">
                        <span>Network: <strong class="text-slate-700">{{ $blockchainHealth['network'] ?? 'Hardhat Local / Sepolia' }}</strong></span>
                        <span>Contract: <strong class="text-slate-700 font-mono">{{ !empty($blockchainHealth['contractAddress']) ? substr($blockchainHealth['contractAddress'], 0, 10) . '...' : 'Configured via Hardhat' }}</strong></span>
                        @if (!empty($blockchainHealth['latestBlock']))
                            <span>Latest Block: <strong class="text-slate-700">#{{ $blockchainHealth['latestBlock'] }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.documents.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Register New PDF
            </a>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Documents -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Registered Documents</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalDocuments) }}</span>
                    <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded">Real Database</span>
                </div>
            </div>

            <!-- Confirmed On-Chain -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Confirmed Transactions</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-blue-600">{{ number_format($confirmedTransactions) }}</span>
                    <span class="text-xs text-slate-500 font-medium">EVM Anchored</span>
                </div>
            </div>

            <!-- Total Verifications -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Public Verifications</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-slate-900">{{ number_format($totalVerifications) }}</span>
                    <span class="text-xs text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded">{{ $validVerifications }} Valid</span>
                </div>
            </div>

            <!-- Tampering Detected -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tampering / Modified</span>
                <div class="mt-2 flex items-baseline justify-between">
                    <span class="text-3xl font-extrabold text-red-600">{{ number_format($modifiedDetections) }}</span>
                    <span class="text-xs text-red-600 font-semibold bg-red-50 px-2 py-0.5 rounded">Integrity Violations</span>
                </div>
            </div>
        </div>

        <!-- Recent Documents and Verifications Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Documents -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-800">Recent Document Registrations</h3>
                    <a href="{{ route('admin.documents.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All &rarr;</a>
                </div>

                @if ($recentDocuments->isEmpty())
                    <p class="text-xs text-slate-400 py-6 text-center">No documents registered yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left">
                            <thead class="text-slate-400 uppercase font-semibold">
                                <tr>
                                    <th class="py-2">Code</th>
                                    <th class="py-2">Name</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentDocuments as $doc)
                                    <tr>
                                        <td class="py-2.5 font-mono font-bold text-slate-800">
                                            <a href="{{ route('admin.documents.show', $doc->id) }}" class="hover:text-blue-600">{{ $doc->document_code }}</a>
                                        </td>
                                        <td class="py-2.5 text-slate-700 truncate max-w-[120px]">{{ $doc->document_name }}</td>
                                        <td class="py-2.5">
                                            @if ($doc->registration_status === 'confirmed')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Confirmed</span>
                                            @elseif ($doc->registration_status === 'pending')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Failed</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-slate-500">{{ $doc->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Recent Verifications -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h3 class="text-sm font-bold text-slate-800">Recent Verification Activity</h3>
                    <a href="{{ route('admin.verification-logs.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">View All &rarr;</a>
                </div>

                @if ($recentVerifications->isEmpty())
                    <p class="text-xs text-slate-400 py-6 text-center">No verifications recorded yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-left">
                            <thead class="text-slate-400 uppercase font-semibold">
                                <tr>
                                    <th class="py-2">Code</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Latency</th>
                                    <th class="py-2">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentVerifications as $log)
                                    <tr>
                                        <td class="py-2.5 font-mono text-slate-800">{{ $log->document_code }}</td>
                                        <td class="py-2.5">
                                            @if ($log->verification_status === 'VALID')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">VALID</span>
                                            @elseif ($log->verification_status === 'MODIFIED')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">MODIFIED</span>
                                            @elseif ($log->verification_status === 'UNVERIFIABLE')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">UNVERIFIABLE</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800">TECHNICAL ERROR</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-slate-500 font-mono">{{ $log->processing_time_ms }} ms</td>
                                        <td class="py-2.5 text-slate-500">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
