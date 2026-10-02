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
| MOD-15 | Portal Siswa | Dashboard, tugas, nilai, jadwal, izin keluar, banding, buku saku, profil, rekap nilai, status akademik, notifikasi | Student Team | `app/Http/Controllers/Student/*`, `resources/views/student/**`, `tests/Feature/StudentPortalTest.php`, `tests/Feature/StudentNotificationTest.php` | DONE |
| FIX-01 | Test suite MySQL | Test dipindahkan dari SQLite in-memory ke MySQL (`sistem_nilai_test`) karena SQLite tidak menegakkan foreign key dan AUTO_INCREMENT-nya di-rollback | Student Team | `phpunit.xml` | DONE |
| FIX-02 | Seeder hardcoded ID | 20 id angka di `SampleBkDataSeeder` diganti resolusi berbasis identitas (NIS, nama kategori) | Student Team | `database/seeders/SampleBkDataSeeder.php` | DONE |

---

## UTANG TEKNIS (di luar cakupan modul siswa)

| ID | Modul | Masalah | Dampak | Catatan |
|---|---|---|---|---|
| DEBT-01 | Guru — `Teacher\AssessmentController::gradeSubmission` (sekitar baris 300-315) | Menulis `status => 'GRADED'` yang **bukan nilai enum valid** (`SubmissionStatus` hanya punya `DRAFT`/`SUBMITTED`/`REVIEWED`), menulis `score`/`feedback`/`graded_by`/`graded_at` yang **tidak ada di `#[Fillable]`** sehingga dibuang diam-diam, dan membaca `$submission->is_late` yang tidak pernah ada | Setiap kali Guru menilai tugas lewat `POST /guru/assessments/{a}/submissions/{s}/grade` berakhir **HTTP 500**. Notifikasi `SubmissionGraded` juga tidak terkirim untuk jalur ini | Sengaja tidak diperbaiki dari modul siswa. Perbaikan: ganti `'GRADED'` menjadi `SubmissionStatus::Reviewed`, gunakan kolom `teacher_feedback`/`reviewed_by`/`reviewed_at`, dan baca `late_minutes` (bukan `is_late`) lalu terapkan `latePolicy` |
| DEBT-02 | Guru — `Teacher\JournalController::store` (sekitar baris 142) | Memakai `AttendanceStatus::Permitted`, sedangkan enum hanya punya `Present`/`Sick`/`Permit`/`Absent` | `Error: Undefined constant` (HTTP 500) bila `absences[]` pernah dikirim | Form jurnal guru juga belum punya input per-siswa, jadi `journal_attendances` praktis tidak pernah terisi |
| DEBT-03 | Guru — `resources/views/teacher/journals/index.blade.php` (sekitar baris 115-118) | Membaca `$j->hadir_count`, `$sakit_count`, `$izin_count`, `$alpha_count` yang tidak menjadi kolom di `class_journals` (angkanya disimpan sebagai teks di `notes`) | Empat kotak ringkasan absensi selalu kosong | Akan ikut benar setelah DEBT-02 dan form jurnal diperbaiki |
| DEBT-04 | Test — `CounselorModuleTest::test_deleting_a_discipline_record_restores_the_point_balance` | Ekspektasi saldo disiplin mengasumsikan saldo awal 100, padahal `SampleBkDataSeeder` sudah membuat catatan untuk siswa tersebut sehingga saldo sebenarnya lebih rendah | 1 test gagal di suite | Diperbaiki sementara di branch modul siswa pada commit `f72949d`, lalu **dikembalikan** ke kondisi semula karena `CounselorModuleTest` berada di luar cakupan. Perlu dikerjakan oleh pemilik modul BK |

---

## IN PROGRESS

*(Sesi Role Guru & Authentication selesai dengan 32 test passing dan Pint formatting terverifikasi)*

---

## TODO

| ID | Modul | Deskripsi | Owner | Dependency | Status |
|---|---|---|---|---|---|
| MOD-02 | Admin Akademik | Manajemen Tahun Ajaran, Semester, Jurusan, Tingkat, Kelas, Mapel, Teaching Assignments | Team | MOD-01 | TODO |
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
