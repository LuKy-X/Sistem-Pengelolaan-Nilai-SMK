# READ THIS FILE BEFORE MODIFYING THE PROJECT.

> **CRITICAL INSTRUCTION FOR ALL DEVELOPERS & AI AGENTS:**
> You MUST read this file (`PROJECT_CONTEXT.md`), along with `DATABASE_DESIGN.md`, `ARCHITECTURE.md`, and `AI_GUIDELINES.md`, before modifying any code or database structure in this repository.
> Never make assumptions about core relationships or business rules without checking these documents first.

---

## 1. Ringkasan & Visi Proyek

**Sistem Pengelolaan Nilai SMK** adalah platform terintegrasi manajemen akademik sekolah kejuruan modern, layanan bimbingan konseling (BK), kesiswaan, dan School CMS. Aplikasi ini dirancang untuk menggantikan pengelolaan nilai manual/spreadsheet dan proses administrasi fisik menjadi ekosistem digital terpadu.

Aplikasi ini dibangun sebagai **satu monolit Laravel modern terintegrasi** (bukan multi-aplikasi terpisah) yang melayani 4 role utama:
- **ADMIN**: Tata kelola master data sekolah, akademik, kurikulum, user, CMS, dan konfigurasi global.
- **GURU (TEACHER)**: Manajemen pengajaran lintas kelas, pembuatan buku nilai digital dinamis, perumusan rubrik, penugasan & penilaian, pengelolaan batas keterlambatan, jurnal kelas, serta absensi mengajar.
- **SISWA (STUDENT)**: Dashboard akademik, penyerahan tugas (teks/file bukti), pemantauan nilai & feedback, jadwal harian, pengajuan izin keluar sekolah realtime & banding keterlambatan, serta rekam poin perilaku yang diizinkan.
- **GURU BK (COUNSELOR)**: Manajemen perizinan keluar sekolah dengan pemantauan timer dan banding, pencatatan rekam pelanggaran/penghargaan disiplin berbasis delta poin, penerbitan Surat Peringatan (SP1, SP2, SP3), serta pembinaan siswa.

---

## 2. Peta Domain & Fitur Utama

Sistem mencakup 17 domain fungsional:

1. **Akademik Dasar**: Tahun ajaran, semester, kompetensi keahlian/jurusan, tingkat kelas (X, XI, XII), rombel/kelas, mata pelajaran, dan riwayat siswa di kelas (`class_enrollments`).
2. **Teaching Assignment (Pivot Inti)**: Hubungan formal pengajaran antara Guru + Mata Pelajaran + Kelas + Semester. Seorang guru dapat mengajar di banyak kelas dan banyak mapel, dan satu kelas diajar oleh banyak guru.
3. **Buku Nilai Digital (Digital Gradebook)**: Buku nilai berbasis teaching assignment. Satu teaching assignment dapat memiliki banyak buku nilai (misal: Buku Nilai Reguler, Remedial, Portofolio).
4. **Kolom Nilai Dinamis**: Buku nilai tidak menggunakan kolom SQL dinamis (`ALTER TABLE`). Kolom dinamis diwakili oleh data baris (`gradebook_columns`) bertipe `SCORE` (nilai riil) atau `SUMMARY` (nilai ringkasan/rata-rata terhitung dari `gradebook_column_sources`).
5. **Penugasan & Aktivitas (Assessments)**: Tugas harian, kuis, project, ujian, terhubung dengan kolom buku nilai tertentu.
6. **Kebijakan Keterlambatan Tugas (Late Policies)**: Guru memiliki default setting global (`teacher_grade_settings`), namun setiap assessment menyimpan snapshot kebijakan keterlambatan aktual (`assessment_late_policies`) untuk melindungi histori nilai lampau.
7. **Pengumpulan Tugas (Submissions)**: Siswa mengirim teks/file lampiran, sistem mencatat keterlambatan menit, guru memberikan nilai dan feedback.
8. **Rubrik Penilaian Multi-Kriteria**: Guru menyusun kriteria penilaian bertingkat; penilaian rubrik merinci poin per kriteria yang mengakumulasi nilai akhir (`gradebook_scores`).
9. **Jadwal Mengajar & Jam Pelajaran**: Jam pelajaran terstruktur (`lesson_periods`) dan jadwal mingguan guru per teaching assignment (`teaching_schedules`).
10. **Jurnal Kelas & Absensi**: Guru mencatat tanggal, jam, materi ajar, catatan kelas, serta absensi siswa (`journal_attendances`: Hadir, Sakit, Izin, Alpha).
11. **Layanan BK - Izin Keluar Sekolah**: Alur pengajuan izin oleh siswa, verifikasi alasan, approval/rejection BK. Timer izin dihitung deterministik dari perbandingan `planned_return_at` vs waktu riil, bukan `remaining_seconds` mentah.
12. **Layanan BK - Banding Keterlambatan (Appeals)**: Siswa yang terlambat kembali dapat mengajukan alasan banding, yang diverifikasi oleh guru BK.
13. **Kedisiplinan & Poin Siswa**: Sistem poin dinamis menggunakan saldo awal + histori delta poin (`points_delta` positif untuk reward, negatif untuk pelanggaran). Saldo terkini dihitung dari histori akumulatif.
14. **Surat Peringatan (Disciplinary Letters)**: Penerbitan SP1, SP2, SP3 saat akumulasi poin menyentuh ambang batas konfigurasi sekolah (`discipline_settings`).
15. **School Profile & CMS**: Profil sekolah, identitas kepala sekolah, visi-misi, fasilitas dan kompetensi keahlian jurusan, artikel dan berita sekolah.
16. **PPDB, Prestasi, & Alumni**: Informasi alur, jalur, syarat, dan biaya PPDB; etalase prestasi siswa/sekolah; rekam jejak lulusan dan kisah sukses alumni.
17. **Katalog Produk Siswa & Career Center (BKK & PKL)**: Showroom produk teaching factory siswa serta bursa kerja khusus (lowongan magang/kerja industri dan pelacakan lamaran).

