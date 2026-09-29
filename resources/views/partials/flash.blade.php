{{--
    Shared flash / validation banner for the public and guest layouts.
    Follows the existing admin alert styling (emerald / red / amber pills).
--}}
@if (session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-5">
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start justify-between gap-3 shadow-xs"
            role="status">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Tutup pemberitahuan"
                class="text-emerald-600 hover:text-emerald-800 p-1 shrink-0">&times;</button>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-5">
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start justify-between gap-3 shadow-xs"
            role="alert">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" aria-label="Tutup pemberitahuan"
                class="text-red-600 hover:text-red-800 p-1 shrink-0">&times;</button>
        </div>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-5">
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm shadow-xs" role="alert">
            <div class="flex items-center gap-2 font-semibold mb-1">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Terdapat kesalahan pengisian data:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs text-amber-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
