<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SMK Negeri 2 Karanganyar — Wujudkan Masa Depanmu</title>
<meta name="description" content="Website profil SMK Negeri 2 Karanganyar — jurusan unggulan, kerja sama industri, lulusan terbaik, prestasi, dan artikel terbaru.">

<link rel="icon" type="image/png" href="{{ asset('assets/images/logo/logo.png') }}">

<link rel="stylesheet" href="{{ asset('assets/css/fonts.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: { bluelight: '#E3F2FD', bluesoft: '#90CAF9', blueprim: '#2196F3', bluedark: '#0D47A1', ink: '#0D2A4A' },
        fontFamily: { heading: ['Poppins', 'sans-serif'], body: ['Inter', 'sans-serif'] },
        borderRadius: { '4xl': '2rem', '5xl': '2.5rem' },
        boxShadow: {
          soft: '0 20px 45px -15px rgba(13, 71, 161, 0.25)',
          card: '0 10px 30px -10px rgba(13, 71, 161, 0.15)',
        },
      }
    }
  }
</script>

<script src="{{ asset('assets/vendor/gsap/gsap.min.js') }}"></script>
<script src="{{ asset('assets/vendor/gsap/ScrollTrigger.min.js') }}"></script>
</head>

<body class="antialiased is-landing">
  <div class="page-transition-overlay" id="pageTransitionOverlay" aria-hidden="true">
    <div class="page-transition-diagonal">
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
      <span class="page-transition-band"></span>
    </div>
  </div>

<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-bluelight">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 h-16 md:h-20 flex items-center justify-between">
    <a href="#home" class="flex items-center gap-2.5 md:gap-3 min-w-0">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo SMK Negeri 2 Karanganyar" class="w-9 h-9 md:w-11 md:h-11 object-contain shrink-0">
      <span class="font-heading leading-tight truncate">
        <span class="block text-[13px] md:text-[15px] font-semibold text-bluedark">SMK Negeri 2</span>
        <span class="block text-[10px] md:text-xs font-medium text-blueprim tracking-wide">KARANGANYAR</span>
      </span>
    </a>

    <nav class="hidden lg:flex items-center gap-4 xl:gap-6 font-heading text-sm font-medium text-bluedark/80">
      <a href="#home" class="hover:text-blueprim transition-colors">Home</a>
      <a href="#ppdb" class="hover:text-blueprim transition-colors">PPDB</a>
      <a href="#jurusan" class="hover:text-blueprim transition-colors">Jurusan</a>
      <a href="#pkl-career" class="hover:text-blueprim transition-colors">PKL &amp; Karier</a>
      <a href="#kerja-sama-industri" class="hover:text-blueprim transition-colors">Kerja Sama Industri</a>
      <a href="#produk-unggulan" class="hover:text-blueprim transition-colors">Produk Unggulan</a>
      <a href="#artikel-terbaru" class="hover:text-blueprim transition-colors">Berita</a>
    </nav>

    <a href="#ppdb" class="hidden lg:inline-flex items-center gap-2 bg-bluedark hover:bg-blueprim transition-colors text-white font-heading font-medium text-sm px-5 py-2.5 rounded-full">
      Daftar Sekarang
    </a>

    <button id="menuBtn" aria-label="Buka menu" aria-expanded="false" class="lg:hidden w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-lg border border-bluesoft/60 text-bluedark shrink-0">
      <svg id="iconMenu" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      <svg id="iconClose" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="hidden"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <div id="mobileMenu" class="hidden lg:hidden border-t border-bluelight bg-white">
    <nav class="flex flex-col px-4 sm:px-6 py-4 gap-1 font-heading text-bluedark">
      <a href="#home" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Home</a>
      <a href="#ppdb" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">PPDB</a>
      <a href="#jurusan" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Jurusan</a>
      <a href="#pkl-career" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">PKL &amp; Career Center</a>
      <a href="#lulusan-terbaik" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Lulusan Terbaik</a>
      <a href="#kerja-sama-industri" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Kerja Sama Industri</a>
      <a href="#produk-unggulan" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Produk Unggulan</a>
      <a href="#prestasi" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Prestasi</a>
      <a href="#artikel-terbaru" class="py-2.5 px-2 rounded-lg hover:bg-bluelight">Berita</a>
      <a href="#ppdb" class="mt-2 text-center bg-blueprim text-white py-2.5 rounded-full">Daftar Sekarang</a>
    </nav>
  </div>
</header>