---

## 3. Konsep Arsitektur Inti yang Dilarang Dilanggar

```
                    TEACHER
                       |
                       v
             TEACHING ASSIGNMENT
             /        |         \
            /         |          \
         CLASS      SUBJECT     SEMESTER
           |                       |
           |                       |
           +----------+------------+
                      |
                      v
                  GRADEBOOK
                      |
              +-------+--------+
              |                |
              v                v
       GRADEBOOK COLUMNS   GRADEBOOK STUDENTS
              |
       +------+-------+
       |              |
       v              v
   ASSESSMENT      SUMMARY
       |              |
   +---+---+          +--> SOURCES
   |       |
SUBMISSION RUBRIC
           |
       CRITERIA
           |
      RUBRIC SCORES
           |
     GRADEBOOK SCORE
```

### Aturan Arsitektur Kritis:
1. **Guru Mengajar Banyak Kelas**: Jangan pernah membuat relasi `teachers.class_id`. Guru terhubung ke kelas melalui `teaching_assignments`.
2. **Buku Nilai Dinamis Tanpa Dynamic SQL**: Jangan pernah memanggil `Schema::table` atau `ALTER TABLE` saat guru menambah kolom nilai. Setiap kolom adalah entitas baris di `gradebook_columns`.
3. **Integritas Nilai**: Satu siswa hanya boleh memiliki satu nilai per kolom gradebook (`UNIQUE(gradebook_column_id, student_id)`).
4. **Class Enrollment vs Gradebook Student**:
   - `class_enrollments`: Siswa terdaftar resmi dalam rombongan belajar / kelas tertentu pada tahun ajaran.
   - `gradebook_students`: Keanggotaan snapshot siswa pada buku nilai spesifik seorang guru.
5. **Timer Izin Keluar**: Jangan pernah menyimpan `remaining_seconds` pada database. Hitung selisih waktu secara dinamis: `now()->diffInMinutes($permit->planned_return_at, false)`.
6. **Poin Disiplin**: Jangan hanya menyimpan `current_points` tanpa histori. Saldo dihitung dari `initial_points + SUM(points_delta)`.
7. **Keamanan & Otorisasi**: Jangan mengandalkan proteksi antarmuka (UI). Server-side authorization (Policy & Middleware) wajib melindungi setiap endpoint. Guru tidak boleh mengakses atau memanipulasi buku nilai milik guru lain.

---

## 4. Struktur Referensi Dokumen

- **Business Overview & Core Concepts**: `PROJECT_CONTEXT.md` (Dokumen ini)
- **Database Schema, Keys, & Constraints**: `DATABASE_DESIGN.md`
- **Application Architecture, Layers, & Conventions**: `ARCHITECTURE.md`
- **AI Agent Strict Guidelines**: `AI_GUIDELINES.md`
- **Task & Progress Tracking**: `TASKS.md`
