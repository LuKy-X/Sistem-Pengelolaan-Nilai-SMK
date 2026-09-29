@extends('layouts.admin')

@section('title', 'Manajemen Berita & Artikel')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Berita &amp; Artikel Sekolah</h1>
            <p class="text-sm text-bluedark/60 mt-1">Publikasi pengumuman sekolah, kegiatan, dan berita prestasi</p>
        </div>
    </div>

    <!-- Table Berita -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Artikel</h2>
            <span class="text-xs text-bluedark/50">Total: {{ $articles->total() }} Artikel</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Judul Berita / Artikel</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Tanggal Terbit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $idx => $art)
                        <tr>
                            <td>{{ $articles->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">
                                <div>{{ $art->title }}</div>
                                <span class="text-[11px] text-bluedark/50 font-mono">{{ $art->slug }}</span>
                            </td>
                            <td>
                                <span class="badge badge-blue text-[10px]">{{ $art->category?->name ?? 'Berita' }}</span>
                            </td>
                            <td class="text-xs">{{ $art->author?->name ?? 'Admin Sekolah' }}</td>
                            <td class="text-xs">{{ $art->created_at?->translatedFormat('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada artikel atau pengumuman sekolah yang dipublikasikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $articles->links() }}
        </div>
    </div>

</div>
@endsection
