@extends('layouts.admin')

@section('title', 'Manajemen Prestasi Sekolah & Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Etalase Prestasi Sekolah &amp; Siswa</h1>
            <p class="text-sm text-bluedark/60 mt-1">Kelola rekam jejak juara lomba, kompetisi LKS, dan kejuaraan siswa</p>
        </div>

        <button type="button" onclick="openAchModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Prestasi Baru</span>
        </button>
    </div>

    <!-- Table Prestasi matching template/admin/prestasi.html -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Prestasi</h2>
            <span class="text-xs text-bluedark/50">Total: {{ $achievements->total() }} Prestasi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Judul Kejuaraan / Prestasi</th>
                        <th>Kategori</th>
                        <th>Tingkat</th>
                        <th>Juara / Rank</th>
                        <th>Penyelenggara</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($achievements as $idx => $ach)
                        <tr>
                            <td>{{ $achievements->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">
                                <div>{{ $ach->title }}</div>
                                @if($ach->is_featured)
                                    <span class="badge badge-yellow text-[9px]">Unggulan</span>
                                @endif
                            </td>
                            <td>{{ $ach->category?->name ?? 'Umum' }}</td>
                            <td>
                                <span class="badge badge-blue text-[10px]">{{ $ach->level }}</span>
                            </td>
                            <td>
                                <strong class="text-emerald-700 font-heading text-xs">{{ $ach->rank ?? '-' }}</strong>
                            </td>
                            <td class="text-xs text-bluedark/70">{{ $ach->organizer ?? '-' }}</td>
                            <td class="text-xs">{{ \Carbon\Carbon::parse($ach->achievement_date)->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada data prestasi yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $achievements->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Prestasi -->
<div id="achModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Tambah Data Prestasi</h3>
            <button type="button" onclick="closeAchModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.achievements.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Kategori Prestasi</label>
                <select name="achievement_category_id" required class="f-select">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="f-label">Nama Lomba / Prestasi</label>
                <input type="text" name="title" required placeholder="LKS Web Technologies 2026" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Scope</label>
                    <select name="scope" required class="f-select">
                        <option value="AKADEMIK">Akademik</option>
                        <option value="NON_AKADEMIK">Non Akademik</option>
                        <option value="VOKASI">Vokasi / Kejuruan</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Tingkat</label>
                    <select name="level" required class="f-select">
                        <option value="KABUPATEN">Kabupaten</option>
                        <option value="PROVINSI">Provinsi</option>
                        <option value="NASIONAL" selected>Nasional</option>
                        <option value="INTERNASIONAL">Internasional</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Peringkat / Juara</label>
                    <input type="text" name="rank" placeholder="Juara 1 Emas" class="f-input">
                </div>
                <div>
                    <label class="f-label">Tanggal Perolehan</label>
                    <input type="date" name="achievement_date" required class="f-input">
                </div>
            </div>

            <div>
                <label class="f-label">Penyelenggara</label>
                <input type="text" name="organizer" placeholder="Kemendikbud / Dinas Pendidikan" class="f-input">
            </div>

            <div>
                <label class="f-label">Deskripsi Singkat</label>
                <textarea name="description" rows="2" class="f-textarea"></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_featured" value="1" class="rounded text-blueprim">
                <label class="text-xs font-medium text-bluedark">Tampilkan di Beranda (Featured)</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeAchModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAchModal() {
        document.getElementById('achModal').classList.remove('hidden');
    }
    function closeAchModal() {
        document.getElementById('achModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