<section id="home" class="relative overflow-hidden parallax-section">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-6 md:pt-12">
    <div class="relative rounded-3xl md:rounded-4xl bg-gradient-to-br from-bluelight via-[#EAF4FE] to-bluesoft/70 px-5 sm:px-8 md:px-14 py-10 md:py-20 overflow-hidden">

      <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-bluesoft/40 blur-3xl parallax-layer" data-speed="0.5"></div>
      <div class="absolute bottom-0 left-1/3 w-56 h-56 rounded-full bg-blueprim/10 blur-3xl parallax-layer" data-speed="0.8"></div>

      <div class="relative grid lg:grid-cols-12 gap-10 items-center">

        <div class="lg:col-span-6 reveal">
          <p class="hero-badge font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-4">Sekolah Menengah Kejuruan</p>
          <h1 class="font-heading font-bold text-3xl sm:text-4xl md:text-5xl xl:text-[3.4rem] leading-[1.15] text-bluedark">
            Belajar Hari Ini,<br>Siap Kerja <span class="text-blueprim">Esok Hari!</span>
          </h1>
          <p class="mt-5 text-bluedark/70 text-base md:text-lg max-w-md leading-relaxed">
            SMK Negeri 2 Karanganyar membekali siswa dengan kurikulum berbasis industri, sertifikasi kompetensi, dan jaringan kerja sama dunia usaha untuk masa depan karier yang nyata.
          </p>
          <div class="mt-8 flex flex-wrap items-center gap-4">
            <a href="#ppdb" class="bg-blueprim hover:bg-bluedark transition-colors text-white font-heading font-medium px-6 sm:px-7 py-3 sm:py-3.5 rounded-full shadow-soft">
              Daftar PPDB Sekarang
            </a>
            <a href="#jurusan" class="inline-flex items-center gap-2 w-11 h-11 rounded-full bg-white border border-bluesoft/60 items-center justify-center hover:bg-bluelight transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <span class="font-heading text-sm text-bluedark/70">Lihat Jurusan</span>
          </div>
        </div>

        <div class="lg:col-span-6 relative reveal">
          <div class="relative max-w-md mx-auto">
            <div class="absolute inset-0 bg-blueprim/10 rounded-full blur-3xl scale-90"></div>
            <img src="{{ asset('assets/images/hero/hero-jurusan.png') }}" alt="Ilustrasi empat program keahlian: Teknik Pemesinan, Teknik Pembuatan Kain, Teknik Ototronik, dan Rekayasa Perangkat Lunak" class="relative w-full h-auto drop-shadow-2xl hero-art-parallax" loading="eager">
          </div>

          <div class="hero-float-chip absolute bottom-2 -left-1 sm:-left-2 md:-left-6 bg-white rounded-2xl shadow-card px-3.5 sm:px-4 py-2.5 sm:py-3 flex items-center gap-3 max-w-[13rem] sm:max-w-[15rem]">
            <div class="flex -space-x-2 shrink-0">
              <img class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1541534741688-6078c6bfb5c5?auto=format&fit=crop&w=100&q=80" alt="">
              <img class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80" alt="">
              <img class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-white object-cover" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=100&q=80" alt="">
            </div>
            <div>
              <p class="font-heading text-xs sm:text-sm font-semibold text-bluedark leading-tight">1.200+ Siswa Aktif</p>
              <p class="text-[11px] sm:text-xs text-bluedark/60">Belajar tiap hari</p>
            </div>
          </div>

          <div class="absolute top-2 right-0 md:right-4 bg-bluedark text-white rounded-2xl shadow-card px-3.5 sm:px-4 py-2 sm:py-2.5">
            <p class="font-heading text-[11px] sm:text-xs font-semibold">Akreditasi A</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== JHIC 2026 & Didukung Oleh ===== -->
