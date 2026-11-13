<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'DocuVerify — Blockchain Document Integrity Verification' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans">
    <!-- Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-9 h-9 rounded-lg bg-slate-900 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                            DV
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900">Docu<span class="text-blue-600">Verify</span></span>
                    </a>
                    <span class="hidden sm:inline-block px-2.5 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                        Sepolia & Local Hardhat
                    </span>
                </div>

                <nav class="flex items-center space-x-1 sm:space-x-4">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-blue-600 hover:bg-slate-100 transition">
                        Home
                    </a>
                    <a href="{{ route('verify') }}" class="px-3 py-2 text-sm font-semibold rounded-md text-blue-600 hover:bg-blue-50 transition">
                        Verify PDF
                    </a>
                    <a href="{{ route('about') }}" class="px-3 py-2 text-sm font-medium rounded-md text-slate-700 hover:text-blue-600 hover:bg-slate-100 transition">
                        About
                    </a>
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-slate-900 hover:bg-slate-800 shadow-sm transition">
                            Admin Portal
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="ml-2 inline-flex items-center px-3.5 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 transition">
                            Admin Sign In
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:justify-between sm:text-left text-sm text-slate-500">
            <div>
                <p class="font-semibold text-slate-700">DocuVerify &copy; {{ date('Y') }} — Digital Document Integrity Verification System</p>
                <p class="text-xs text-slate-400 mt-1">Cryptographic SHA-256 Hashing & Ethereum Sepolia Smart Contracts</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-6 justify-center">
                <a href="{{ route('about') }}" class="hover:text-blue-600 transition">Documentation & Architecture</a>
                <a href="{{ route('verify') }}" class="hover:text-blue-600 transition">Public Verifier</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

