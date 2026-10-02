@extends('layouts.student')

@section('title', 'Notifikasi')

@section('content')
<div class="space-y-4 lg:space-y-5">

  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Notifikasi</h1>
      <p class="text-sm text-bluedark/60 mt-1">Pemberitahuan tugas baru, nilai yang masuk, dan hasil pengajuan izin keluar.</p>
    </div>

    @if($unreadCount > 0)
      <form method="POST" action="{{ route('student.notifications.read-all') }}" class="shrink-0">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Tandai semua dibaca</button>
      </form>
    @endif
  </div>

  @if($unreadCount > 0)
    <div class="panel px-4 py-3 border-l-4 border-blueprim bg-bluelight/30">
      <p class="text-xs text-bluedark/75">
        Anda memiliki <span class="font-semibold">{{ $unreadCount }}</span> notifikasi yang belum dibaca.
      </p>
    </div>
  @endif

  <div class="panel p-4 lg:p-5">
    @forelse($notifications as $notification)
      @php
        $data = $notification->data;
        $unread = $notification->read_at === null;
      @endphp

      <a href="{{ route('student.notifications.read', $notification->id) }}"
         class="flex items-start gap-3 py-3 {{ $unread ? 'bg-bluelight/25 -mx-2 px-2 rounded-xl' : '' }} hover:bg-bluelight/40 transition-colors">
        <span class="mt-0.5 shrink-0 {{ $unread ? 'text-blueprim' : 'text-bluedark/35' }}">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            @if(($data['icon'] ?? null) === 'grade')
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
            @elseif(($data['icon'] ?? null) === 'permit')
              <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            @else
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            @endif
          </svg>
        </span>

        <span class="min-w-0 flex-1">
          <span class="flex items-center gap-2">
            <span class="text-sm {{ $unread ? 'font-semibold text-bluedark' : 'font-medium text-bluedark/75' }} truncate">
              {{ $data['title'] ?? 'Notifikasi' }}
            </span>
            @if($unread)
              <span class="badge badge-blue shrink-0">Baru</span>
            @endif
          </span>
          @if(! empty($data['body']))
            <span class="block text-xs text-bluedark/60 mt-0.5">{{ $data['body'] }}</span>
          @endif
          <span class="block text-[10px] text-bluedark/40 mt-1">{{ $notification->created_at->diffForHumans() }}</span>
        </span>
      </a>
    @empty
      <div class="text-center py-8">
        <p class="text-sm text-bluedark/60">Belum ada notifikasi.</p>
        <p class="text-[11px] text-bluedark/45 mt-1">
          Notifikasi akan muncul di sini saat guru menerbitkan tugas atau mengisi nilai,
          dan saat Guru BK memproses izin keluar Anda.
        </p>
      </div>
    @endforelse
  </div>

  <x-bk.pagination :paginator="$notifications" />

</div>
@endsection