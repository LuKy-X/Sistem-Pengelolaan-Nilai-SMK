<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — JHIC 2026 | SMK Negeri 2 Karanganyar</title>
<meta name="description" content="Masuk ke Portal Sistem Pengelolaan Nilai SMK Negeri 2 Karanganyar.">
<link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: { bluelight: '#E3F2FD', bluesoft: '#90CAF9', blueprim: '#2196F3', bluedark: '#0D47A1', ink: '#0D2A4A' },
        fontFamily: { heading: ['Poppins', 'sans-serif'], body: ['Inter', 'sans-serif'] },
      }
    }
  }
</script>
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="font-body text-ink antialiased">

<div class="page-transition-overlay" id="pageTransitionOverlay" aria-hidden="true">
  <div class="page-transition-diagonal">
    <span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span><span class="page-transition-band"></span>
  </div>
</div>

        {{-- Left brand panel --}}
        <div
            class="relative isolate overflow-hidden flex flex-col p-7 pb-12 lg:w-1/2 lg:min-h-screen lg:p-12 bg-gradient-to-br from-bluedark to-blueprim text-white">
            <div class="login-dot-grid"></div>

  <div class="relative isolate overflow-hidden flex flex-col p-7 pb-12 lg:w-1/2 lg:min-h-screen lg:p-12 bg-gradient-to-br from-bluedark to-blueprim">
    <div class="login-dot-grid"></div>
    <span class="absolute rounded-full bg-white/10 blur-[2px] w-[280px] h-[280px] -top-[120px] -right-[90px] lg:w-[420px] lg:h-[420px] lg:-top-40 lg:-right-[140px]"></span>
    <span class="absolute rounded-full bg-white/[0.08] blur-[2px] w-[180px] h-[180px] -bottom-[60px] -left-[60px] lg:w-[280px] lg:h-[280px] lg:-bottom-24 lg:-left-24"></span>

    <a href="{{ route('public.home') }}" class="relative z-10 flex items-center gap-2.5">
      <img src="{{ asset('assets/img/logo.png') }}" alt="Logo SMK Negeri 2 Karanganyar" class="w-[34px] h-[34px] lg:w-10 lg:h-10 object-contain shrink-0">
      <span class="font-heading text-[0.92rem] lg:text-base font-bold text-white leading-tight">SMK Negeri 2 Karanganyar</span>
    </a>

    <div class="hidden lg:flex relative z-10 flex-1 items-center justify-center my-2 min-h-0">
      <img src="{{ asset('assets/img/hero-jurusan.png') }}" alt="Ilustrasi jurusan SMK Negeri 2 Karanganyar" class="max-w-[420px] w-full drop-shadow-2xl">
    </div>

    <div class="relative z-10 mt-6 lg:mt-0 lg:mb-2">
      <h1 class="font-heading text-white text-[2.3rem] font-bold leading-tight">Welcome Back!</h1>
      <p class="hidden lg:block text-bluelight text-sm leading-relaxed mt-2.5 max-w-md">Masuk untuk melanjutkan aktivitasmu di Portal Sistem Pengelolaan Nilai SMK Negeri 2 Karanganyar.</p>
    </div>
  </div>

  <div class="login-form-panel relative flex-1 flex justify-center -mt-8 pb-12 z-10 lg:mt-0 lg:min-h-screen lg:items-center lg:bg-white lg:py-12 lg:pr-12 lg:pl-[5.5rem]">
    <div class="relative w-full lg:max-w-md">
      <svg class="block w-full h-8 lg:hidden" viewBox="0 0 400 32" preserveAspectRatio="none" aria-hidden="true">
        <path class="login-card-curve" d="M0,32 Q0,0 32,0 L368,0 Q400,0 400,32 Z"/>
      </svg>

      <div class="relative z-10 bg-white px-6 pt-[2.15rem] pb-9 lg:bg-transparent lg:p-0">
        <h2 class="font-heading text-2xl lg:text-[1.85rem] font-bold text-ink mb-6">Masuk ke Akun</h2>

        @if(session('success'))
          <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
          </div>
        @endif

        @if($errors->any())
          <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs">
            <div class="flex items-center gap-2 font-semibold mb-1">
              <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Gagal Masuk:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-red-700">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="loginForm">
          @csrf

          <div class="mb-4">
            <label for="login" class="block text-xs font-semibold text-ink mb-1.5">Email atau Username</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
              <input type="text" id="login" name="login" value="{{ old('login') }}" placeholder="nama@email.com atau username" autocomplete="username" required autofocus
                class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-[1.5px] border-bluelight bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-colors focus:border-blueprim focus:ring-4 focus:ring-blueprim/15">
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="block text-xs font-semibold text-ink mb-1.5">Kata Sandi</label>
            <div class="relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 text-bluesoft pointer-events-none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required
                class="w-full pl-11 pr-11 py-3.5 rounded-2xl border-[1.5px] border-bluelight bg-[#FAFDFF] font-body text-sm text-ink placeholder:text-[#9FB6CE] outline-none transition-colors focus:border-blueprim focus:ring-4 focus:ring-blueprim/15">
              <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-bluedark p-1" title="Lihat kata sandi">
                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
            </div>
          </div>

          <div class="flex justify-between items-center -mt-1.5 mb-5">
            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
              <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-slate-300 text-blueprim focus:ring-blueprim/30">
              <span class="text-xs text-[#5C7899]">Ingat saya</span>
            </label>
            <a href="javascript:void(0)" onclick="alert('Silakan hubungi administrator IT sekolah untuk reset kata sandi Anda.')" class="text-[0.8rem] font-semibold text-blueprim hover:underline">Lupa kata sandi?</a>
          </div>

          <button type="submit" class="w-full py-[0.9rem] rounded-full bg-bluedark hover:bg-blueprim text-white font-heading text-sm font-semibold shadow-[0_10px_22px_-8px_rgba(13,71,161,0.5)] transition-colors active:translate-y-px cursor-pointer">Masuk</button>
        </form>

        <!-- Quick Demo Credentials Chips for testing -->
        <div class="mt-6 pt-5 border-t border-slate-100">
          <p class="text-[11px] font-semibold text-[#5C7899] mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-blueprim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Akun Uji Coba Demo (Klik untuk auto-fill):
          </p>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="fillCredential('guru.agus', 'password123')" class="text-left p-2 rounded-xl border border-bluelight bg-[#FAFDFF] hover:bg-bluelight/50 transition-colors cursor-pointer">
              <div class="text-xs font-bold text-bluedark">Guru: Agus Rum</div>
              <div class="text-[10px] text-slate-500 font-mono">guru.agus</div>
            </button>
            <button type="button" onclick="fillCredential('admin', 'password123')" class="text-left p-2 rounded-xl border border-bluelight bg-[#FAFDFF] hover:bg-bluelight/50 transition-colors cursor-pointer">
              <div class="text-xs font-bold text-bluedark">Admin: Sekolah</div>
              <div class="text-[10px] text-slate-500 font-mono">admin</div>
            </button>
          </div>
        </div>

        <p class="text-center mt-6 text-[0.83rem] text-[#5C7899]">Kembali ke <a href="{{ route('public.home') }}" class="text-bluedark font-bold hover:underline">Beranda</a></p>
      </div>
    </div>
  </div>

</div>

<script src="{{ asset('assets/js/loader.js') }}"></script>
<script>
  function togglePasswordVisibility() {
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
