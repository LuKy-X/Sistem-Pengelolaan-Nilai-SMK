@extends('layouts.teacher')

@section('title', 'Catatan Nilai — Guru')

@section('content')
<div class="space-y-6">

    <!-- Header Title -->
    <div>
        <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Catatan Nilai</h1>
        <p class="text-sm text-bluedark/60 mt-1">Kelola Catatan Nilai Siswa</p>
    </div>

    @if(! $selectedAssignment)
        <!-- PANEL 1: Pilih Kelas Grid matching mockup -->
        <div class="panel p-5">
            <div class="mb-4">
                <h2 class="font-heading font-semibold text-bluedark text-[15px]">Catatan Nilai</h2>
                <p class="text-xs text-bluedark/50 mt-0.5">Pilih kelas untuk mengelola catatan nilai siswa</p>
            </div>

            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                @forelse($assignments as $assignment)
                    <a href="{{ route('teacher.grade-notes.index', ['assignment_id' => $assignment->id]) }}" class="kelas-card group">
                        <div class="flex items-center justify-between mb-2">
                            <div class="crud-card__icon group-hover:bg-blueprim group-hover:text-white transition-colors">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            </div>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bluesoft"><polyline points="9 18 15 12 9 6"/></svg>
                        </div>
                        <div class="font-heading font-bold text-bluedark text-sm">
                            {{ $assignment->schoolClass?->name ?? 'XII RA' }}
                        </div>
                        <div class="text-xs text-bluedark/50">
                            {{ $assignment->subject?->name ?? 'Rekayasa Perangkat Lunak' }}
                        </div>
                        <div class="kelas-card__meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> 
                                {{ $assignment->schoolClass?->students_count ?? 36 }} Siswa
                            </span>
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> 
                                {{ $assignment->weekly_hours }} Jam / Minggu
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-blue">Gasal</span>
                            <span class="badge badge-gray">2026/2027</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-bluedark/50">
                        Belum ada data penugasan kelas.
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <!-- PANEL 2: Detail Catatan Nilai Kelas matching mockup -->
        <div>
            <p class="text-sm text-bluedark/60 mb-4">
                <a href="{{ route('teacher.grade-notes.index') }}" class="font-semibold text-blueprim hover:underline">Catatan Nilai</a> / 
                <span class="font-semibold text-bluedark">{{ $selectedAssignment->schoolClass?->name }}</span>
            </p>

            <div class="panel p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="font-heading font-semibold text-bluedark text-[15px]">
                            Catatan Nilai — Kelas {{ $selectedAssignment->schoolClass?->name }}
                        </h2>
                        <p class="text-xs text-bluedark/50">{{ $selectedAssignment->subject?->name }} • Gasal 2026/2027</p>
                    </div>
                    <button type="button" onclick="openNoteModal()" class="btn btn-primary btn-sm shadow-xs">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> 
                        Catatan Baru
                    </button>
                </div>

                <div class="overflow-x-auto rounded-xl border border-[#E3F2FD]">
                    <table class="tbl text-xs">
                        <thead>
                            <tr>
                                <th style="width:3.5rem" class="text-center">No</th>
                                <th class="min-w-[180px]">Nama Siswa</th>
                                <th class="w-32">Kategori</th>
                                <th>Catatan Nilai / Evaluasi</th>
                                <th class="w-28 text-center">Tanggal</th>
                                <th style="width:6rem" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E3F2FD]">
                            @forelse($notes as $index => $item)
                                <tr class="hover:bg-[#F5FAFF]">
                                    <td class="text-center font-semibold text-slate-500">{{ $index + 1 }}</td>
                                    <td class="font-bold text-bluedark">{{ $item->student?->full_name ?? 'Siswa' }}</td>
                                    <td>
                                        <span class="badge {{ $item->category === 'REMEDIAL' ? 'badge-yellow' : ($item->category === 'PRESTASI' ? 'badge-green' : 'badge-blue') }}">
                                            {{ $item->category }}
                                        </span>
                                    </td>
                                    <td class="text-ink/80 leading-relaxed">{{ $item->note }}</td>
                                    <td class="text-center text-slate-500 text-[11px] whitespace-nowrap">
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('teacher.grade-notes.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-semibold p-1">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-xs text-bluedark/50">
                                        Belum ada catatan nilai untuk kelas ini. Klik "+ Catatan Baru" untuk menambahkan evaluasi siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Catatan Baru -->
        <div id="noteModal" class="fixed inset-0 z-50 bg-ink/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
            <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in-95">
                <div class="form-block__header flex items-center justify-between">
                    <div>
                        <h3>Buat Catatan Nilai Baru</h3>
                        <p>Kelas {{ $selectedAssignment->schoolClass?->name }}</p>
                    </div>
                    <button type="button" onclick="closeNoteModal()" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
                </div>

                <form action="{{ route('teacher.grade-notes.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="teaching_assignment_id" value="{{ $selectedAssignment->id }}">

                    <div>
                        <label class="f-label">Pilih Siswa <span class="text-red-500">*</span></label>
                        <select name="student_id" required class="f-select">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($enrolledStudents as $st)
                                <option value="{{ $st->id }}">{{ $st->full_name }} (NIS: {{ $st->nis }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Kategori Catatan <span class="text-red-500">*</span></label>
                        <select name="category" required class="f-select">
                            <option value="AKADEMIK">Akademik / Materi</option>
                            <option value="REMEDIAL">Rekomendasi Remedial</option>
                            <option value="PERILAKU">Sikap &amp; Keaktifan</option>
                            <option value="PRESTASI">Pencapaian Istimewa</option>
                        </select>
                    </div>

                    <div>
                        <label class="f-label">Isi Catatan Guru <span class="text-red-500">*</span></label>
                        <textarea name="note" rows="3" required placeholder="Tuliskan catatan kemajuan belajar atau evaluasi siswa..." class="f-textarea"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeNoteModal()" class="btn btn-outline btn-sm">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm shadow-md">Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openNoteModal() {
                document.getElementById('noteModal').classList.remove('hidden');
            }
            function closeNoteModal() {
                document.getElementById('noteModal').classList.add('hidden');
            }
        </script>
    @endif

</div>
@endsection