<section id="jhic-2026" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-12 sm:mt-16 md:mt-20" aria-labelledby="jhicTitle">
  <div class="reveal rounded-3xl md:rounded-4xl bg-white border border-bluelight shadow-card px-5 sm:px-8 md:px-12 py-8 sm:py-10 md:py-12">
    <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">

      <div class="lg:col-span-5 text-center">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-3">Kompetisi Inovasi</p>
        <h2 id="jhicTitle" class="sr-only">Jagoan Hosting Innovation Competition 2026</h2>
        <img src="{{ asset('assets/images/logo/jhic-2026.webp') }}" width="900" height="479" loading="lazy" decoding="async"
             alt="Logo Jagoan Hosting Innovation Competition 2026"
             class="mx-auto w-full max-w-[280px] sm:max-w-[340px] lg:max-w-[380px] h-auto">
      </div>

      <div class="lg:col-span-7">
        <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-bluedark/60 font-semibold text-center lg:text-left mb-4">Didukung oleh</p>
        <div class="grid grid-cols-2 gap-3 sm:gap-4">
          <div class="h-24 sm:h-32 rounded-2xl bg-bluelight/30 border border-bluelight flex items-center justify-center px-3 sm:px-5">
            <img src="{{ asset('assets/images/logo/jagoan-hosting.webp') }}" width="700" height="206" loading="lazy" decoding="async"
                 alt="Logo Jagoan Hosting" class="max-w-[88%] max-h-[62%] w-auto h-auto object-contain">
          </div>
          <div class="h-24 sm:h-32 rounded-2xl bg-bluelight/30 border border-bluelight flex items-center justify-center px-3 sm:px-5">
            <img src="{{ asset('assets/images/logo/komdigi.webp') }}" width="500" height="351" loading="lazy" decoding="async"
                 alt="Logo Komdigi" class="max-w-[88%] max-h-[72%] w-auto h-auto object-contain">
          </div>
          <div class="h-24 sm:h-32 rounded-2xl bg-bluelight/30 border border-bluelight flex items-center justify-center px-3 sm:px-5">
            <img src="{{ asset('assets/images/logo/garuda-spark.webp') }}" width="700" height="367" loading="lazy" decoding="async"
                 alt="Logo Garuda Spark Innovation Hub by Komdigi" class="max-w-[88%] max-h-[72%] w-auto h-auto object-contain">
          </div>
          <div class="h-24 sm:h-32 rounded-2xl bg-bluelight/30 border border-bluelight flex items-center justify-center px-3 sm:px-5">
            <img src="{{ asset('assets/images/logo/ngalup.webp') }}" width="700" height="111" loading="lazy" decoding="async"
                 alt="Logo Ngalup.co" class="max-w-[88%] max-h-[40%] w-auto h-auto object-contain">
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<section id="jurusan" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-20 sm:pt-24 md:pt-32">
  <div class="flex items-end justify-between gap-6 reveal">
    <div>
      <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Program Keahlian</p>
      <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Jurusan Unggulan</h2>
      <p class="text-bluedark/60 mt-2 max-w-md text-sm sm:text-base">Empat program keahlian dengan kurikulum yang disusun bersama mitra industri.</p>
    </div>
  </div>

  <div class="mt-10 sm:mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-8 sm:gap-y-8 stagger-group">

    <div class="jurusan-item stagger-item group">
      <article class="jurusan-card link-card bg-white rounded-3xl border border-bluelight shadow-card">
        <a href="{{ route('public.departments.index') }}" class="absolute inset-0 z-10 rounded-3xl jurusan-card__link focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Selengkapnya tentang Teknik Pemesinan"></a>
        <h3 class="font-heading font-semibold text-lg text-bluedark">Teknik Pemesinan</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Mempelajari cara memproduksi barang teknik dan mengoperasikan mesin produksi secara presisi.</p>
        <span class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-2 group-hover:gap-2.5 transition-all">
          Selengkapnya
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </span>
      </article>
      <div class="jurusan-card__art-wrap"><img src="{{ asset('assets/images/jurusan/jurusan-pemesinan.png') }}" alt="Ilustrasi Teknik Pemesinan" class="jurusan-card__art"></div>
    </div>

    <div class="jurusan-item stagger-item group">
      <article class="jurusan-card link-card bg-white rounded-3xl border border-bluelight shadow-card">
        <a href="{{ route('public.departments.index') }}" class="absolute inset-0 z-10 rounded-3xl jurusan-card__link focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Selengkapnya tentang Teknik Pembuatan Kain"></a>
        <h3 class="font-heading font-semibold text-lg text-bluedark">Teknik Pembuatan Kain</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Mempelajari desain tenun, mesin pembuatan kain, pemeliharaan, dan pengendalian mutu produksi.</p>
        <span class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-2 group-hover:gap-2.5 transition-all">
          Selengkapnya
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </span>
      </article>
      <div class="jurusan-card__art-wrap"><img src="{{ asset('assets/images/jurusan/jurusan-kain.png') }}" alt="Ilustrasi Teknik Pembuatan Kain" class="jurusan-card__art"></div>
    </div>

    <div class="jurusan-item stagger-item group">
      <article class="jurusan-card link-card bg-white rounded-3xl border border-bluelight shadow-card">
        <a href="{{ route('public.departments.index') }}" class="absolute inset-0 z-10 rounded-3xl jurusan-card__link focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Selengkapnya tentang Teknik Ototronik"></a>
        <h3 class="font-heading font-semibold text-lg text-bluedark">Teknik Ototronik</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Mempelajari teknologi elektronik dan sistem kontrol pada kendaraan bermotor modern.</p>
        <span class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-2 group-hover:gap-2.5 transition-all">
          Selengkapnya
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </span>
      </article>
      <div class="jurusan-card__art-wrap"><img src="{{ asset('assets/images/jurusan/jurusan-ototronik.png') }}" alt="Ilustrasi Teknik Ototronik" class="jurusan-card__art"></div>
    </div>

    <div class="jurusan-item stagger-item group">
      <article class="jurusan-card link-card bg-white rounded-3xl border border-bluelight shadow-card">
        <a href="{{ route('public.departments.index') }}" class="absolute inset-0 z-10 rounded-3xl jurusan-card__link focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Selengkapnya tentang Rekayasa Perangkat Lunak"></a>
        <h3 class="font-heading font-semibold text-lg text-bluedark">Rekayasa Perangkat Lunak</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Mempelajari pengembangan perangkat lunak, mulai dari pembuatan hingga manajemen organisasi TI.</p>
        <span class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-auto pt-2 group-hover:gap-2.5 transition-all">
          Selengkapnya
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </span>
      </article>
      <div class="jurusan-card__art-wrap"><img src="{{ asset('assets/images/jurusan/jurusan-rpl.png') }}" alt="Ilustrasi Rekayasa Perangkat Lunak" class="jurusan-card__art"></div>
    </div>

  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="text-center max-w-xl mx-auto reveal">
    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Jalan Menuju Karier, Dibuat Sederhana</h2>
    <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Dari pendaftaran hingga menyalurkan kerja, setiap tahap dirancang agar siswa siap terjun ke dunia industri.</p>
  </div>

  <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch stagger-group">
    <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between">
      <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
      </div>
      <div>
        <h3 class="font-heading font-semibold text-bluedark text-lg">Daftar &amp; Seleksi</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Isi formulir PPDB online, lengkapi berkas, dan ikuti proses seleksi sesuai jurusan pilihan.</p>
      </div>
    </div>

    <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between">
      <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
      </div>
      <div>
        <h3 class="font-heading font-semibold text-bluedark text-lg">Belajar &amp; Sertifikasi</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Kurikulum berbasis industri, praktik langsung, dan sertifikasi kompetensi yang diakui dunia kerja.</p>
      </div>
    </div>

    <div class="bg-bluelight rounded-3xl p-6 sm:p-8 flex flex-col justify-between">
      <div class="w-11 h-11 rounded-xl bg-white flex items-center justify-center mb-6">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <div>
        <h3 class="font-heading font-semibold text-bluedark text-lg">Kerja &amp; Karier</h3>
        <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Praktik kerja lapangan di mitra industri, rekrutmen langsung, hingga bekal melanjutkan kuliah.</p>
      </div>
    </div>
  </div>
</section>

<section id="lulusan-terbaik" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="grid lg:grid-cols-12 gap-10 items-center">
    <div class="lg:col-span-5 relative reveal">
      <div class="rounded-3xl md:rounded-4xl overflow-hidden shadow-soft aspect-[4/5] max-w-sm mx-auto lg:mx-0 bg-bluelight">
        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80" alt="Lulusan terbaik SMK Negeri 2 Karanganyar" class="w-full h-full object-cover lulusan-photo-parallax" loading="eager">
      </div>
      <div class="absolute -bottom-6 right-4 sm:right-2 md:right-8 bg-white rounded-2xl shadow-card px-4 sm:px-5 py-3 sm:py-4 max-w-[11rem] sm:max-w-[13rem]">
        <p class="font-heading text-xl sm:text-2xl font-bold text-blueprim counter" data-target="96" data-suffix="%">0%</p>
        <p class="text-xs text-bluedark/60 mt-1">Lulusan terserap kerja atau kuliah dalam 6 bulan</p>
      </div>
    </div>

    <div class="lg:col-span-7 reveal mt-8 lg:mt-0">
      <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-3">Kisah Sukses</p>
      <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-[2.6rem] leading-tight text-bluedark">
        Lulusan Terbaik Kami<br class="hidden sm:block">Berkarier di Perusahaan Ternama
      </h2>
      <p class="text-bluedark/60 mt-4 max-w-lg leading-relaxed text-sm sm:text-base">Setiap tahun, alumni SMK Negeri 2 Karanganyar diterima bekerja di perusahaan nasional maupun melanjutkan ke perguruan tinggi favorit berkat bekal kompetensi dan sertifikasi yang mereka bawa.</p>

      <div class="mt-8 bg-white rounded-3xl shadow-card p-5 sm:p-6 flex items-center gap-4 max-w-lg">
        <img src="https://images.unsplash.com/photo-1607746882042-944635dfe10e?auto=format&fit=crop&w=200&q=80" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover shrink-0 bg-bluelight" alt="Alumni SMK Negeri 2 Karanganyar" loading="eager">
        <div>
          <p class="font-heading font-semibold text-bluedark">Rania Putri Ayu</p>
          <p class="text-xs text-bluedark/50">Alumni RPL 2023 — Software Engineer di Telkom Indonesia</p>
          <p class="text-sm text-bluedark/70 mt-2">"Praktik industri di sekolah bikin saya percaya diri langsung kerja tanpa canggung."</p>
        </div>
      </div>

      <a href="{{ route('public.alumni.index') }}" class="inline-flex items-center gap-2 mt-8 text-bluedark font-heading font-medium hover:text-blueprim transition-colors">
        Lihat semua kisah alumni
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>
  </div>
