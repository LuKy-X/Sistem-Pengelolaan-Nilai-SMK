<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Portal Sistem Pengelolaan Nilai SMK</title>
    <meta name="description" content="Masuk ke Portal Akademik & Penilaian Guru SMK Negeri 2 Karanganyar.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/logo.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-body text-ink antialiased bg-[#F4F9FD]">

<div class="min-h-screen flex flex-col lg:flex-row">

    <!-- Left Brand Hero Section -->
    <div class="relative isolate overflow-hidden flex flex-col p-7 pb-12 lg:w-1/2 lg:min-h-screen lg:p-14 bg-gradient-to-br from-bluedark via-[#1565C0] to-blueprim text-white justify-between">
        <div class="login-dot-grid opacity-60"></div>
        
        <!-- Ambient Glowing Orbs -->
        <span class="absolute rounded-full bg-white/10 blur-xl w-[280px] h-[280px] -top-20 -right-20 lg:w-[450px] lg:h-[450px] lg:-top-32 lg:-right-32 pointer-events-none"></span>
        <span class="absolute rounded-full bg-blue-400/20 blur-2xl w-[200px] h-[200px] -bottom-16 -left-16 lg:w-[350px] lg:h-[350px] pointer-events-none"></span>

        <!-- Header Brand -->
        <a href="{{ route('public.home') }}" class="relative z-10 flex items-center gap-3 group">
            <div class="w-11 h-11 rounded-2xl bg-white/15 p-2 backdrop-blur-md border border-white/20 shadow-md group-hover:scale-105 transition-transform flex items-center justify-center">
                <img src="{{ asset('assets/img/logo.svg') }}" alt="Logo SMK Negeri 2 Karanganyar" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="font-heading text-base lg:text-lg font-bold text-white tracking-wide block leading-tight">SMK Negeri 2 Karanganyar</span>
                <span class="text-xs text-bluelight/80 font-normal">Sistem Pengelolaan Nilai &amp; Layanan Akademik</span>
            </div>
        </a>

        <!-- Middle Feature Illustration / Badges -->
        <div class="relative z-10 my-8 lg:my-auto flex flex-col items-center">
            <div class="relative w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 lg:p-8 shadow-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-400/20 text-emerald-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-xs font-semibold tracking-wider uppercase text-bluelight">Platform Terpadu Guru &amp; Siswa</span>
                </div>
                
                <h3 class="font-heading text-xl lg:text-2xl font-bold text-white mb-2 leading-snug">
                    Buku Nilai Digital &amp; Manajemen Pembelajaran SMK
                </h3>
                <p class="text-xs lg:text-sm text-bluelight/90 leading-relaxed mb-5">
                    Mendukung penilaian kompetensi kejuruan, rubrik multi-kriteria, absensi jurnal harian, dan monitoring layanan BK secara realtime.
                </p>

                <!-- Quick Feature Pills -->
                <div class="grid grid-cols-2 gap-2 text-xs font-medium">
                    <div class="bg-white/10 rounded-xl px-3 py-2 flex items-center gap-2 border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-cyan-300"></span> Buku Nilai Digital
                    </div>
                    <div class="bg-white/10 rounded-xl px-3 py-2 flex items-center gap-2 border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-amber-300"></span> Rubrik Penilaian
                    </div>
                    <div class="bg-white/10 rounded-xl px-3 py-2 flex items-center gap-2 border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-emerald-300"></span> Presensi &amp; Jurnal
                    </div>
                    <div class="bg-white/10 rounded-xl px-3 py-2 flex items-center gap-2 border border-white/10">
                        <span class="w-2 h-2 rounded-full bg-violet-300"></span> Layanan BK &amp; Izin
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="relative z-10 text-xs text-bluelight/70 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} SMK Negeri 2 Karanganyar</span>
            <span class="font-semibold text-white/90">Kurikulum Merdeka SMK</span>
        </div>
    </div>

    <!-- Right Login Form Section -->
    <div class="relative flex-1 flex flex-col justify-center px-6 py-12 lg:px-16 lg:py-16 bg-white min-h-[500px]">
        <div class="w-full max-w-md mx-auto">
            
            <div class="mb-8">
                <span class="inline-block px-3 py-1 bg-bluelight text-bluedark text-xs font-semibold rounded-full mb-3">
                    Portal Autentikasi
                </span>
                <h2 class="font-heading text-2xl lg:text-3xl font-extrabold text-bluedark">
                    Masuk ke Akun
                </h2>
                <p class="text-sm text-ink/70 mt-1.5">
                    Silakan gunakan Email atau Username yang terdaftar di sekolah.
                </p>
            </div>

            <!-- Flash Error / Success Notifications -->
            @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm">
                    <div class="flex items-center gap-2 font-semibold mb-1">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Gagal Masuk:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" id="loginForm">
                @csrf

                <!-- Username or Email -->
                <div>
                    <label for="login" class="block text-xs font-semibold text-bluedark mb-1.5">
                        Email atau Username <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input 
                            type="text" 
                            id="login" 
                            name="login" 
                            value="{{ old('login') }}" 
                            placeholder="cth. guru.agus atau agus@smk.test" 
                            required 
                            autofocus
                            class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-[1.5px] border-bluelight bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-all focus:border-blueprim focus:bg-white focus:ring-4 focus:ring-blueprim/15"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-bluedark">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <a href="javascript:void(0)" onclick="alert('Silakan hubungi administrator IT sekolah untuk reset kata sandi Anda.')" class="text-xs font-semibold text-blueprim hover:underline">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan kata sandi akun" 
                            required 
                            class="w-full pl-11 pr-11 py-3.5 rounded-2xl border-[1.5px] border-bluelight bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-all focus:border-blueprim focus:bg-white focus:ring-4 focus:ring-blueprim/15"
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-bluedark p-1"
                            title="Tampilkan / Sembunyikan"
                        >
                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-slate-300 text-blueprim focus:ring-blueprim/30 rounded">
                        <span class="text-xs text-ink/80 font-medium">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    id="submitBtn"
                    class="w-full py-3.5 px-6 rounded-full bg-bluedark hover:bg-blueprim text-white font-heading text-sm font-semibold shadow-[0_10px_22px_-8px_rgba(13,71,161,0.5)] transition-all duration-200 active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Dev Demo Credentials Chips -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs font-semibold text-slate-500 mb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Akun Pengujian Demo (Klik untuk auto-fill):
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCredential('guru.agus', 'password123')" class="text-left p-2.5 rounded-xl border border-bluelight bg-bluelight/30 hover:bg-bluelight transition-colors">
                        <div class="text-xs font-bold text-bluedark">Guru: Agus Rum</div>
                        <div class="text-[11px] text-slate-500 font-mono">guru.agus</div>
                    </button>
                    <button type="button" onclick="fillCredential('admin', 'password123')" class="text-left p-2.5 rounded-xl border border-bluelight bg-bluelight/30 hover:bg-bluelight transition-colors">
                        <div class="text-xs font-bold text-bluedark">Admin: Sekolah</div>
                        <div class="text-[11px] text-slate-500 font-mono">admin</div>
                    </button>
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-bluedark transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Beranda Sekolah</span>
                </a>
            </div>

        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    }

    function fillCredential(login, pass) {
        document.getElementById('login').value = login;
        document.getElementById('password').value = pass;
    }
</script>

</body>
</html>
