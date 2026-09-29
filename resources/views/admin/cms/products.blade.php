@extends('layouts.admin')

@section('title', 'Katalog Produk & Jasa Kreatif Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-xl md:text-2xl font-bold text-bluedark">Produk &amp; Jasa Kreatif Siswa</h1>
            <p class="text-sm text-bluedark/60 mt-1">Showcase produk Teaching Factory dan karya kejuruan siswa SMK</p>
        </div>

        <button type="button" onclick="openProductModal()" class="btn btn-primary btn-sm flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Produk Baru</span>
        </button>
    </div>

    <!-- Table Produk matching template/admin/produk-jasa.html -->
    <div class="panel p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-bluedark text-[15px]">Daftar Produk &amp; Jasa Siswa</h2>
            <span class="text-xs text-bluedark/50">Total: {{ $products->total() }} Produk</span>
        </div>

        <div class="overflow-x-auto">
            <table class="tbl w-full text-left">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Produk / Jasa</th>
                        <th>Jurusan</th>
                        <th>Kategori</th>
                        <th>Estimasi Harga</th>
                        <th>Kontak Pemesanan</th>
                        <th class="w-24">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $idx => $p)
                        <tr>
                            <td>{{ $products->firstItem() + $idx }}</td>
                            <td class="font-semibold text-bluedark">{{ $p->name }}</td>
                            <td>{{ $p->department?->name ?? 'Lintas Jurusan' }}</td>
                            <td>
                                <span class="badge badge-blue text-[10px]">{{ $p->category?->name ?? 'Umum' }}</span>
                            </td>
                            <td class="font-mono text-xs font-semibold text-emerald-700">
                                {{ $p->price ? 'Rp ' . number_format($p->price, 0, ',', '.') : 'Hubungi Kami' }}
                            </td>
                            <td class="text-xs">{{ $p->contact ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $p->status === 'AVAILABLE' ? 'badge-green' : 'badge-gray' }} text-[10px]">
                                    {{ $p->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-xs text-bluedark/40">
                                Belum ada katalog produk siswa yang didaftarkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>

</div>

<!-- Modal Tambah Produk Siswa -->
<div id="productModal" class="hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl animate-in fade-in duration-150 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-bluelight">
            <h3 class="font-heading font-bold text-lg text-bluedark">Daftarkan Produk Baru</h3>
            <button type="button" onclick="closeProductModal()" class="text-bluedark/40 hover:text-bluedark text-xl font-bold">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.cms.products.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="f-label">Nama Produk / Jasa</label>
                <input type="text" name="name" required placeholder="Aplikasi Kasir POS / Kain Batik Tulis" class="f-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Kategori</label>
                    <select name="category_id" required class="f-select">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f-label">Jurusan</label>
                    <select name="department_id" class="f-select">
                        <option value="">-- Lintas Jurusan --</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="f-label">Harga (Rp)</label>
                    <input type="number" name="price" min="0" placeholder="50000" class="f-input">
                </div>
                <div>
                    <label class="f-label">Status Ketersediaan</label>
                    <select name="status" required class="f-select">
                        <option value="AVAILABLE">Tersedia (Ready)</option>
                        <option value="PRE_ORDER">Pre-Order</option>
                        <option value="OUT_OF_STOCK">Habis</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="f-label">Kontak Pemesanan (WhatsApp/Email)</label>
                <input type="text" name="contact" placeholder="08xxxxxxxx" class="f-input">
            </div>

            <div>
                <label class="f-label">Deskripsi Produk</label>
                <textarea name="description" rows="2" class="f-textarea"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-bluelight">
                <button type="button" onclick="closeProductModal()" class="btn btn-outline btn-sm">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openProductModal() {
        document.getElementById('productModal').classList.remove('hidden');
    }
    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
