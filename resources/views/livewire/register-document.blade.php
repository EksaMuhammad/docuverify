<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-xl font-bold text-slate-900">Register New Document to Blockchain</h2>
            <p class="text-xs text-slate-500 mt-1">
                Upload a verified PDF document. The system calculates its byte-level SHA-256 hash and permanently records the fingerprint on the smart contract.
            </p>
        </div>

        @if ($errorMessage)
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg text-xs text-red-700">
                <span class="font-bold">Registration Failed:</span> {{ $errorMessage }}
            </div>
        @endif

        @if ($registrationResult)
            <div class="mb-6 bg-emerald-50 border-2 border-emerald-500 rounded-xl p-5 text-emerald-900">
                <div class="flex items-center space-x-2 text-emerald-800 font-bold mb-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Document Certified & Confirmed on Blockchain!</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs pt-2">
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold text-[10px]">Document Code</span>
                        <span class="font-mono font-bold text-slate-900">{{ $registrationResult['documentCode'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block uppercase font-semibold text-[10px]">Block Number</span>
                        <span class="font-mono font-bold text-slate-900">#{{ $registrationResult['blockNumber'] }} (Gas: {{ number_format($registrationResult['gasUsed']) }})</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-slate-500 block uppercase font-semibold text-[10px]">Transaction Hash</span>
                        <code class="block bg-emerald-100 p-2 rounded text-[11px] font-mono break-all text-emerald-900 mt-0.5">
                            {{ $registrationResult['transactionHash'] }}
                        </code>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-slate-500 block uppercase font-semibold text-[10px]">SHA-256 Fingerprint</span>
                        <code class="block bg-emerald-100 p-2 rounded text-[11px] font-mono break-all text-emerald-900 mt-0.5">
                            {{ $registrationResult['documentHash'] }}
                        </code>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="register" class="space-y-5">
            <!-- Document Name -->
            <div>
                <label for="documentName" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Document Title / Safe Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="documentName" wire:model.defer="documentName"
                       placeholder="e.g. Master Thesis Official Approval Certificate 2026"
                       class="block w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-sm">
                @error('documentName') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Document Code -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="documentCode" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Generated Document Code <span class="text-red-500">*</span>
                    </label>
                    <button type="button" wire:click="generateNewCode" class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                        Regenerate Code
                    </button>
                </div>
                <input type="text" id="documentCode" wire:model.defer="documentCode"
                       class="block w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-slate-50 focus:ring-blue-500 focus:border-blue-500 text-sm font-mono tracking-wider uppercase font-bold text-slate-800">
                @error('documentCode') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Select PDF Document File <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-blue-400 bg-slate-50 transition">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="admin-pdf-upload" class="cursor-pointer bg-white rounded-md font-semibold text-blue-600 hover:text-blue-500 px-3 py-1 border border-slate-200 shadow-xs">
                                <span>Choose PDF File</span>
                                <input id="admin-pdf-upload" wire:model="file" type="file" accept=".pdf,application/pdf" class="sr-only">
                            </label>
                        </div>
                        <p class="text-xs text-slate-400">PDF file only. Temporary memory/storage used strictly for hashing and deleted immediately.</p>
                        @if ($file)
                            <div class="mt-2 text-xs text-emerald-700 font-semibold">
                                Selected: {{ $file->getClientOriginalName() }} ({{ round($file->getSize() / 1024, 1) }} KB)
                            </div>
                        @endif
                    </div>
                </div>
                @error('file') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Optional Notes -->
            <div>
                <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Internal Notes / Administrative Remarks (Optional)
                </label>
                <textarea id="notes" wire:model.defer="notes" rows="2"
                          placeholder="e.g. Registered for thesis defense trial batch 1"
                          class="block w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-xs text-slate-700"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Smart contract: <strong class="text-slate-700">DocumentRegistry</strong>
                </span>

                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-slate-900 hover:bg-slate-800 disabled:opacity-50 transition">
                    <span wire:loading.remove wire:target="register">Sign & Anchor to Blockchain</span>
                    <span wire:loading wire:target="register" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Hashing & Broadcasting Transaction...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
