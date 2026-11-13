<x-layouts.public title="Admin Sign In | DocuVerify">
    <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-lg">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-900 mx-auto flex items-center justify-center text-white font-bold text-xl shadow-md">
                    DV
                </div>
                <h2 class="mt-4 text-2xl font-extrabold text-slate-900 tracking-tight">Admin Portal Sign In</h2>
                <p class="mt-2 text-xs text-slate-500">
                    Restricted access for authorized registrars & auditors
                </p>
            </div>

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 p-4 border border-red-200 text-xs text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-8 space-y-6" action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email', 'admin@docuverify.test') }}"
                               class="appearance-none block w-full px-3 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               value="admin123456"
                               class="appearance-none block w-full px-3 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded">
                        <label for="remember" class="ml-2 block text-slate-600">Remember session</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition">
                        Sign In to Dashboard
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <span class="text-xs text-slate-400">
                    Default Credentials: <code class="text-slate-600 font-mono">admin@docuverify.test</code> / <code class="text-slate-600 font-mono">admin123456</code>
                </span>
            </div>
        </div>
    </div>
</x-layouts.public>
