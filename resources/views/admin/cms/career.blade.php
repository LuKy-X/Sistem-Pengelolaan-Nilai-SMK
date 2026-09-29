@extends('layouts.admin')

@section('title', 'Bursa Kerja Khusus & Lowongan PKL')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Bursa Kerja Khusus (BKK) &amp; PKL</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola lowongan magang industri, kerja sama perusahaan, dan lowongan alumni</p>
        </div>

        <button type="button" onclick="openCareerModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Lowongan Baru</span>
        </button>
    </div>

    <!-- Table Lowongan matching template/admin/lowongan.html -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Lowongan Karir &amp; Magang</h2>
            <span class="text-xs text-bluedark/50">Total: {{ $opportunities->total() }} Lowongan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Posisi / Judul</th>
                        <th>Perusahaan Mitra</th>
                        <th>Tipe</th>
                        <th>Lokasi</th>
                        <th>Batas Pendaftaran</th>
                        <th>Pelamar</th>
                        <th class="w-24">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opportunities as $idx => $opp)
                        <tr>
                            <td>{{ $opportunities->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">{{ $opp->title }}</td>
                            <td>{{ $opp->company?->name ?? 'Mitra Sekolah' }}</td>
                            <td>
                                <span class="badge {{ $opp->type === 'JOB' ? 'badge-blue' : 'badge-yellow' }} text-[10px]">
                                    {{ $opp->type === 'JOB' ? 'Kerja (Alumni)' : 'Magang PKL' }}
                                </span>
                            </td>
                            <td class="text-xs text-bluedark/70">{{ $opp->location ?? 'Karanganyar / Solo' }}</td>
                            <td class="text-xs">{{ $opp->close_date ? \Carbon\Carbon::parse($opp->close_date)->translatedFormat('d M Y') : 'Terbuka' }}</td>
                            <td>
                                <span class="badge badge-gray text-[10px]">{{ $opp->applications->count() }} Pelamar</span>
                            </td>
                            <td>
                                <span class="badge {{ $opp->status === 'OPEN' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $opp->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada lowongan pekerjaan atau magang PKL yang dibuka.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $opportunities->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Lowongan -->
<div id="careerModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Buka Lowongan Baru</h3>
            <button type="button" onclick="closeCareerModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.career.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Perusahaan Mitra</label>
                <select name="company_id" required class="f-select">
                    @foreach($companies as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->industry }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Posisi / Judul Lowongan</label>
                <input type="text" name="title" required placeholder="Junior Web Developer / Teknisi Otomotif" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Tipe Lowongan</label>
                    <select name="type" required class="f-select">
                        <option value="INTERNSHIP">Magang PKL</option>
                        <option value="JOB">Pekerjaan Penuh</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Status</label>
                    <select name="status" required class="f-select">
                        <option value="OPEN">Dibuka (Open)</option>
                        <option value="CLOSED">Ditutup</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="f-label">Lokasi Penempatan</label>
                <input type="text" name="location" placeholder="Surakarta / Karanganyar" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Tanggal Buka</label>
                    <input type="date" name="open_date" class="f-input">
                </div>
                <div>
                    <label class="f-label">Tanggal Tutup</label>
                    <input type="date" name="close_date" class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Deskripsi Pekerjaan / Kualifikasi</label>
                <textarea name="description" rows="2" class="f-textarea"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeCareerModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openCareerModal() {
        document.getElementById('careerModal').classList.remove('hidden');
    }
    function closeCareerModal() {
        document.getElementById('careerModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
