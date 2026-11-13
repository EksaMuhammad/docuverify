<div class="space-y-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="w-full sm:w-80">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search by transaction hash or document code..."
                   class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <select wire:model.live="statusFilter" class="text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-700">
                <option value="all">All Statuses</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>

            <a href="{{ route('admin.research.export-transactions') }}" class="inline-flex items-center px-3 py-2 border border-slate-300 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition">
                <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Tx Hash</th>
                        <th class="px-5 py-3">Document Code</th>
                        <th class="px-5 py-3">Block #</th>
                        <th class="px-5 py-3">Gas Used</th>
                        <th class="px-5 py-3">Network</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono text-blue-600 font-semibold max-w-[180px] truncate" title="{{ $tx->transaction_hash }}">
                                @if ($tx->explorer_url && $tx->network === 'sepolia')
                                    <a href="{{ $tx->explorer_url }}" target="_blank" class="hover:underline">
                                        {{ substr($tx->transaction_hash, 0, 16) }}...
                                    </a>
                                @else
                                    {{ substr($tx->transaction_hash, 0, 16) }}...
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                                @if ($tx->document)
                                    <a href="{{ route('admin.documents.show', $tx->document->id) }}" class="hover:text-blue-600">
                                        {{ $tx->document->document_code }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-700">
                                {{ $tx->block_number ? '#' . $tx->block_number : 'Pending' }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-600">
                                {{ $tx->gas_used ? number_format($tx->gas_used) : '—' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $tx->network }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($tx->transaction_status === 'confirmed')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Confirmed</span>
                                @elseif ($tx->transaction_status === 'pending')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">Failed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $tx->confirmed_at ? $tx->confirmed_at->format('d M Y H:i') : $tx->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No blockchain transactions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
