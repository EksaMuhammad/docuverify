<div class="space-y-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
        <div class="w-full sm:w-80">
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search activity, description, or IP..."
                   class="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Timestamp</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($activities as $act)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap">
                                {{ $act->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">
                                {{ $act->user?->name ?? 'System' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $act->activity }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $act->description }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 font-mono">
                                {{ $act->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                No activity logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $activities->links() }}
        </div>
    </div>
</div>

