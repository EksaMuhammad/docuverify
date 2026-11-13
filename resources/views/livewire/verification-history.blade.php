<div class="space-y-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="w-full sm:w-80">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search by code, hash, or IP..."
                   class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <select wire:model.live="statusFilter" class="text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-700">
                <option value="all">All Statuses</option>
                <option value="VALID">VALID</option>
                <option value="MODIFIED">MODIFIED</option>
                <option value="UNVERIFIABLE">UNVERIFIABLE</option>
                <option value="TECHNICAL_ERROR">TECHNICAL ERROR</option>
            </select>

            <a href="{{ route('admin.research.export-verifications') }}" class="inline-flex items-center px-3 py-2 border border-slate-300 text-xs font-semibold rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition">
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
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">Document Code</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Uploaded SHA-256</th>
                        <th class="px-5 py-3">Processing Latency</th>
                        <th class="px-5 py-3">Client IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                                {{ $log->document_code }}
                            </td>
                            <td class="px-5 py-3.5">
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
                            <td class="px-5 py-3.5 font-mono text-slate-500 max-w-[160px] truncate" title="{{ $log->uploaded_hash }}">
                                {{ substr($log->uploaded_hash, 0, 16) }}...
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-600">
                                {{ $log->processing_time_ms }} ms
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                No verification audit entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
