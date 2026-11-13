<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Public Document Verification</h1>
        <p class="text-slate-600 mt-2 text-sm max-w-xl mx-auto">
            Enter the unique Document Code and upload the corresponding PDF file. The system will compute its raw SHA-256 fingerprint and verify it directly against Ethereum Sepolia records.
        </p>
    </div>

    @if ($errorMessage)
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg text-sm text-red-700">
            <span class="font-bold">Error:</span> {{ $errorMessage }}
        </div>
    @endif

    <!-- Verification Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 mb-8">
        <form wire:submit.prevent="verify" class="space-y-6">
            <!-- Document Code -->
            <div>
                <label for="documentCode" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Document Code / Registration ID <span class="text-red-500">*</span>
                </label>
                <div class="relative rounded-md shadow-sm">
                    <input type="text" id="documentCode" wire:model.defer="documentCode"
                           placeholder="e.g. DOC-20261113-ABC123"
                           class="block w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 text-sm font-mono tracking-wide uppercase placeholder-slate-400">
                </div>
                @error('documentCode')
                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- PDF File Upload -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Upload Target PDF Document <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-blue-400 bg-slate-50 transition">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-slate-600 justify-center">
                            <label for="pdf-upload" class="relative cursor-pointer bg-white rounded-md font-semibold text-blue-600 hover:text-blue-500 focus-within:outline-none px-2 py-0.5 border border-slate-200 shadow-xs">
                                <span>Select PDF File</span>
                                <input id="pdf-upload" wire:model="file" type="file" accept=".pdf,application/pdf" class="sr-only">
                            </label>
                        </div>
                        <p class="text-xs text-slate-500">Only genuine PDF files up to 20MB are accepted.</p>
                        @if ($selectedFileName && !$result)
                            <div class="mt-2 text-xs text-emerald-700 font-semibold flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Selected: {{ $selectedFileName }} @if ($selectedFileSizeKb) ({{ $selectedFileSizeKb }} KB) @endif
                            </div>
                        @endif
                    </div>
                </div>
                @error('file')
                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition">
                    <span wire:loading.remove wire:target="verify">Run Verification</span>
                    <span wire:loading wire:target="verify" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Hashing & Checking Blockchain...
                    </span>
                </button>

                @if ($result)
                    <button type="button" wire:click="resetForm" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                        Reset / Verify Another
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Verification Result Display -->
    @if ($result)
        <div class="mt-8 transition-all duration-300">
            @php
                $status = $result['status'] ?? 'TECHNICAL_ERROR';
            @endphp

            @if ($status === 'VALID')
                <!-- VALID STATE (GREEN) -->
                <div class="bg-white rounded-2xl border-2 border-emerald-500 shadow-md p-6 sm:p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 transform translate-x-4 -translate-y-4 w-28 h-28 bg-emerald-50 rounded-full flex items-end justify-start p-6 -z-0 opacity-60">
                        <svg class="w-16 h-16 text-emerald-500 opacity-20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>

                    <div class="flex items-center space-x-3 mb-4">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                            <span class="w-2 h-2 mr-2 bg-emerald-600 rounded-full animate-ping"></span>
                            STATUS: VALID
                        </span>
                        <span class="text-xs text-slate-500">Verified in {{ $result['durationMs'] }} ms</span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-900">Document Integrity Confirmed Authentic</h2>
                    <p class="text-sm text-slate-600 mt-1">
                        The uploaded PDF is 100% genuine and matches the immutable cryptographic hash recorded on the Ethereum smart contract.
                    </p>

                    <div class="mt-6 border-t border-slate-100 pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase tracking-wider">Document Code</span>
                            <span class="font-mono text-slate-800 font-bold text-sm">{{ $result['documentCode'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase tracking-wider">Registered Timestamp</span>
                            <span class="text-slate-700 font-medium">{{ $result['registeredAt'] ? \Carbon\Carbon::parse($result['registeredAt'])->toDayDateTimeString() : 'Confirmed On-Chain' }}</span>
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-slate-400 font-semibold block uppercase tracking-wider">Matching SHA-256 Hash</span>
                            <code class="block bg-emerald-50 text-emerald-900 p-2.5 rounded-lg border border-emerald-200 font-mono text-xs break-all mt-1">
                                {{ $result['uploadedHash'] }}
                            </code>
                        </div>
                    </div>
                </div>

            @elseif ($status === 'MODIFIED')
                <!-- MODIFIED STATE (RED) -->
                <div class="bg-white rounded-2xl border-2 border-red-500 shadow-md p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-bold bg-red-100 text-red-800 border border-red-300 shadow-xs">
                            <span class="w-2 h-2 mr-2 bg-red-600 rounded-full"></span>
                            STATUS: MODIFIED
                        </span>
                        <span class="text-xs text-slate-500">Verified in {{ $result['durationMs'] }} ms</span>
                    </div>

                    <h2 class="text-xl font-bold text-red-700">Integrity Violation Detected</h2>
                    <p class="text-sm text-slate-700 mt-1">
                        The document code is registered on the blockchain, but the uploaded PDF's fingerprint does <strong>not</strong> match the recorded hash. This indicates the file has been altered, edited, or re-saved with modifications.
                    </p>

                    <div class="mt-6 border-t border-slate-100 pt-4 space-y-4 text-xs">
                        <div>
                            <span class="text-red-700 font-bold block uppercase tracking-wider">Uploaded File SHA-256 (Current):</span>
                            <code class="block bg-red-50 text-red-900 p-2 rounded border border-red-200 font-mono break-all mt-1">
                                {{ $result['uploadedHash'] }}
                            </code>
                        </div>
                        <div>
                            <span class="text-slate-600 font-bold block uppercase tracking-wider">On-Chain Registered Hash (Original Authentic):</span>
                            <code class="block bg-slate-100 text-slate-800 p-2 rounded border border-slate-200 font-mono break-all mt-1">
                                {{ $result['blockchainHash'] }}
                            </code>
                        </div>
                    </div>
                </div>

            @elseif ($status === 'UNVERIFIABLE')
                <!-- UNVERIFIABLE STATE (AMBER) -->
                <div class="bg-white rounded-2xl border-2 border-amber-500 shadow-md p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-xs">
                            <span class="w-2 h-2 mr-2 bg-amber-600 rounded-full"></span>
                            STATUS: UNVERIFIABLE
                        </span>
                        <span class="text-xs text-slate-500">Verified in {{ $result['durationMs'] }} ms</span>
                    </div>

                    <h2 class="text-xl font-bold text-amber-800">Document Not Found On Blockchain</h2>
                    <p class="text-sm text-slate-700 mt-1">
                        No registration record exists on the smart contract for document code <span class="font-mono font-bold">{{ $result['documentCode'] }}</span>.
                        Please verify that the code was typed correctly.
                    </p>
                </div>

            @else
                <!-- TECHNICAL ERROR (GRAY/WARNING) -->
                <div class="bg-white rounded-2xl border-2 border-slate-400 shadow-md p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-sm font-bold bg-slate-200 text-slate-800 border border-slate-400 shadow-xs">
                            STATUS: TECHNICAL ERROR
                        </span>
                    </div>

                    <h2 class="text-xl font-bold text-slate-800">Blockchain Network Communication Failure</h2>
                    <p class="text-sm text-slate-600 mt-1">
                        A technical error occurred while attempting to contact the blockchain RPC node or microservice.
                        This is an isolated infrastructure issue and does <strong>not</strong> indicate an unverified or modified document.
                    </p>
                    @if (!empty($result['errorMessage']))
                        <div class="mt-4 p-3 bg-slate-50 border border-slate-200 rounded text-xs font-mono text-slate-700">
                            {{ $result['errorMessage'] }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>

