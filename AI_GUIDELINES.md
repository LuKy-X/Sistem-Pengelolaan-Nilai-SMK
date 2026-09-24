# AI AGENT GUIDELINES & STANDING RULES - SISTEM PENGELOLAAN NILAI SMK

> **MANDATORY INSTRUCTIONS FOR ALL AI AGENTS & PAIR PROGRAMMERS**
> Before coding, inspect the current implementation and documentation.
> Every AI agent operating in this repository is strictly bound by the 20 rules below. Failure to comply will lead to broken relationships, data loss, and architectural divergence.

---

## 1. The 20 Mandatory Commandments

1. **Baca Context Sebelum Coding**: Wajib membaca `PROJECT_CONTEXT.md`, `DATABASE_DESIGN.md`, dan `ARCHITECTURE.md` sebelum melakukan perubahan kode atau skema.
2. **Periksa DATABASE_DESIGN.md Sebelum Mengubah Basis Data**: Jangan mengubah nama kolom, tipe data, foreign key, atau constraint tanpa memperbarui `DATABASE_DESIGN.md`.
3. **Jangan Mengubah Relationship Sembarangan**: Hubungan `teacher -> teaching_assignments -> classes/subjects/gradebooks` adalah hukum mutlak proyek. Dilarang menambahkan `class_id` langsung pada profil guru.
4. **Jangan Menghapus Tabel**: Dilarang menghapus atau mengabaikan tabel yang terdaftar pada desain sistem meskipun belum ada UI atau controller aktif yang menggunakannya.
5. **Jangan Menambah Dependency Tanpa Alasan**: Dilarang menginstal package composer atau npm baru tanpa instruksi atau persetujuan eksplisit.
6. **Dilarang Menggunakan Livewire**: Aplikasi ini berbasis Laravel native Blade + Tailwind CSS.
7. **Dilarang Menggunakan Vue**: Dilarang memasang komponen Vue atau runtime Vue.
8. **Dilarang Menggunakan React**: Dilarang memasang komponen React atau runtime React.
9. **Jangan Membuat API Jika Belum Dibutuhkan**: Dilarang membangun REST API / GraphQL tanpa kebutuhan riil. Seluruh interaksi menggunakan standard HTTP Blade request-response.
10. **Gunakan Blade**: Gunakan Blade view modular dan reusable Blade components di `resources/views/components/`.
11. **Gunakan Form Request**: Seluruh validasi input form wajib ditempatkan pada class Form Request di `app/Http/Requests/{Role}/`.
12. **Gunakan Policy**: Setiap otorisasi hak akses terhadap model wajib dievaluasi melalui Laravel Policies di `app/Policies/`.
13. **Gunakan Service Hanya Ketika Diperlukan**: Tempatkan logika perhitungan matematika/alur bisnis multi-step di `app/Services/`. Jangan over-engineering untuk operasi CRUD sederhana.
14. **Jangan Memasukkan Business Logic Berat ke Controller**: Controller hanya bertugas orkestrasi request, validasi, pemanggilan service/model, dan response.
15. **Dilarang Melakukan Query Database dari Blade**: Seluruh data yang dibutuhkan view harus dipersiapkan di controller/view-composer. Dilarang menulis query Eloquent atau facade DB di dalam template Blade.
16. **Jangan Membuat Duplicate Implementation**: Periksa apakah method, helper, atau service sejenis sudah ada sebelum menulis implementasi baru.
17. **Pahami Struktur Sebelum Mengubah Existing Code**: Lakukan inspeksi file tetangga dan riwayat sebelumnya sebelum mengedit kode yang ada.
18. **Update Dokumentasi Setelah Perubahan Signifikan**: Setiap penambahan tabel, field, route grup, atau service baru wajib dicatat dan disinkronkan ke dokumen markdown terkait.
19. **Jaga Backward Compatibility**: Pastikan migrasi data dan struktur entitas tidak mematahkan fungsionalitas yang telah berjalan pada modul lain.
20. **Jangan Menghapus Fitur Milik Agen Lain**: Setiap agen harus memahami dependensi antar modul (misal: keterkaitan nilai dan absensi terhadap teaching assignment).

---

## 2. Checklist Sebelum Melakukan Perubahan (Pre-flight Checklist)

- [ ] Apakah saya sudah membaca `PROJECT_CONTEXT.md` dan `DATABASE_DESIGN.md`?
- [ ] Apakah perubahan saya mempengaruhi relasi antartabel? Jika ya, apakah sesuai kamus data?
- [ ] Apakah ada Form Request untuk memvalidasi input ini?
- [ ] Apakah aksi ini dilindungi oleh Middleware role dan Policy?
- [ ] Apakah ada unit/feature test yang menguji integritas perubahan ini?