</section>

<section id="pkl-career" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="text-center max-w-xl mx-auto reveal">
    <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Bursa Kerja Khusus</p>
    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">PKL &amp; Career Center</h2>
    <p class="text-bluedark/60 mt-3 text-sm sm:text-base">BKK SMK Negeri 2 Karanganyar menjembatani siswa dan alumni dengan dunia kerja — mulai dari praktik kerja lapangan, informasi lowongan, hingga penelusuran alumni.</p>
  </div>

  <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">

    <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
      </div>
      <h3 class="font-heading font-semibold text-bluedark">Lowongan Kerja</h3>
      <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi rekrutmen terbaru dari mitra industri, khusus untuk siswa dan alumni.</p>
      <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
        <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
          <span>Lowongan Terbaru</span>
          <span class="font-heading font-semibold text-blueprim">12 baru</span>
        </li>
        <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
          <span>Jadwal Rekrutmen</span>
          <span class="font-heading font-semibold text-blueprim">Sep 2026</span>
        </li>
      </ul>
      <a href="{{ route('public.career.index') }}" class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-5">
        Lihat lowongan
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
      </div>
      <h3 class="font-heading font-semibold text-bluedark">Mitra Industri</h3>
      <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Daftar perusahaan mitra tempat siswa melaksanakan PKL dan menyalurkan kerja.</p>
      <div class="mt-4 flex-1 flex items-end">
        <p class="font-heading text-2xl font-bold text-bluedark counter" data-target="60" data-suffix="+">0</p>
      </div>
      <a href="#kerja-sama-industri" class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-3">
        Lihat mitra
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <h3 class="font-heading font-semibold text-bluedark">Alumni</h3>
      <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Jejaring alumni dan hasil tracer study penyerapan lulusan tiap tahunnya.</p>
      <div class="mt-4 flex-1 flex items-end">
        <p class="font-heading text-2xl font-bold text-bluedark counter" data-target="96" data-suffix="%">0%</p>
      </div>
      <a href="#lulusan-terbaik" class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-3">
        Tracer Study
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

    <div class="link-card bg-white rounded-3xl border border-bluelight shadow-card p-6 flex flex-col">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M12 2l9 4.9V17L12 22l-9-5.1V6.9L12 2z"/><path d="M12 12l9-5M12 12v10M12 12L3 7"/></svg>
      </div>
      <h3 class="font-heading font-semibold text-bluedark">PKL</h3>
      <p class="text-sm text-bluedark/60 mt-2 leading-relaxed">Informasi jadwal, pembekalan, dan penempatan Praktik Kerja Lapangan siswa.</p>
      <ul class="mt-4 space-y-2 text-xs text-bluedark/60">
        <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
          <span>Informasi PKL</span>
          <span class="font-heading font-semibold text-blueprim">Terbaru</span>
        </li>
        <li class="flex items-center justify-between bg-bluelight/60 rounded-lg px-3 py-2">
          <span>Mitra PKL</span>
          <span class="font-heading font-semibold text-blueprim">45 lokasi</span>
        </li>
      </ul>
      <a href="{{ route('public.career.index') }}" class="inline-flex items-center gap-1.5 text-sm font-heading font-medium text-blueprim mt-5">
        Info PKL
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
      </a>
    </div>

  </div>

  <div class="mt-6 sm:mt-8 flex justify-center reveal">
    <a href="{{ route('public.career.index') }}" class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm sm:text-base">
      Tentang BKK Selengkapnya
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>
</section>

<section id="kerja-sama-industri" class="mt-24 sm:mt-28 md:mt-36">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 text-center reveal">
    <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Jaringan Dunia Usaha &amp; Industri</p>
    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Kerja Sama Industri</h2>
    <p class="text-bluedark/60 mt-3 max-w-lg mx-auto text-sm sm:text-base">Dipercaya bermitra dengan perusahaan-perusahaan terkemuka untuk praktik kerja lapangan, rekrutmen, hingga penyusunan kurikulum.</p>
  </div>

  <div class="relative mt-10 sm:mt-12 w-screen left-1/2 -translate-x-1/2 industri-marquee-wrap">
    <div class="reveal">
      <div class="industri-marquee-track">

        <div class="industri-marquee-set">
          @for ($i = 0; $i < 7; $i++)
            <div class="industri-logo-card"><img src="{{ asset('assets/images/industri/mitra-utama.png') }}" alt="Mitra Industri" width="80" height="40" class="h-9 md:h-10 w-auto"></div>
          @endfor
        </div>

        <div class="industri-marquee-set" aria-hidden="true">
          @for ($i = 0; $i < 7; $i++)
            <div class="industri-logo-card"><img src="{{ asset('assets/images/industri/mitra-utama.png') }}" alt="" width="80" height="40" class="h-9 md:h-10 w-auto"></div>
          @endfor
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 reveal">
    <div class="relative mt-10 sm:mt-12 flex justify-center gap-10 sm:gap-14 border-t border-bluelight pt-7">
      <div class="text-center">
        <p class="font-heading text-xl sm:text-2xl font-bold text-bluedark counter" data-target="60" data-suffix="+">0</p>
        <p class="text-xs text-bluedark/50 mt-1">Perusahaan mitra</p>
      </div>
      <div class="text-center">
        <p class="font-heading text-xl sm:text-2xl font-bold text-bluedark counter" data-target="15">0</p>
        <p class="text-xs text-bluedark/50 mt-1">Kelas industri</p>
      </div>
    </div>
  </div>
