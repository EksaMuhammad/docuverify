<x-layouts.public title="DocuVerify — Decentralized Digital Document Verification">
    <div class="relative overflow-hidden bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-ping"></span>
                    <span>Blockchain-Anchored Integrity Engine</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Verify PDF Integrity with <span class="text-blue-600">Cryptographic Certainty</span>
                </h1>
                <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                    DocuVerify anchors raw SHA-256 document fingerprints directly to the Ethereum blockchain. Detect tampering, page alterations, or modified byte streams instantly—without exposing your document contents.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('verify') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 shadow-md hover:shadow-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verify a PDF Document
                    </a>
                    <a href="{{ route('about') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-slate-300 text-base font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition">
                        Learn Architecture & Specs
                    </a>
                </div>

                <!-- Privacy Guarantee Alert -->
                <div class="mt-12 bg-slate-50 border border-slate-200 rounded-xl p-4 text-left max-w-2xl mx-auto flex items-start space-x-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div class="text-xs text-slate-600">
                        <span class="font-semibold text-slate-800">Zero-Retention Guarantee:</span> PDF files are strictly processed in-memory / temporary storage for SHA-256 fingerprint extraction and are purged immediately. No file bytes or private document contents are ever uploaded to the blockchain or stored permanently.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification States Breakdown -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Deterministic 4-State Verification</h2>
            <p class="text-sm text-slate-500 mt-2">Every verification request produces an indisputable, blockchain-audited state.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- VALID -->
            <div class="bg-white p-6 rounded-xl border border-emerald-200 shadow-sm hover:border-emerald-300 transition">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold mb-4">
                    ✓
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Status 1</div>
                <h3 class="text-lg font-bold text-slate-900 mt-1">VALID</h3>
                <p class="text-xs text-slate-600 mt-2">
                    Document ID exists on the blockchain and uploaded file's SHA-256 byte fingerprint matches the immutable record exactly.
                </p>
            </div>

            <!-- MODIFIED -->
            <div class="bg-white p-6 rounded-xl border border-red-200 shadow-sm hover:border-red-300 transition">
                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-700 flex items-center justify-center font-bold mb-4">
                    ✕
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-red-700">Status 2</div>
                <h3 class="text-lg font-bold text-slate-900 mt-1">MODIFIED</h3>
                <p class="text-xs text-slate-600 mt-2">
                    Document ID is recognized on chain, but bytes have been altered (text edits, deleted pages, altered figures).
                </p>
            </div>

            <!-- UNVERIFIABLE -->
            <div class="bg-white p-6 rounded-xl border border-amber-200 shadow-sm hover:border-amber-300 transition">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold mb-4">
                    ?
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-amber-700">Status 3</div>
                <h3 class="text-lg font-bold text-slate-900 mt-1">UNVERIFIABLE</h3>
                <p class="text-xs text-slate-600 mt-2">
                    No matching registration record exists on the smart contract for the provided document identifier.
                </p>
            </div>

            <!-- TECHNICAL ERROR -->
            <div class="bg-white p-6 rounded-xl border border-slate-300 shadow-sm hover:border-slate-400 transition">
                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold mb-4">
                    !
                </div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Status 4</div>
                <h3 class="text-lg font-bold text-slate-900 mt-1">TECHNICAL ERROR</h3>
                <p class="text-xs text-slate-600 mt-2">
                    Explicitly isolated network/RPC or gateway failures. Never falsely labeled as an unverifiable document.
                </p>
            </div>
        </div>
    </div>
</x-layouts.public>
