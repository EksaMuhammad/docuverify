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

            <!-- PDF File Upload with Drag & Drop -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                    Upload Target PDF Document <span class="text-red-500">*</span>
                </label>

                <div x-data="{
                        isDropping: false,
                        isUploading: false,
                        progress: 0,
                        dragCounter: 0,
                        clientError: null,
                        init() {
                            window.addEventListener('dragover', (e) => e.preventDefault(), false);
                            window.addEventListener('drop', (e) => e.preventDefault(), false);
                        },
                        handleDragEnter(e) {
                            e.preventDefault();
                            this.dragCounter++;
                            this.isDropping = true;
                        },
                        handleDragLeave(e) {
                            e.preventDefault();
                            this.dragCounter--;
                            if (this.dragCounter <= 0) {
                                this.dragCounter = 0;
                                this.isDropping = false;
                            }
                        },
                        handleDragOver(e) {
                            e.preventDefault();
                            this.isDropping = true;
                        },
                        handleDrop(e) {
                            e.preventDefault();
                            this.dragCounter = 0;
                            this.isDropping = false;
                            this.clientError = null;

                            if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length === 0) {
                                return;
                            }

                            const file = e.dataTransfer.files[0];
                            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                            if (!isPdf) {
                                this.clientError = 'Only PDF documents (.pdf) are supported.';
                                return;
                            }

                            if (file.size > 20 * 1024 * 1024) {
                                this.clientError = 'File exceeds maximum limit of 20MB.';
                                return;
                            }

                            this.isUploading = true;
                            $wire.upload('file', file,
                                () => { this.isUploading = false; this.progress = 0; },
                                (err) => { this.isUploading = false; this.progress = 0; this.clientError = 'Upload failed: ' + err; },
                                (evt) => { this.progress = evt.detail.progress; }
                            );
                        }
                     }"
                     x-on:livewire-upload-start="isUploading = true; progress = 0; clientError = null;"
                     x-on:livewire-upload-finish="isUploading = false; progress = 100;"
                     x-on:livewire-upload-error="isUploading = false; clientError = 'Upload error occurred. Please try again.';"
                     x-on:livewire-upload-progress="progress = $event.detail.progress"
                     @dragenter="handleDragEnter($event)"
                     @dragover="handleDragOver($event)"
                     @dragleave="handleDragLeave($event)"
                     @drop="handleDrop($event)"
                     @click="$refs.fileInput.click()"
                     class="relative mt-1 group cursor-pointer rounded-2xl border-2 border-dashed p-6 text-center transition-all duration-200 select-none"
                     :class="{
                         'border-blue-500 bg-blue-50/80 ring-4 ring-blue-100 scale-[1.008] shadow-sm': isDropping,
                         'border-emerald-300 bg-emerald-50/20 hover:border-emerald-400': !isDropping && {{ $selectedFileName && !$result ? 'true' : 'false' }},
                         'border-slate-300 bg-slate-50/70 hover:border-blue-400 hover:bg-blue-50/20': !isDropping && !{{ $selectedFileName && !$result ? 'true' : 'false' }}
                     }">

                    <input x-ref="fileInput"
                           id="pdf-upload"
                           wire:model="file"
                           type="file"
                           accept=".pdf,application/pdf"
                           class="sr-only"
                           @click.stop>

                    <!-- Dropping Active State -->
                    <div x-show="isDropping" class="pointer-events-none py-3 text-center">
                        <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2 animate-bounce">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-blue-700">Drop PDF file here</p>
                        <p class="text-xs text-blue-500 mt-0.5">Release to start instant SHA-256 integrity check</p>
                    </div>

                    <!-- Uploading State with Progress Bar -->
                    <div x-show="isUploading && !isDropping" x-cloak class="pointer-events-none py-3 text-center max-w-sm mx-auto">
                        <div class="flex items-center justify-center space-x-2 text-blue-700 text-xs font-semibold mb-2">
                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Loading PDF into buffer (<span x-text="progress + '%'"></span>)...</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    <!-- Ready / Selected State -->
                    @if ($selectedFileName && !$result)
                        <div x-show="!isDropping && !isUploading" class="py-1">
                            <div class="flex flex-col sm:flex-row items-center justify-between bg-white border border-emerald-200 rounded-xl p-3.5 shadow-2xs gap-3">
                                <div class="flex items-center space-x-3 text-left w-full sm:w-auto">
                                    <div class="w-10 h-10 rounded-lg bg-red-50 border border-red-200 text-red-600 flex flex-col items-center justify-center font-bold text-[9px] leading-tight shrink-0 shadow-2xs">
                                        <svg class="w-4 h-4 mb-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>PDF</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate max-w-xs sm:max-w-md" title="{{ $selectedFileName }}">
                                            {{ $selectedFileName }}
                                        </p>
                                        <div class="flex items-center space-x-2 text-[11px] text-slate-500 mt-0.5">
                                            @if ($selectedFileSizeKb)
                                                <span>{{ $selectedFileSizeKb > 1024 ? number_format($selectedFileSizeKb / 1024, 2) . ' MB' : $selectedFileSizeKb . ' KB' }}</span>
                                                <span>•</span>
                                            @endif
                                            <span class="inline-flex items-center text-emerald-700 font-medium">
                                                <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Ready for verification
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    <button type="button" @click.stop="$refs.fileInput.click()" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition">
                                        Change
                                    </button>
                                    <button type="button" wire:click.stop="clearFile" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold text-slate-600 bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2 text-center">
                                Click or drag another PDF here anytime to replace
                            </p>
                        </div>
                    @endif

                    <!-- Empty / Idle State -->
                    <div x-show="!isDropping && !isUploading && !{{ $selectedFileName && !$result ? 'true' : 'false' }}" class="space-y-2.5 py-2">
                        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 text-slate-400 group-hover:text-blue-600 group-hover:bg-blue-50 flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                <span class="text-blue-600 font-bold group-hover:underline">Drag and drop your PDF here</span>, or click to browse
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Only genuine PDF files up to 20MB are accepted
                            </p>
                        </div>

                        <div class="pt-0.5">
                            <span class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-300 shadow-2xs group-hover:border-blue-400 group-hover:text-blue-600 transition">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-slate-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Choose PDF File
                            </span>
                        </div>
                    </div>

                    <!-- Client Error Alert -->
                    <div x-show="clientError" x-cloak class="mt-2 text-xs text-red-600 font-semibold bg-red-50 py-1.5 px-3 rounded-lg border border-red-200">
                        <span x-text="clientError"></span>
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

