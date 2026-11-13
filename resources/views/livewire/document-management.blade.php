<div class="space-y-6">
    <!-- Controls Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="w-full sm:w-80">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search by code, title, or hash..."
                   class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="flex items-center space-x-3 w-full sm:w-auto justify-end">
            <select wire:model.live="statusFilter" class="text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-700">
                <option value="all">All Statuses</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>

            <a href="{{ route('admin.documents.create') }}" class="inline-flex items-center px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                + Register PDF
            </a>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Code / ID</th>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">SHA-256 Fingerprint</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Block #</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($documents as $doc)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                                <a href="{{ route('admin.documents.show', $doc->id) }}" class="hover:text-blue-600">
                                    {{ $doc->document_code }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-800 max-w-[200px] truncate">
                                {{ $doc->document_name }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-500 max-w-[140px] truncate" title="{{ $doc->document_hash }}">
                                {{ substr($doc->document_hash, 0, 16) }}...
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($doc->registration_status === 'confirmed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                        Confirmed
                                    </span>
                                @elseif ($doc->registration_status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-slate-600">
                                {{ $doc->block_number ? '#' . $doc->block_number : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $doc->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.documents.show', $doc->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                    Audit Detail
                                </a>
                                <a href="{{ route('verify', ['code' => $doc->document_code]) }}" target="_blank" class="text-slate-500 hover:text-slate-800">
                                    Verify
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                No registered documents found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $documents->links() }}
        </div>
    </div>
</div>