</section>

<section id="produk-unggulan" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="flex items-end justify-between gap-6 reveal">
    <div>
      <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Karya Siswa &amp; Sekolah</p>
      <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Produk Unggulan Sekolah</h2>
      <p class="text-bluedark/60 mt-2 max-w-md text-sm sm:text-base">Katalog produk dan jasa hasil karya siswa dari setiap program keahlian.</p>
    </div>
    <a href="{{ route('public.products.index') }}" class="hidden md:inline-flex items-center gap-2 font-heading font-medium text-bluedark hover:text-blueprim transition-colors shrink-0">
      Katalog Produk &amp; Jasa
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>

  <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">

    <article class="link-card group relative bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.products.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Detail Produk Meja &amp; Rak Kerja Custom"></a>
      <div class="h-40 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1567521464027-f127ff144326?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover" alt="Produk meja kerja custom Teknik Pemesinan">
      </div>
      <div class="p-5">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">Teknik Pemesinan</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Meja &amp; Rak Kerja Custom</h3>
        <p class="text-xs text-bluedark/50 mt-2 leading-relaxed">Deskripsi: dibuat presisi CNC sesuai pesanan bengkel &amp; industri.</p>
        <div class="flex flex-wrap gap-1.5 mt-3">
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Custom ukuran</span>
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Finishing cat</span>
        </div>
        <div class="flex items-center justify-between mt-4">
          <p class="font-heading font-bold text-bluedark">Rp750rb</p>
          <span class="text-xs font-heading font-medium text-blueprim">Detail Produk →</span>
        </div>
      </div>
    </article>

    <article class="link-card group relative bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.products.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Detail Produk Kain Tenun Motif Sekolah"></a>
      <div class="h-40 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1528459801416-a9e53bbf4e17?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover" alt="Produk kain tenun Teknik Pembuatan Kain">
      </div>
      <div class="p-5">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">Pembuatan Kain</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Kain Tenun Motif Sekolah</h3>
        <p class="text-xs text-bluedark/50 mt-2 leading-relaxed">Deskripsi: tenun motif khas hasil praktik siswa jurusan tekstil.</p>
        <div class="flex flex-wrap gap-1.5 mt-3">
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Serat alami</span>
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Motif khas</span>
        </div>
        <div class="flex items-center justify-between mt-4">
          <p class="font-heading font-bold text-bluedark">Rp150rb</p>
          <span class="text-xs font-heading font-medium text-blueprim">Detail Produk →</span>
        </div>
      </div>
    </article>

    <article class="link-card group relative bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.products.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Detail Produk Jasa Servis &amp; Tune-Up"></a>
      <div class="h-40 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1632823469850-1b7b1e8b7692?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover" alt="Jasa servis kendaraan Teknik Ototronik">
      </div>
      <div class="p-5">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">Ototronik</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Jasa Servis &amp; Tune-Up</h3>
        <p class="text-xs text-bluedark/50 mt-2 leading-relaxed">Deskripsi: servis kendaraan ringan oleh siswa didampingi instruktur.</p>
        <div class="flex flex-wrap gap-1.5 mt-3">
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Cek gratis</span>
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Bergaransi</span>
        </div>
        <div class="flex items-center justify-between mt-4">
          <p class="font-heading font-bold text-bluedark">Mulai Rp100rb</p>
          <span class="text-xs font-heading font-medium text-blueprim">Detail Produk →</span>
        </div>
      </div>
    </article>

    <article class="link-card group relative bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.products.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Detail Produk Jasa Website &amp; Aplikasi"></a>
      <div class="h-40 overflow-hidden">
        <img src="https://images.unsplash.com/photo-1551650975-87deedd944c3?auto=format&fit=crop&w=500&q=80" class="w-full h-full object-cover" alt="Jasa pembuatan aplikasi dan website RPL">
      </div>
      <div class="p-5">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full">RPL</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Jasa Website &amp; Aplikasi</h3>
        <p class="text-xs text-bluedark/50 mt-2 leading-relaxed">Deskripsi: pengembangan website &amp; aplikasi custom untuk UMKM.</p>
        <div class="flex flex-wrap gap-1.5 mt-3">
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Responsif</span>
          <span class="text-[11px] bg-bluelight/70 text-bluedark/70 px-2 py-1 rounded-md">Free revisi</span>
        </div>
        <div class="flex items-center justify-between mt-4">
          <p class="font-heading font-bold text-bluedark">Mulai Rp500rb</p>
          <span class="text-xs font-heading font-medium text-blueprim">Detail Produk →</span>
        </div>
      </div>
    </article>

  </div>

  <div class="mt-8 flex md:hidden justify-center reveal">
    <a href="{{ route('public.products.index') }}" class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm">
      Katalog Produk &amp; Jasa
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>
</section>

