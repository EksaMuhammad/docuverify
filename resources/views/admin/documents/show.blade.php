<x-layouts.admin title="Document Integrity Audit Record">
    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Back navigation -->
        <div>
            <a href="{{ route('admin.documents.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center">
                &larr; Back to Documents
            </a>
        </div>

        <!-- On-Chain Reconciliation Banner -->
        @php
            $onChainRegistered = $onChainRecord['success'] ?? false;
            $onChainHash = $onChainRecord['blockchainHash'] ?? null;
            $hashesMatch = $onChainHash && (strtolower($onChainHash) === strtolower('0x' . $document->document_hash) || strtolower($onChainHash) === strtolower($document->document_hash));
        @endphp

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg {{ $hashesMatch ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center font-bold">
                    @if ($hashesMatch)
                        ✓
                    @else
                        ?
                    @endif
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        Blockchain State Reconciliation:
                        <span class="{{ $hashesMatch ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $hashesMatch ? '100% Synchronized & Verified On-Chain' : 'Pending or Discrepancy Detected' }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Smart contract record was queried live from the EVM node at page render.
                    </p>
                </div>
            </div>

            <a href="{{ route('verify', ['code' => $document->document_code]) }}" target="_blank"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold shadow-xs">
                Test Public Verification
            </a>
        </div>

        <!-- Document Details Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Document Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Document Title</span>
                    <span class="text-sm font-bold text-slate-900">{{ $document->document_name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Document Code</span>
                    <span class="text-sm font-mono font-bold text-blue-600">{{ $document->document_code }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Derivation ID (bytes32 keccak256)</span>
                    <code class="block font-mono bg-slate-50 p-2 rounded border border-slate-200 text-slate-700 break-all mt-1">
                        {{ $document->document_id }}
                    </code>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">SHA-256 Byte Fingerprint</span>
                    <code class="block font-mono bg-slate-50 p-2 rounded border border-slate-200 text-slate-700 break-all mt-1">
                        {{ $document->document_hash }}
                    </code>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Registered By</span>
                    <span class="text-slate-800 font-medium">{{ $document->registrar?->name ?? 'System' }} ({{ $document->registrar?->email ?? 'N/A' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Original File Size</span>
                    <span class="text-slate-800 font-medium">{{ $document->file_size ? round($document->file_size / 1024, 1) . ' KB' : 'N/A' }}</span>
                </div>
                @if ($document->notes)
                    <div class="md:col-span-2">
                        <span class="text-slate-400 font-semibold block uppercase">Administrative Notes</span>
                        <p class="text-slate-700 mt-1 italic">{{ $document->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Blockchain Transaction Details Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Blockchain Audit & Transaction Proof</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="md:col-span-2">
                    <span class="text-slate-400 font-semibold block uppercase">Transaction Hash</span>
                    @if ($document->transaction_hash)
                        <code class="block font-mono bg-blue-50 text-blue-900 p-2.5 rounded border border-blue-200 break-all mt-1">
                            {{ $document->transaction_hash }}
                        </code>
                    @else
                        <span class="text-amber-600 font-semibold">No confirmed transaction hash recorded</span>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Block Number</span>
                    <span class="font-mono text-slate-800 font-bold">{{ $document->block_number ? '#' . $document->block_number : 'Pending' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 font-semibold block uppercase">Gas Consumed</span>
                    <span class="font-mono text-slate-800 font-bold">{{ $document->gas_used ? number_format($document->gas_used) . ' units' : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Associated Verification Logs -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">Verification Query Log</h2>
            @if ($document->verificationLogs->isEmpty())
                <p class="text-xs text-slate-400 py-4 text-center">No public verifications have been conducted for this document code yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 font-semibold uppercase">
                            <tr>
                                <th class="px-4 py-2">Timestamp</th>
                                <th class="px-4 py-2">Result</th>
                                <th class="px-4 py-2">Uploaded Hash</th>
                                <th class="px-4 py-2">Latency</th>
                                <th class="px-4 py-2">Client IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($document->verificationLogs as $vLog)
                                <tr>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $vLog->created_at->format('d M Y H:i:s') }}</td>
                                    <td class="px-4 py-2.5">
                                        @if ($vLog->verification_status === 'VALID')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">VALID</span>
                                        @elseif ($vLog->verification_status === 'MODIFIED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800">MODIFIED</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">{{ $vLog->verification_status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-slate-500 max-w-[120px] truncate" title="{{ $vLog->uploaded_hash }}">
                                        {{ substr($vLog->uploaded_hash, 0, 16) }}...
                                    </td>
                                    <td class="px-4 py-2.5 font-mono text-slate-600">{{ $vLog->processing_time_ms }} ms</td>
                                    <td class="px-4 py-2.5 text-slate-500">{{ $vLog->ip_address ?? '127.0.0.1' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>

