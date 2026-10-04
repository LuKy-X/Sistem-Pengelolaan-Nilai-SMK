@php
  /**
   * Navigasi tab halaman Absensi Kelas Guru BK.
   *
   * @var array<string, mixed> $tabQuery  Parameter query yang diteruskan ke tab tujuan
   */
  $tabQuery = $tabQuery ?? [];
  $tabQuery = array_filter($tabQuery, fn ($value) => $value !== null && $value !== '');
  $tabs = [
    'counselor.journals.index' => 'Isi Absensi',
    'counselor.journals.attendance' => 'Lihat Absensi',
    'counselor.journals.history' => 'Riwayat',
  ];
@endphp

<nav class="flex flex-wrap items-center gap-1.5 bg-white border border-bluelight rounded-2xl p-1.5 shadow-2xs">
  @foreach($tabs as $routeName => $label)
    @php
      $isActive = request()->routeIs($routeName);
      $isActive = $isActive || ($routeName === 'counselor.journals.attendance' && request()->routeIs('counselor.journals.show'));
    @endphp
    <a href="{{ route($routeName, $tabQuery) }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors
              {{ $isActive
                  ? 'bg-blueprim text-white shadow-sm hover:bg-blueprim'
                  : 'text-bluedark/70 hover:bg-bluelight hover:text-blueprim' }}"
       @if($isActive) aria-current="page" @endif>
      @if($routeName === 'counselor.journals.index')
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
      @elseif($routeName === 'counselor.journals.attendance')
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      @else
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
      @endif
      {{ $label }}
    </a>
  @endforeach
  <span class="ml-auto pr-2 text-[11px] text-bluedark/45 hidden sm:inline">
    Isi absensi hanya pada jadwal Anda &middot; lihat absensi kelas binaan &amp; yang diajar kapan saja
  </span>
</nav>