<section id="prestasi" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="text-center max-w-xl mx-auto reveal">
    <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Pencapaian</p>
    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Prestasi Siswa &amp; Sekolah</h2>
    <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Beberapa capaian terbaru dari siswa dan sekolah di tingkat regional hingga nasional.</p>
  </div>

  <div class="mt-10 sm:mt-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-6 stagger-group">
    <div class="card-hover bg-white rounded-3xl border border-bluelight p-6">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><circle cx="12" cy="8" r="5"/><path d="M8 13l-2 8 6-3 6 3-2-8"/></svg>
      </div>
      <p class="font-heading font-semibold text-bluedark">Juara 1 LKS Tingkat Provinsi</p>
      <p class="text-sm text-bluedark/60 mt-2">Bidang IT Software Solution for Business, 2025.</p>
    </div>

    <div class="card-hover bg-white rounded-3xl border border-bluelight p-6">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M12 3l9 4.5-9 4.5-9-4.5L12 3z"/><path d="M3 12l9 4.5 9-4.5"/></svg>
      </div>
      <p class="font-heading font-semibold text-bluedark">Sekolah Adiwiyata Nasional</p>
      <p class="text-sm text-bluedark/60 mt-2">Penghargaan lingkungan sekolah berkelanjutan, 2024.</p>
    </div>

    <div class="card-hover bg-white rounded-3xl border border-bluelight p-6">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M4 19h16M6 15l4-4 3 3 5-6"/></svg>
      </div>
      <p class="font-heading font-semibold text-bluedark">Top 10 Robotik Nasional</p>
      <p class="text-sm text-bluedark/60 mt-2">Kompetisi robotik pelajar tingkat nasional, 2024.</p>
    </div>

    <div class="card-hover bg-white rounded-3xl border border-bluelight p-6">
      <div class="w-11 h-11 rounded-xl bg-bluelight flex items-center justify-center mb-5">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 12l2 2 4-4"/></svg>
      </div>
      <p class="font-heading font-semibold text-bluedark">Akreditasi A</p>
      <p class="text-sm text-bluedark/60 mt-2">Predikat unggul dari Badan Akreditasi Sekolah, 2023.</p>
    </div>
  </div>

  <div class="mt-8 sm:mt-10 flex justify-center reveal">
    <a href="{{ route('public.achievements.index') }}" class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm sm:text-base">
      Lihat Semua Prestasi
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>
</section>

<section id="artikel-terbaru" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-36">
  <div class="flex items-end justify-between gap-6 reveal">
    <div>
      <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Info Sekolah</p>
      <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Berita &amp; Artikel Terbaru</h2>
    </div>
    <a href="{{ route('public.articles.index') }}" class="hidden md:inline-flex items-center gap-2 font-heading font-medium text-bluedark hover:text-blueprim transition-colors shrink-0">
      Lihat semua artikel
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>

  <div class="mt-6 flex flex-wrap gap-2 reveal" id="beritaFilter">
    <button type="button" data-filter="all" class="berita-filter-btn is-active font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">Semua</button>
    <button type="button" data-filter="ppdb" class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">PPDB</button>
    <button type="button" data-filter="industri" class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">Industri</button>
    <button type="button" data-filter="prestasi" class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">Prestasi</button>
    <button type="button" data-filter="pkl" class="berita-filter-btn font-heading text-xs sm:text-sm font-medium px-4 py-2 rounded-full border transition-colors">PKL</button>
  </div>

  <div class="mt-8 sm:mt-10 grid sm:grid-cols-2 md:grid-cols-3 gap-6 stagger-group" id="beritaGrid">
    <article data-category="ppdb" class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.articles.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Baca artikel: Pendaftaran PPDB 2026/2027 Resmi Dibuka"></a>
      <div class="berita-card__thumb h-44 sm:h-48">
        <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover" alt="Pembukaan PPDB">
      </div>
      <div class="p-5 flex flex-col flex-1">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">PPDB</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Pendaftaran PPDB 2026/2027 Resmi Dibuka</h3>
        <p class="text-xs text-bluedark/50 mt-2">10 Agustus 2026 · 3 menit baca</p>
        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">Baca selengkapnya<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </div>
    </article>

    <article data-category="industri pkl" class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.articles.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Baca artikel: 30 Siswa Diterjunkan Praktik Kerja di Mitra Industri"></a>
      <div class="berita-card__thumb h-44 sm:h-48">
        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover" alt="Praktik kerja lapangan">
      </div>
      <div class="p-5 flex flex-col flex-1">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">Industri</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">30 Siswa Diterjunkan Praktik Kerja di Mitra Industri</h3>
        <p class="text-xs text-bluedark/50 mt-2">2 Agustus 2026 · 4 menit baca</p>
        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">Baca selengkapnya<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </div>
    </article>

    <article data-category="prestasi" class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight sm:col-span-2 md:col-span-1">
      <a href="{{ route('public.articles.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Baca artikel: Tim Robotik Raih Top 10 Kompetisi Nasional"></a>
      <div class="berita-card__thumb h-44 sm:h-48">
        <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover" alt="Kompetisi siswa">
      </div>
      <div class="p-5 flex flex-col flex-1">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">Prestasi</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Tim Robotik Raih Top 10 Kompetisi Nasional</h3>
        <p class="text-xs text-bluedark/50 mt-2">28 Juli 2026 · 2 menit baca</p>
        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">Baca selengkapnya<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </div>
    </article>

    <article data-category="pkl" class="berita-card link-card bg-white rounded-3xl overflow-hidden border border-bluelight">
      <a href="{{ route('public.articles.index') }}" class="absolute inset-0 z-10 rounded-3xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blueprim" aria-label="Baca artikel: Pembekalan PKL Angkatan 2026 Dimulai Pekan Ini"></a>
      <div class="berita-card__thumb h-44 sm:h-48">
        <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80" class="w-full h-full object-cover" alt="Pembekalan PKL">
      </div>
      <div class="p-5 flex flex-col flex-1">
        <span class="text-xs font-heading font-semibold text-blueprim bg-bluelight px-2.5 py-1 rounded-full self-start">PKL</span>
        <h3 class="font-heading font-semibold text-bluedark mt-3 leading-snug">Pembekalan PKL Angkatan 2026 Dimulai Pekan Ini</h3>
        <p class="text-xs text-bluedark/50 mt-2">20 Juli 2026 · 3 menit baca</p>
        <span class="berita-card__cta inline-flex items-center gap-1.5 text-xs font-heading font-medium text-blueprim">Baca selengkapnya<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
      </div>
    </article>
  </div>

  <p id="beritaEmpty" class="hidden text-center text-sm text-bluedark/50 mt-10">Belum ada artikel untuk kategori ini.</p>

  <div class="mt-8 flex md:hidden justify-center reveal">
    <a href="{{ route('public.articles.index') }}" class="inline-flex items-center gap-2 font-heading font-medium text-white bg-bluedark hover:bg-blueprim transition-colors px-6 py-3 rounded-full text-sm">
      Lihat Semua Artikel
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>
</section>

