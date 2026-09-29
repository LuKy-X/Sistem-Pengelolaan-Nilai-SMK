@extends('layouts.admin')

@section('title', 'Manajemen Semester')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Manajemen Semester</h1>
            <p class="text-sm text-bluedark/60 mt-1">Daftar seluruh semester per tahun ajaran dan status keaktifan</p>
        </div>
        <a href="{{ route('admin.academic.years.index') }}" class="btn btn-outline btn-sm">
            &larr; Kelola Tahun Ajaran
        </a>
    </div>

    <div class="panel p-5">
        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Tipe</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($semesters as $sem)
                        <tr>
                            <td class="font-semibold">{{ $sem->academicYear?->name ?? '-' }}</td>
                            <td>{{ $sem->name }}</td>
                            <td>Semester {{ $sem->semester_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($sem->start_date)->translatedFormat('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($sem->end_date)->translatedFormat('d M Y') }}</td>
                            <td>
                                <span class="badge {{ $sem->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $sem->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.academic.semesters.toggle-active', $sem) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $sem->is_active ? 'btn-outline' : 'btn-primary' }} text-xs py-1">
                                        {{ $sem->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-xs text-bluedark/40">Belum ada data semester.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
