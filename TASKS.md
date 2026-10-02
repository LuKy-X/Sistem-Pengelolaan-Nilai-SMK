# TASK TRACKER & KOORDINASI TIM - SISTEM PENGELOLAAN NILAI SMK

Dokumen pelacak pekerjaan antar pengembang dan AI Agent untuk mencegah tumpang tindih dan memastikan seluruh dependensi terpenuhi secara berurutan.

---

## DONE

| ID | Modul | Deskripsi | Owner | Files Involved | Status |
|---|---|---|---|---|---|
| FND-01 | Environment & Setup | Konfigurasi MySQL (`smk_nilai`), PHP 8.4 Laragon, Tailwind v4, Vite | Lead Architect | `.env`, `package.json`, `vite.config.js` | DONE |
| FND-02 | AI Team Documentation | Pembuatan 5 dokumen acuan utama (`PROJECT_CONTEXT.md`, `DATABASE_DESIGN.md`, `ARCHITECTURE.md`, `AI_GUIDELINES.md`, `TASKS.md`) | Lead Architect | `/*.md` | DONE |
| FND-03 | Enums | Pembuatan 16 domain PHP backed enums di `app/Enums/` | Senior Developer | `app/Enums/*.php` | DONE |
| FND-04 | Database Migrations | Pembuatan 28 berkas migrasi terurut mencakup seluruh 58+ tabel ternormalisasi | Senior Developer | `database/migrations/*.php` | DONE |
| FND-05 | Eloquent Models | Pembuatan 62 Model Eloquent dengan atribut PHP 8.4 `#[Fillable]`, casts, dan relasi | Senior Developer | `app/Models/*.php` | DONE |
| FND-06 | Middleware & Bootstrap | Pembuatan `RoleMiddleware` dan pendaftaran alias di `bootstrap/app.php` | Senior Developer | `app/Http/Middleware/RoleMiddleware.php`, `bootstrap/app.php` | DONE |
| FND-07 | Policies | Pembuatan Policy dasar untuk Gradebook, Assessment, Submission, ExitPermit, Discipline | Senior Developer | `app/Policies/*.php` | DONE |
| FND-08 | Route Skeleton | Pengelompokan 76 route di `routes/web.php` (Public, Auth, Admin, Guru, Siswa, BK) | Senior Developer | `routes/web.php` | DONE |
| FND-09 | Seeders & Factories | Pembuatan Seeder dasar realistis & 11 Factory untuk entitas inti | Senior Developer | `database/seeders/*.php`, `database/factories/*.php` | DONE |
| FND-10 | Automated Testing | Pembuatan test Feature/Unit untuk validasi relasi dan integritas database (14 passed) | Senior Developer | `tests/Feature/*.php` | DONE |
| MOD-01 | Auth & Login System | Sistem login split-screen (username/email), validasi akun aktif, auto-redirect role, & logout | Lead Architect | `app/Http/Controllers/Auth/LoginController.php`, `resources/views/auth/login.blade.php`, `tests/Feature/AuthTest.php` | DONE |
| MOD-03 | Teacher Gradebook | Buku nilai spreadsheet digital, kolom asesmen dinamis, update skor batch, & export CSV | Senior Developer | `app/Http/Controllers/Teacher/GradebookController.php`, `resources/views/teacher/gradebooks/*.blade.php` | DONE |
| MOD-04 | Teacher Assessment & Rubrik | Manajemen tugas, aturan potongan telat, rubrik penskoran multi-kriteria, & koreksi submission | Senior Developer | `app/Http/Controllers/Teacher/AssessmentController.php`, `RubricController.php`, `resources/views/teacher/assessments/*.blade.php` | DONE |
| MOD-06 | Teacher Journal & Presensi | Jurnal harian mengajar, jam ke- sd ke-, presensi Hadir/S/I/A, dan pencatatan siswa | Senior Developer | `app/Http/Controllers/Teacher/JournalController.php`, `resources/views/teacher/journals/*.blade.php` | DONE |
| MOD-14 | Catatan Nilai & Profil | Catatan evaluasi/remedial siswa per rombel dan pengelolaan data profil/keamanan guru | Senior Developer | `app/Http/Controllers/Teacher/GradeNoteController.php`, `ProfileController.php`, `tests/Feature/TeacherPortalTest.php` | DONE |
| MOD-02 | Admin Portal & Akademik | Dashboard admin, Tahun Ajaran, Semester, Jurusan, Rombel/Kelas, Mapel, Teaching Assignments, Jadwal KBM, Siswa, Guru, Users, Presensi & Export CSV, Monitoring Nilai & BK, serta Pengelolaan Konten CMS Sekolah | AI Agent | `app/Http/Controllers/Admin/*.php`, `app/Http/Requests/Admin/*.php`, `resources/views/admin/**/*.blade.php`, `resources/views/layouts/admin.blade.php`, `tests/Feature/AdminPortalTest.php` | DONE |

---

## IN PROGRESS

*(Sesi Role Admin Portal selesai dengan 42 test passing, 111 assertions, dan Laravel Pint code style formatting 100% terverifikasi)*

---

## TODO

| ID | Modul | Deskripsi | Owner | Dependency | Status |
|---|---|---|---|---|---|
| MOD-05 | Student Submission | Pengumpulan jawaban teks/file oleh siswa, deteksi telat, rekam nilai & feedback | Team | MOD-04 | TODO |
| MOD-07 | BK Izin Keluar & Timer | Alur pengajuan izin keluar, approval BK, timer kepulangan, & banding terlambat | Team | MOD-01 | TODO |
| MOD-08 | BK Disiplin & SP | Pencatatan pelanggaran/penghargaan, kalkulasi delta poin, & penerbitan SP1/2/3 | Team | MOD-01 | TODO |
| MOD-09 | CMS & Profil Sekolah | Landing page sekolah, profil, visi-misi, jurusan, fasilitas, artikel/berita | Team | MOD-01 | TODO |
| MOD-10 | PPDB | Informasi jalur pendaftaran, alur seleksi, syarat, dan rincian biaya | Team | MOD-09 | TODO |
| MOD-11 | Prestasi & Alumni | Showcase prestasi siswa/sekolah & rekam jejak/cerita alumni sukses | Team | MOD-09 | TODO |
| MOD-12 | Produk Siswa | Katalog produk kreatif teaching factory karya siswa per jurusan | Team | MOD-09 | TODO |
| MOD-13 | BKK & PKL Career Center | Bursa kerja khusus, lowongan magang PKL, dan riwayat lamaran siswa | Team | MOD-09 | TODO |

---

## REVIEW

*(Belum ada task dalam tahap review)*

---

## BLOCKED

*(Tidak ada task terblokir)*