<section id="ppdb" class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-24 sm:mt-28 md:mt-32">
  <div class="text-center max-w-xl mx-auto reveal">
    <p class="font-heading text-xs md:text-sm tracking-[0.2em] uppercase text-blueprim font-semibold mb-2">Penerimaan Peserta Didik Baru</p>
    <h2 class="font-heading font-bold text-2xl sm:text-3xl md:text-4xl text-bluedark">Informasi Alur &amp; Syarat Pendaftaran</h2>
    <p class="text-bluedark/60 mt-3 text-sm sm:text-base">Tahun ajaran 2026/2027 resmi dibuka. Simak alur pendaftaran dan siapkan berkas persyaratan berikut ini.</p>
  </div>

  <div class="mt-10 sm:mt-12 grid lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">

    <div class="lg:col-span-7 bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
      <h3 class="font-heading font-semibold text-lg text-bluedark mb-6">Alur Pendaftaran</h3>
      <ol class="space-y-6">
        <li class="flex gap-4">
          <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">1</span>
          <div>
            <p class="font-heading font-semibold text-bluedark">Registrasi Online</p>
            <p class="text-sm text-bluedark/60 mt-1">Isi formulir pendaftaran melalui link pendaftaran online resmi sekolah.</p>
          </div>
        </li>
        <li class="flex gap-4">
          <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">2</span>
          <div>
            <p class="font-heading font-semibold text-bluedark">Unggah Berkas</p>
            <p class="text-sm text-bluedark/60 mt-1">Unggah dokumen persyaratan sesuai jadwal dan pilih jurusan yang diminati.</p>
          </div>
        </li>
        <li class="flex gap-4">
          <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">3</span>
          <div>
            <p class="font-heading font-semibold text-bluedark">Verifikasi &amp; Tes Seleksi</p>
            <p class="text-sm text-bluedark/60 mt-1">Panitia memverifikasi berkas, dilanjutkan tes/wawancara sesuai jurusan.</p>
          </div>
        </li>
        <li class="flex gap-4">
          <span class="shrink-0 w-9 h-9 rounded-full bg-bluelight text-blueprim font-heading font-bold text-sm flex items-center justify-center">4</span>
          <div>
            <p class="font-heading font-semibold text-bluedark">Pengumuman &amp; Daftar Ulang</p>
            <p class="text-sm text-bluedark/60 mt-1">Hasil seleksi diumumkan secara online, dilanjutkan daftar ulang peserta didik baru.</p>
          </div>
        </li>
      </ol>
    </div>

    <div class="lg:col-span-5 flex flex-col gap-6 h-full">
      <div class="bg-white rounded-3xl border border-bluelight shadow-card p-6 sm:p-8 reveal">
        <h3 class="font-heading font-semibold text-lg text-bluedark mb-5">Syarat Pendaftaran</h3>
        <ul class="space-y-3.5 text-sm text-bluedark/70">
          <li class="flex gap-3"><svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Fotokopi ijazah/SKL SMP atau sederajat</li>
          <li class="flex gap-3"><svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Fotokopi Kartu Keluarga &amp; akta kelahiran</li>
          <li class="flex gap-3"><svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Pas foto berwarna terbaru 3x4</li>
          <li class="flex gap-3"><svg class="shrink-0 mt-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg> Surat keterangan sehat dari puskesmas/dokter</li>
        </ul>
      </div>

      <div class="rounded-3xl bg-gradient-to-br from-blueprim to-bluedark p-6 sm:p-8 text-center reveal flex-1 flex flex-col items-center justify-center">
        <p class="font-heading font-semibold text-white">Link Pendaftaran Online</p>
        <p class="text-white/70 text-sm mt-1.5">Daftar langsung melalui portal PPDB resmi sekolah.</p>
        <a href="{{ route('public.ppdb.index') }}" class="inline-flex items-center gap-2 mt-5 bg-white text-bluedark font-heading font-semibold px-6 py-3 rounded-full hover:bg-bluelight transition-colors text-sm">
          Daftar PPDB Sekarang
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<footer class="mt-24 sm:mt-28 md:mt-32 bg-bluedark text-white rounded-t-[2rem] sm:rounded-t-[2.5rem] md:rounded-t-[3rem] overflow-hidden">

  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 pt-12 sm:pt-14 md:pt-16 reveal">
    <div class="grid md:grid-cols-2 gap-10 md:gap-12 lg:gap-16">

      <div class="flex flex-col">
        <div class="footer-brand-logo w-fit mb-6">
          <img src="{{ asset('assets/images/logo/logo-smk-bisa-hebat.png') }}" alt="Logo SMK Bisa Hebat" class="h-16 sm:h-20 md:h-24 w-auto object-contain">
        </div>

        <p class="text-sm text-white/60 mb-6 leading-relaxed max-w-md">SMK Negeri 2 Karanganyar adalah salah satu Sekolah Menengah Kejuruan favorit di Kabupaten Karanganyar. Serta merupakan sekolah yang berpendidikan karakter, berwawasan, disiplin, tanggung jawab, dan bermoral baik.</p>

        <div class="space-y-4 text-sm mb-7">
          <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            </span>
            <p class="font-heading font-semibold">0271-6498171</p>
          </div>
          <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
            </span>
            <a href="mailto:smkn2kra97@gmail.com" class="font-heading font-semibold hover:text-bluesoft transition-colors break-all">smkn2kra97@gmail.com</a>
          </div>
        </div>

        <div class="mt-auto pt-5 border-t border-white/10 flex justify-center md:justify-start flex-wrap gap-3">
          <a href="#" aria-label="Facebook" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M15 4h-2a4 4 0 00-4 4v3H7v3h2v6h3v-6h2.5l.5-3H12V8a1 1 0 011-1h2V4z"/></svg>
          </a>
          <a href="#" aria-label="Instagram" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
          </a>
          <a href="#" aria-label="YouTube" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="4"/><path d="M10 9l5 3-5 3V9z" fill="white" stroke="none"/></svg>
          </a>
          <a href="#" aria-label="TikTok" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16 3c.3 2.1 1.7 3.6 4 3.9v2.7c-1.4 0-2.7-.4-4-1.2v6.4a5.3 5.3 0 11-4.7-5.3v2.8a2.5 2.5 0 102 2.5V3h2.7z" fill="white"/></svg>
          </a>
          <a href="#" aria-label="LinkedIn" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-blueprim hover:-translate-y-1 flex items-center justify-center transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="white"><path d="M6.94 5a2 2 0 11-4-.02 2 2 0 014 .02zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-3.96 4.77 0V21H22v-7.93c0-6.17-6.87-5.94-8.68-2.91V8.48z"/></svg>
          </a>
        </div>
      </div>

      <div class="footer-map-card rounded-2xl overflow-hidden border border-white/10 min-h-[260px] sm:min-h-[300px] md:min-h-0 md:h-full">
        <iframe
          class="map-frame w-full h-full"
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.857074950696!2d110.94792197505011!3d-7.5905309924241084!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e654b99ab219bfd%3A0x4e63f4d5cebe448a!2sSMK%20Negeri%202%20Karanganyar!5e0!3m2!1sid!2sid!4v1786770289292!5m2!1sid!2sid"
          width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" title="Lokasi SMK Negeri 2 Karanganyar">
        </iframe>
      </div>

    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 mt-10 sm:mt-12">
    <!-- sponsor-bar -->
    <div class="sponsor-bar sponsor-bar--card">
      <div class="sponsor-bar__logos">
        <img src="{{ asset('assets/images/logo/jhic-2026.webp') }}" alt="Logo Jagoan Hosting Innovation Competition 2026" width="900" height="479" class="sb-jhic" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/jagoan-hosting.webp') }}" alt="Logo Jagoan Hosting" width="700" height="206" class="sb-jagoan" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/komdigi.webp') }}" alt="Logo Komdigi" width="500" height="351" class="sb-komdigi" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/garuda-spark.webp') }}" alt="Logo Garuda Spark Innovation Hub by Komdigi" width="700" height="367" class="sb-garuda" loading="lazy" decoding="async">
        <img src="{{ asset('assets/images/logo/ngalup.webp') }}" alt="Logo Ngalup.co" width="700" height="111" class="sb-ngalup" loading="lazy" decoding="async">
      </div>
    </div>
  </div>

  <div class="border-t border-white/10 mt-12 sm:mt-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-5 flex flex-col sm:flex-row justify-between gap-2 text-xs text-white/50">
      <p>&copy; <span id="year">{{ date('Y') }}</span> SMK Negeri 2 Karanganyar. Seluruh hak cipta dilindungi.</p>
      <p>Dibuat dengan bangga untuk pendidikan vokasi Indonesia.</p>
    </div>
  </div>
