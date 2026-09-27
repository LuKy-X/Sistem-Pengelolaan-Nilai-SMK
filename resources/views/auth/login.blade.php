@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- Left brand panel --}}
        <div
            class="relative isolate overflow-hidden flex flex-col p-7 pb-12 lg:w-1/2 lg:min-h-screen lg:p-12 bg-gradient-to-br from-bluedark to-blueprim text-white">
            <div class="login-dot-grid"></div>

            <span
                class="absolute rounded-full bg-white/10 blur-sm w-[280px] h-[280px] -top-[120px] -right-[90px] lg:w-[420px] lg:h-[420px] lg:-top-40 lg:-right-[140px] pointer-events-none"></span>
            <span
                class="absolute rounded-full bg-white/[0.08] blur-sm w-[180px] h-[180px] -bottom-[60px] -left-[60px] lg:w-[280px] lg:h-[280px] lg:-bottom-24 lg:-left-24 pointer-events-none"></span>

            <a href="{{ route('public.home') }}" class="relative z-10 flex items-center gap-2.5 group">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo {{ $schoolName }}"
                    class="w-[34px] h-[34px] lg:w-10 lg:h-10 object-contain shrink-0">
                <span class="font-heading text-[0.92rem] lg:text-base font-bold text-white leading-tight">
                    {{ $schoolName }}
                </span>
            </a>

            <div class="hidden lg:flex relative z-10 flex-1 items-center justify-center my-2 min-h-0">
                <img src="{{ asset('assets/img/hero-jurusan.png') }}" alt="Ilustrasi jurusan {{ $schoolName }}"
                    class="max-w-[420px] w-full drop-shadow-2xl">
            </div>

            <div class="relative z-10 mt-6 lg:mt-0 lg:mb-2">
                <h1 class="font-heading text-white text-[2.3rem] font-bold leading-tight">Welcome Back!</h1>
                <p class="hidden lg:block text-bluelight text-sm leading-relaxed mt-2.5 max-w-md">
                    Masuk untuk melanjutkan aktivitasmu di {{ $schoolName }}.
                </p>
            </div>
        </div>

        {{-- Right form panel --}}
        <div
            class="login-form-panel relative flex-1 flex justify-center -mt-8 pb-12 z-10 lg:mt-0 lg:min-h-screen lg:items-center lg:bg-white lg:py-12 lg:pr-12 lg:pl-[5.5rem]">
            <div class="relative w-full lg:max-w-md">
                <svg class="block w-full h-8 lg:hidden" viewBox="0 0 400 32" preserveAspectRatio="none" aria-hidden="true">
                    <path class="login-card-curve" d="M0,32 Q0,0 32,0 L368,0 Q400,0 400,32 Z" />
                </svg>

                <div class="relative z-10 bg-white px-6 pt-[2.15rem] pb-9 lg:bg-transparent lg:p-0">
                    <h2 class="font-heading text-2xl lg:text-[1.85rem] font-bold text-ink mb-6">Masuk ke Akun</h2>


                @if (session('success'))
                    <div
                        class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3"
                        role="status">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm" role="alert">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Gagal masuk:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="login" class="block text-xs font-semibold text-bluedark mb-1.5">
                            Email atau Username <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input type="text" id="login" name="login" value="{{ old('login') }}"
                                placeholder="cth. guru.agus atau agus@sekolah.test" required autofocus
                                autocomplete="username"
                                aria-invalid="{{ $errors->has('login') ? 'true' : 'false' }}"
                                aria-describedby="login-error"
                                @class([
                                    'w-full pl-11 pr-4 py-3.5 rounded-2xl border-[1.5px] bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-all focus:bg-white focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-500/15' => $errors->has('login'),
                                    'border-bluelight focus:border-blueprim focus:ring-blueprim/15' => ! $errors->has('login'),
                                ])>
                        </div>
                        @error('login')
                            <p id="login-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-bluedark">
                                Kata Sandi <span class="text-red-500">*</span>
                            </label>
                            <button type="button" data-password-reset-hint
                                aria-expanded="false" aria-controls="password-reset-hint"
                                class="text-xs font-semibold text-blueprim hover:underline">
                                Lupa kata sandi?
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi akun"
                                required autocomplete="current-password"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                aria-describedby="password-error"
                                @class([
                                    'w-full pl-11 pr-11 py-3.5 rounded-2xl border-[1.5px] bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-all focus:bg-white focus:ring-4',
                                    'border-red-300 focus:border-red-500 focus:ring-red-500/15' => $errors->has('password'),
                                    'border-bluelight focus:border-blueprim focus:ring-blueprim/15' => ! $errors->has('password'),
                                ])>
                            <button type="button" data-password-toggle aria-controls="password"
                                aria-label="Tampilkan kata sandi"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-bluedark p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror

                        <p id="password-reset-hint" hidden
                            class="mt-2 text-xs text-bluedark/60 leading-relaxed bg-bluelight/40 border border-bluelight rounded-xl px-3 py-2">
                            Reset kata sandi dilakukan oleh administrator sekolah.
                            @if ($schoolProfile?->email)
                                Hubungi <span class="font-semibold text-bluedark">{{ $schoolProfile->email }}</span> untuk bantuan.
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1"
                                class="w-4 h-4 rounded border-slate-300 text-blueprim focus:ring-blueprim/30">
                            <span class="text-xs text-ink/80 font-medium">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-6 rounded-full bg-bluedark hover:bg-blueprim text-white font-heading text-sm font-semibold shadow-[0_10px_22px_-8px_rgba(13,71,161,0.5)] transition-all duration-200 active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Masuk</span>
                    </button>
                </form>

                <p class="text-center mt-6 text-[0.83rem] text-[#5C7899]">
                    Kembali ke <a href="{{ route('public.home') }}"
                        class="text-bluedark font-bold hover:underline">Beranda</a>
                </p>
                </div>
            </div>
        </div>
    </div>
@endsection