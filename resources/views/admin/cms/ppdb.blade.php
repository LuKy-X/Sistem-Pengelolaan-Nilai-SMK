@extends('layouts.admin')

@section('title', 'Manajemen Informasi PPDB')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Penerimaan Peserta Didik Baru (PPDB)</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola gelombang pendaftaran, jalur seleksi, jadwal, dan syarat PPDB</p>
        </div>

        <button type="button" onclick="openPpdbModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Gelombang Baru</span>
        </button>
    </div>

    <!-- Daftar Gelombang PPDB matching template/admin/ppdb.html -->
    <div class="space-y-4">
        @forelse($periods as $period)
            <div class="panel p-5 space-y-4">
                <div class="flex items-center justify-between gap-4 flex-wrap pb-3 border-b border-bluelight">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-heading font-bold text-bluedark text-base">{{ $period->title }}</h2>
                            <span class="badge {{ $period->status === 'OPEN' ? 'badge-green' : ($period->status === 'DRAFT' ? 'badge-yellow' : 'badge-gray') }}">
                                {{ $period->status }}
                            </span>
                        </div>
                        <p class="text-xs text-bluedark/50 mt-0.5">
                            Tahun Ajaran: {{ $period->academicYear?->name }} &middot;
                            Registrasi: {{ \Carbon\Carbon::parse($period->registration_start)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($period->registration_end)->translatedFormat('d M Y') }}
                        </p>
                    </div>
                </div>

                <!-- Jalur Pendaftaran Terhubung -->
                <div>
                    <h3 class="font-heading font-semibold text-bluedark text-xs uppercase tracking-wider mb-2">Jalur Seleksi PPDB</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @forelse($period->paths as $path)
                            <div class="p-3 rounded-xl border border-bluelight bg-bluelight/10">
                                <div class="font-semibold text-xs text-bluedark">{{ $path->name }}</div>
                                <div class="text-[11px] text-bluedark/50 mt-0.5">Kuota: {{ $path->quota ?? 'Tidak dibatasi' }} siswa</div>
                            </div>
                        @empty
                            <div class="col-span-full py-2 text-xs text-bluedark/40 italic">
                                Belum ada jalur pendaftaran spesifik.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="panel p-10 text-center text-xs text-bluedark/40">
                Belum ada periode atau gelombang PPDB yang dibuat. Silakan tambahkan gelombang baru.
            </div>
        @endforelse
    </div>

</div>

<!-- Modal Tambah Gelombang PPDB -->
<div id="ppdbModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Buka Gelombang PPDB</h3>
            <button type="button" onclick="closePpdbModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.ppdb.periods.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Tahun Ajaran</label>
                <select name="academic_year_id" required class="f-select">
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ $y->is_active ? 'selected' : '' }}>{{ $y->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Nama Gelombang (cth: PPDB Gelombang 1 - Jalur Prestasi)</label>
                <input type="text" name="title" required placeholder="Gelombang 1" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Mulai Pendaftaran</label>
                    <input type="date" name="registration_start" required class="f-input">
                </div>
                <div>
                    <label class="f-label">Selesai Pendaftaran</label>
                    <input type="date" name="registration_end" required class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Status Gelombang</label>
                <select name="status" required class="f-select">
                    <option value="DRAFT">Draft</option>
                    <option value="OPEN" selected>Dibuka (Open)</option>
                    <option value="CLOSED">Ditutup (Closed)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closePpdbModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openPpdbModal() {
        document.getElementById('ppdbModal').classList.remove('hidden');
    }
    function closePpdbModal() {
        document.getElementById('ppdbModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