</footer>

<div class="ai-chat-launcher">
  <button id="aiChatReset" type="button" aria-label="Mulai obrolan baru" class="ai-chat-fab ai-chat-fab--reset">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D47A1" stroke-width="2"><path d="M21 12a9 9 0 11-2.9-6.6"/><path d="M21 3v6h-6"/></svg>
  </button>
  <button id="aiChatFab" type="button" aria-label="Buka chat AI" aria-expanded="false" class="ai-chat-fab ai-chat-fab--main">
    <span class="ai-chat-fab__icon ai-chat-fab__icon--chat">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="4" y="7" width="16" height="12" rx="4"/><path d="M8 7V5a4 4 0 018 0v2"/><circle cx="9" cy="13" r="1.2" fill="white" stroke="none"/><circle cx="15" cy="13" r="1.2" fill="white" stroke="none"/><path d="M9 16.5c1 .8 5 .8 6 0"/><path d="M2 12h2M20 12h2"/></svg>
    </span>
    <span class="ai-chat-fab__icon ai-chat-fab__icon--close">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </span>
  </button>
</div>

<div id="aiChatPanel" class="ai-chat-panel" hidden>
  <div class="bg-gradient-to-r from-blueprim to-bluedark px-4 py-3.5 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center shrink-0">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="4" y="7" width="16" height="12" rx="4"/><path d="M8 7V5a4 4 0 018 0v2"/><circle cx="9" cy="13" r="1.2" fill="white" stroke="none"/><circle cx="15" cy="13" r="1.2" fill="white" stroke="none"/></svg>
      </div>
      <div>
        <p class="font-heading font-semibold text-white text-sm leading-tight">Tanya AI SMKN 2</p>
        <p class="text-[11px] text-white/70 leading-tight">Siap bantu jawab pertanyaanmu</p>
      </div>
    </div>
    <button id="aiChatClose" type="button" aria-label="Tutup chat" class="w-8 h-8 rounded-full hover:bg-white/15 flex items-center justify-center text-white transition-colors shrink-0">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <div id="aiChatMessages" class="flex-1 min-h-0 overflow-y-auto px-4 py-4 flex flex-col gap-3 bg-[#F7FBFF]">
    <div class="ai-chat-msg ai-chat-msg--bot">Halo! Aku asisten virtual SMK Negeri 2 Karanganyar. Ada yang bisa dibantu seputar PPDB, jurusan, PKL, atau produk unggulan sekolah?</div>
  </div>

  <form id="aiChatForm" class="border-t border-bluelight p-3 flex items-center gap-2 shrink-0 bg-white">
    <input id="aiChatInput" type="text" autocomplete="off" placeholder="Tulis pertanyaanmu..." class="flex-1 text-sm bg-bluelight/60 rounded-full px-4 py-2.5 outline-none focus:ring-2 focus:ring-blueprim/40 text-bluedark placeholder:text-bluedark/40">
    <button type="submit" aria-label="Kirim pertanyaan" class="w-10 h-10 shrink-0 rounded-full bg-blueprim hover:bg-bluedark transition-colors flex items-center justify-center">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
    </button>
  </form>
</div>

<script src="{{ asset('assets/js/loader.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

</body>
</html>