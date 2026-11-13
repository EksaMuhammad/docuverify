<x-layouts.public title="About & System Architecture | DocuVerify">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Header -->
        <div class="border-b border-slate-200 pb-8 mb-10">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">System Architecture & Technical Specifications</h1>
            <p class="text-slate-600 mt-2">Comprehensive architectural blueprint of the DocuVerify blockchain document verification platform.</p>
        </div>

        <div class="prose max-w-none text-slate-700 space-y-10">
            <!-- 1. System Overview -->
            <section class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-4">1. Product Overview & Purpose</h2>
                <p class="text-sm leading-relaxed mb-4">
                    DocuVerify is an enterprise-grade digital document integrity verification system designed to establish mathematical trust in digital PDF records. Using cryptographic SHA-256 hashing and immutable smart contracts on the Ethereum blockchain (Sepolia testnet and local node), DocuVerify enables instant, public verification of document integrity without manual publisher confirmation.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-xs">
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="font-bold text-slate-800 block mb-1">What It Does:</span>
                        Guarantees that a presented PDF document is identical byte-for-byte to the version certified by the authorized registrar, revealing any tampering or modification.
                    </div>
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="font-bold text-slate-800 block mb-1">What It Strictly Does NOT Do:</span>
                        Never uploads raw PDF bytes to the blockchain or database. Never stores user documents permanently. Completely protects document privacy.
                    </div>
                </div>
            </section>

            <!-- 2. Cryptographic Specifications -->
            <section class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-4">2. Cryptographic Normalization & Hashing Rules</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="font-semibold text-slate-900">Document Fingerprint (SHA-256):</span>
                        <p class="text-xs text-slate-600 mt-1">
                            Computed directly from the binary byte stream of the original PDF. A 64-character lowercase hexadecimal hash is formatted into standard Solidity <code class="bg-slate-100 px-1 py-0.5 rounded text-blue-700">bytes32</code> for on-chain anchoring.
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-900">Document Identifier (keccak256):</span>
                        <p class="text-xs text-slate-600 mt-1">
                            Derived deterministically from the normalized document code:
                            <br><code class="bg-slate-100 px-1 py-0.5 rounded text-blue-700">documentId = keccak256(toUtf8Bytes(trim(strtoupper(documentCode))))</code>
                            <br>Normalized identically across both Laravel (PHP) and the Node.js / ethers.js microservice.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 3. Port Allocation & Environmental Isolation -->
            <section class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                <h2 class="text-xl font-bold text-slate-900 mb-4">3. Environmental Isolation & Port Architecture</h2>
                <p class="text-xs text-slate-600 mb-4">
                    In compliance with environment safety constraints, DocuVerify isolates all microservices across dedicated, verified open ports:
                </p>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2">Service</th>
                                <th class="px-4 py-2">Port</th>
                                <th class="px-4 py-2">Protocol / Role</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">Laravel Application</td>
                                <td class="px-4 py-2 text-blue-600 font-bold">8001</td>
                                <td class="px-4 py-2">HTTP / Web & Livewire UI (Port 8000 safely bypassed)</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">Vite Asset Server</td>
                                <td class="px-4 py-2 text-blue-600 font-bold">5173</td>
                                <td class="px-4 py-2">Tailwind CSS & JavaScript compilation</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">Blockchain Helper Service</td>
                                <td class="px-4 py-2 text-blue-600 font-bold">3001</td>
                                <td class="px-4 py-2">Node.js + ethers.js v6 JSON-RPC bridge</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">Local Hardhat Ethereum Node</td>
                                <td class="px-4 py-2 text-blue-600 font-bold">8545</td>
                                <td class="px-4 py-2">Simulated EVM node for tests & dev confirmation</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">MySQL / MariaDB</td>
                                <td class="px-4 py-2 text-blue-600 font-bold">3307</td>
                                <td class="px-4 py-2">XAMPP MariaDB (<code class="text-slate-700">docuverify_db</code>)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts.public>
