# DATABASE DESIGN - SISTEM PENGELOLAAN NILAI SMK

> **SINGLE SOURCE OF TRUTH UNTUK STRUKTUR BASIS DATA**
> Seluruh developer dan AI Agent dilarang memodifikasi skema migrasi atau asumsi relasi database tanpa merujuk dan memperbarui dokumen ini.

---

## 1. Prinsip Desain & Integritas Data

1. **Normalized Relational Schema**: Mencegah redundansi data dengan model relasional 3NF yang konsisten.
2. **Dynamic Columns via Rows**: Kolom buku nilai dimodelkan sebagai entitas baris pada tabel `gradebook_columns`, bukan skema DDL dinamis (`ALTER TABLE`).
3. **Data Integrity & Deletion Strategy**:
   - `RESTRICT` pada data induk/historis krusial (guru, siswa, kelas, mata pelajaran, gradebook, assessment, jurnal) agar histori nilai dan kehadiran tidak hilang mendadak akibat penghapusan data induk.
   - `CASCADE` hanya pada tabel komposisi murni / pivot internal (misal: `rubric_criteria` milik `rubric`, `assessment_submissions` milik `assessment`, atau `role_user`).
   - `SET NULL` untuk relasi opsional jika entitas rujukan dihapus (misal: `homeroom_teacher_id` pada kelas).
4. **Unique Constraints Ketat**:
   - Mencegah duplikasi assignment guru (`teaching_assignments` unik untuk guru + mapel + kelas + semester).
   - Mencegah duplikasi nilai siswa per kolom (`gradebook_scores` unik untuk kolom + siswa).
   - Mencegah duplikasi kehadiran siswa per pertemuan jurnal (`journal_attendances` unik untuk jurnal + siswa).
   - Mencegah duplikasi penilaian kriteria rubrik (`rubric_scores` unik untuk skor + kriteria).
5. **No Ephemeral State Storage**: Timer perizinan dihitung berdasarkan selisih waktu terencana dengan waktu sekarang, bukan angka countdown mentah yang disimpan di database.
6. **Auditable Point Balances**: Poin kedisiplinan dihitung dari saldo awal tahun ajaran + rekam jejak mutasi poin (`points_delta`), menjamin riwayat perilaku transparan dan dapat diaudit.

---

## 2. Kamus Data & Spesifikasi 64 Tabel

### A. Autentikasi, Role, & Profil Pengguna

#### 1. `users`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `username`: VARCHAR(50) NOT NULL UNIQUE
- `email`: VARCHAR(255) NOT NULL UNIQUE
- `password`: VARCHAR(255) NOT NULL
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `last_login_at`: TIMESTAMP NULLABLE
- `remember_token`: VARCHAR(100) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

#### 2. `roles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(50) NOT NULL (Admin, Guru, Siswa, Guru BK)
- `code`: VARCHAR(30) NOT NULL UNIQUE (ADMIN, TEACHER, STUDENT, COUNSELOR)
- `created_at`, `updated_at`: TIMESTAMP

#### 3. `role_user` (Pivot)
- `user_id`: BIGINT UNSIGNED NOT NULL, FK -> `users(id)` ON DELETE CASCADE
- `role_id`: BIGINT UNSIGNED NOT NULL, FK -> `roles(id)` ON DELETE CASCADE
- PRIMARY KEY (`user_id`, `role_id`)

#### 4. `teacher_profiles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `users(id)` ON DELETE CASCADE
- `nip`: VARCHAR(30) NOT NULL UNIQUE
- `full_name`: VARCHAR(150) NOT NULL
- `gender`: ENUM('MALE', 'FEMALE') NULLABLE
- `phone`: VARCHAR(20) NULLABLE
- `photo`: VARCHAR(255) NULLABLE
- `status`: VARCHAR(30) NOT NULL DEFAULT 'ACTIVE'
- `created_at`, `updated_at`: TIMESTAMP

#### 5. `student_profiles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NULLABLE UNIQUE, FK -> `users(id)` ON DELETE SET NULL
- `nis`: VARCHAR(20) NOT NULL UNIQUE
- `nisn`: VARCHAR(20) NOT NULL UNIQUE
- `full_name`: VARCHAR(150) NOT NULL
- `gender`: ENUM('MALE', 'FEMALE') NOT NULL
- `birth_place`: VARCHAR(100) NULLABLE
- `birth_date`: DATE NULLABLE
- `phone`: VARCHAR(20) NULLABLE
- `address`: TEXT NULLABLE
- `entry_date`: DATE NULLABLE
- `graduation_date`: DATE NULLABLE
- `status`: VARCHAR(30) NOT NULL DEFAULT 'ACTIVE'
- `created_at`, `updated_at`: TIMESTAMP

#### 6. `staff_profiles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `users(id)` ON DELETE CASCADE
- `employee_number`: VARCHAR(30) NULLABLE UNIQUE
- `full_name`: VARCHAR(150) NOT NULL
- `phone`: VARCHAR(20) NULLABLE
- `photo`: VARCHAR(255) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

---

### B. Struktur Akademik & Kelas

#### 7. `academic_years`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(30) NOT NULL (e.g. "2025/2026")
- `start_date`: DATE NOT NULL
- `end_date`: DATE NOT NULL
- `is_active`: BOOLEAN NOT NULL DEFAULT FALSE
- `created_at`, `updated_at`: TIMESTAMP

#### 8. `semesters`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `academic_year_id`: BIGINT UNSIGNED NOT NULL, FK -> `academic_years(id)` ON DELETE RESTRICT
- `name`: VARCHAR(30) NOT NULL (e.g. "Ganjil", "Genap")
- `semester_number`: TINYINT UNSIGNED NOT NULL (1 / 2)
- `start_date`: DATE NOT NULL
- `end_date`: DATE NOT NULL
- `is_active`: BOOLEAN NOT NULL DEFAULT FALSE
- `created_at`, `updated_at`: TIMESTAMP

#### 9. `departments` (Jurusan / Kompetensi Keahlian)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code`: VARCHAR(20) NOT NULL UNIQUE (e.g. "RPL", "TKR", "DKV")
- `name`: VARCHAR(100) NOT NULL
- `short_name`: VARCHAR(50) NULLABLE
- `description`: TEXT NULLABLE
- `vision`: TEXT NULLABLE
- `mission`: TEXT NULLABLE
- `career_prospects`: TEXT NULLABLE
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 10. `grade_levels` (Tingkat Kelas)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code`: VARCHAR(10) NOT NULL UNIQUE (e.g. "X", "XI", "XII")
- `name`: VARCHAR(50) NOT NULL (e.g. "Kelas 10", "Kelas 11", "Kelas 12")
- `created_at`, `updated_at`: TIMESTAMP

#### 11. `classes` (Rombongan Belajar)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `academic_year_id`: BIGINT UNSIGNED NOT NULL, FK -> `academic_years(id)` ON DELETE RESTRICT
- `department_id`: BIGINT UNSIGNED NOT NULL, FK -> `departments(id)` ON DELETE RESTRICT
- `grade_level_id`: BIGINT UNSIGNED NOT NULL, FK -> `grade_levels(id)` ON DELETE RESTRICT
- `homeroom_teacher_id`: BIGINT UNSIGNED NULLABLE, FK -> `teacher_profiles(id)` ON DELETE SET NULL
- `code`: VARCHAR(30) NOT NULL (e.g. "XII-RPL-1")
- `name`: VARCHAR(100) NOT NULL (e.g. "XII Rekayasa Perangkat Lunak 1")
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`academic_year_id`, `code`)

#### 12. `class_enrollments`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `class_id`: BIGINT UNSIGNED NOT NULL, FK -> `classes(id)` ON DELETE RESTRICT
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `start_date`: DATE NOT NULL
- `end_date`: DATE NULLABLE
- `status`: VARCHAR(20) NOT NULL DEFAULT 'ACTIVE'
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`class_id`, `student_id`)

#### 13. `subjects` (Mata Pelajaran)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `code`: VARCHAR(20) NOT NULL UNIQUE (e.g. "MTK", "PBO", "BD")
- `name`: VARCHAR(100) NOT NULL
- `category`: VARCHAR(50) NOT NULL DEFAULT 'MUATAN_KEJURUAN' (MUATAN_NASIONAL, MUATAN_KEWILAYAHAN, MUATAN_KEJURUAN)
- `department_id`: BIGINT UNSIGNED NULLABLE, FK -> `departments(id)` ON DELETE SET NULL
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 14. `teaching_assignments` (Pivot Sentral Pengajaran)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `teacher_id`: BIGINT UNSIGNED NOT NULL, FK -> `teacher_profiles(id)` ON DELETE RESTRICT
- `subject_id`: BIGINT UNSIGNED NOT NULL, FK -> `subjects(id)` ON DELETE RESTRICT
- `class_id`: BIGINT UNSIGNED NOT NULL, FK -> `classes(id)` ON DELETE RESTRICT
- `semester_id`: BIGINT UNSIGNED NOT NULL, FK -> `semesters(id)` ON DELETE RESTRICT
- `weekly_hours`: INT UNSIGNED NULLABLE DEFAULT 2
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`teacher_id`, `subject_id`, `class_id`, `semester_id`)

---

### C. Jam Pelajaran & Jadwal Mengajar

#### 15. `lesson_periods`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `period_number`: INT UNSIGNED NOT NULL UNIQUE
- `start_time`: TIME NOT NULL
- `end_time`: TIME NOT NULL
- `label`: VARCHAR(50) NOT NULL (e.g. "Jam Ke-1 (07.00 - 07.45)")
- `created_at`, `updated_at`: TIMESTAMP

#### 16. `teaching_schedules`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `teaching_assignment_id`: BIGINT UNSIGNED NOT NULL, FK -> `teaching_assignments(id)` ON DELETE CASCADE
- `day_of_week`: TINYINT UNSIGNED NOT NULL (1=Senin s.d 7=Minggu)
- `start_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `lesson_periods(id)` ON DELETE RESTRICT
- `end_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `lesson_periods(id)` ON DELETE RESTRICT
- `room`: VARCHAR(50) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

---

### D. Buku Nilai Digital (Gradebook) & Kolom Dinamis

#### 17. `gradebooks`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `teaching_assignment_id`: BIGINT UNSIGNED NOT NULL, FK -> `teaching_assignments(id)` ON DELETE RESTRICT
- `name`: VARCHAR(150) NOT NULL
- `description`: TEXT NULLABLE
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 18. `gradebook_categories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebooks(id)` ON DELETE CASCADE
- `name`: VARCHAR(100) NOT NULL (e.g. "Ulangan Harian", "Tugas Praktik")
- `code`: VARCHAR(50) NOT NULL
- `weight`: DECIMAL(5,2) NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `is_included_in_average`: BOOLEAN NOT NULL DEFAULT TRUE
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 19. `gradebook_columns`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebooks(id)` ON DELETE CASCADE
- `category_id`: BIGINT UNSIGNED NULLABLE, FK -> `gradebook_categories(id)` ON DELETE SET NULL
- `name`: VARCHAR(100) NOT NULL (e.g. "UH 1", "Tugas 1", "Rata-rata UH")
- `code`: VARCHAR(50) NULLABLE
- `column_type`: ENUM('SCORE', 'SUMMARY') NOT NULL DEFAULT 'SCORE'
- `calculation_type`: ENUM('AVERAGE', 'SUM', 'WEIGHTED_AVERAGE') NULLABLE
- `max_score`: DECIMAL(5,2) NULLABLE DEFAULT 100.00
- `weight`: DECIMAL(5,2) NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `is_visible`: BOOLEAN NOT NULL DEFAULT TRUE
- `is_included_in_average`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 20. `gradebook_column_sources`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `summary_column_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebook_columns(id)` ON DELETE CASCADE
- `source_column_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebook_columns(id)` ON DELETE CASCADE
- `weight`: DECIMAL(5,2) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`summary_column_id`, `source_column_id`)

#### 21. `gradebook_students`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebooks(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `class_enrollment_id`: BIGINT UNSIGNED NULLABLE, FK -> `class_enrollments(id)` ON DELETE SET NULL
- `status`: VARCHAR(20) NOT NULL DEFAULT 'ACTIVE'
- `joined_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `left_at`: TIMESTAMP NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`gradebook_id`, `student_id`)

---

### E. Rubrik Penilaian

#### 22. `rubrics`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(150) NOT NULL
- `description`: TEXT NULLABLE
- `created_by`: BIGINT UNSIGNED NOT NULL, FK -> `teacher_profiles(id)` ON DELETE RESTRICT
- `status`: VARCHAR(20) NOT NULL DEFAULT 'ACTIVE'
- `created_at`, `updated_at`: TIMESTAMP

#### 23. `rubric_criteria`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `rubric_id`: BIGINT UNSIGNED NOT NULL, FK -> `rubrics(id)` ON DELETE CASCADE
- `criterion`: VARCHAR(200) NOT NULL
- `description`: TEXT NULLABLE
- `max_points`: DECIMAL(5,2) NOT NULL
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

---

### F. Assessment, Kebijakan Keterlambatan, Pengumpulan, & Nilai

#### 24. `teacher_grade_settings`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `teacher_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `teacher_profiles(id)` ON DELETE CASCADE
- `default_late_enabled`: BOOLEAN NOT NULL DEFAULT TRUE
- `default_reduction_type`: ENUM('PERCENTAGE', 'FIXED_POINTS') NOT NULL DEFAULT 'PERCENTAGE'
- `default_reduction_value`: DECIMAL(5,2) NOT NULL DEFAULT 5.00
- `default_interval`: INT NOT NULL DEFAULT 60 (menit)
- `default_grace_minutes`: INT NOT NULL DEFAULT 15 (menit)
- `default_min_max_score`: DECIMAL(5,2) NOT NULL DEFAULT 50.00
- `created_at`, `updated_at`: TIMESTAMP

#### 25. `assessments`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_column_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebook_columns(id)` ON DELETE RESTRICT
- `teaching_assignment_id`: BIGINT UNSIGNED NOT NULL, FK -> `teaching_assignments(id)` ON DELETE RESTRICT
- `type`: ENUM('TASK', 'QUIZ', 'PROJECT', 'EXAM', 'OTHER') NOT NULL DEFAULT 'TASK'
- `title`: VARCHAR(200) NOT NULL
- `description`: TEXT NULLABLE
- `instructions`: LONGTEXT NULLABLE
- `due_at`: TIMESTAMP NULLABLE
- `submission_required`: BOOLEAN NOT NULL DEFAULT TRUE
- `rubric_id`: BIGINT UNSIGNED NULLABLE, FK -> `rubrics(id)` ON DELETE SET NULL
- `created_by`: BIGINT UNSIGNED NOT NULL, FK -> `teacher_profiles(id)` ON DELETE RESTRICT
- `published_at`: TIMESTAMP NULLABLE
- `status`: ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') NOT NULL DEFAULT 'DRAFT'
- `created_at`, `updated_at`: TIMESTAMP

#### 26. `assessment_late_policies`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `assessment_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `assessments(id)` ON DELETE CASCADE
- `enabled`: BOOLEAN NOT NULL DEFAULT TRUE
- `reduction_type`: ENUM('PERCENTAGE', 'FIXED_POINTS') NOT NULL DEFAULT 'PERCENTAGE'
- `reduction_value`: DECIMAL(5,2) NOT NULL DEFAULT 5.00
- `interval`: INT NOT NULL DEFAULT 60
- `grace_period_minutes`: INT NOT NULL DEFAULT 15
- `minimum_max_score`: DECIMAL(5,2) NOT NULL DEFAULT 50.00
- `created_at`, `updated_at`: TIMESTAMP

#### 27. `assessment_submissions`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `assessment_id`: BIGINT UNSIGNED NOT NULL, FK -> `assessments(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `submitted_at`: TIMESTAMP NULLABLE
- `status`: ENUM('DRAFT', 'SUBMITTED', 'REVIEWED') NOT NULL DEFAULT 'DRAFT'
- `content`: LONGTEXT NULLABLE
- `late_minutes`: INT NOT NULL DEFAULT 0
- `teacher_feedback`: TEXT NULLABLE
- `reviewed_by`: BIGINT UNSIGNED NULLABLE, FK -> `teacher_profiles(id)` ON DELETE SET NULL
- `reviewed_at`: TIMESTAMP NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`assessment_id`, `student_id`)

#### 28. `gradebook_scores`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_column_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebook_columns(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `raw_score`: DECIMAL(5,2) NULLABLE
- `final_score`: DECIMAL(5,2) NULLABLE
- `max_score_snapshot`: DECIMAL(5,2) NULLABLE DEFAULT 100.00
- `late_minutes`: INT NULLABLE DEFAULT 0
- `late_deduction`: DECIMAL(5,2) NULLABLE DEFAULT 0.00
- `feedback`: TEXT NULLABLE
- `source`: ENUM('MANUAL', 'RUBRIC') NOT NULL DEFAULT 'MANUAL'
- `graded_by`: BIGINT UNSIGNED NULLABLE, FK -> `teacher_profiles(id)` ON DELETE SET NULL
- `graded_at`: TIMESTAMP NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`gradebook_column_id`, `student_id`)

#### 29. `rubric_scores`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `gradebook_score_id`: BIGINT UNSIGNED NOT NULL, FK -> `gradebook_scores(id)` ON DELETE CASCADE
- `rubric_criterion_id`: BIGINT UNSIGNED NOT NULL, FK -> `rubric_criteria(id)` ON DELETE RESTRICT
- `points_awarded`: DECIMAL(5,2) NOT NULL
- `note`: VARCHAR(255) NULLABLE
- `graded_by`: BIGINT UNSIGNED NOT NULL, FK -> `teacher_profiles(id)` ON DELETE RESTRICT
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`gradebook_score_id`, `rubric_criterion_id`)

---

### G. Jurnal Kelas & Presensi Siswa

#### 30. `class_journals`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `teaching_assignment_id`: BIGINT UNSIGNED NOT NULL, FK -> `teaching_assignments(id)` ON DELETE RESTRICT
- `schedule_id`: BIGINT UNSIGNED NULLABLE, FK -> `teaching_schedules(id)` ON DELETE SET NULL
- `journal_date`: DATE NOT NULL
- `start_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `lesson_periods(id)` ON DELETE RESTRICT
- `end_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `lesson_periods(id)` ON DELETE RESTRICT
- `material`: TEXT NOT NULL
- `notes`: TEXT NULLABLE
- `created_by`: BIGINT UNSIGNED NOT NULL, FK -> `teacher_profiles(id)` ON DELETE RESTRICT
- `created_at`, `updated_at`: TIMESTAMP

#### 31. `journal_attendances`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `journal_id`: BIGINT UNSIGNED NOT NULL, FK -> `class_journals(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `status`: ENUM('PRESENT', 'SICK', 'PERMIT', 'ABSENT') NOT NULL DEFAULT 'PRESENT'
- `note`: VARCHAR(255) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`journal_id`, `student_id`)

---

### H. Layanan BK: Izin Keluar Sekolah & Banding Keterlambatan

#### 32. `exit_permit_reasons`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) NOT NULL (e.g. "Sakit ke Faskes", "Keperluan Keluarga Mendesak")
- `description`: TEXT NULLABLE
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 33. `exit_permits`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `reason_id`: BIGINT UNSIGNED NOT NULL, FK -> `exit_permit_reasons(id)` ON DELETE RESTRICT
- `reason_detail`: TEXT NOT NULL
- `requested_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `planned_exit_at`: TIMESTAMP NOT NULL
- `planned_return_at`: TIMESTAMP NOT NULL
- `approved_at`: TIMESTAMP NULLABLE
- `approved_by`: BIGINT UNSIGNED NULLABLE, FK -> `staff_profiles(id)` ON DELETE SET NULL
- `actual_exit_at`: TIMESTAMP NULLABLE
- `actual_return_at`: TIMESTAMP NULLABLE
- `status`: ENUM('PENDING', 'APPROVED', 'REJECTED', 'COMPLETED', 'LATE', 'CANCELLED') NOT NULL DEFAULT 'PENDING'
- `approval_note`: TEXT NULLABLE
- `rejection_note`: TEXT NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

#### 34. `exit_permit_appeals`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `exit_permit_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `exit_permits(id)` ON DELETE CASCADE
- `submitted_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `reason`: TEXT NOT NULL
- `decision`: ENUM('PENDING', 'ACCEPTED', 'REJECTED') NOT NULL DEFAULT 'PENDING'
- `decision_note`: TEXT NULLABLE
- `decided_by`: BIGINT UNSIGNED NULLABLE, FK -> `staff_profiles(id)` ON DELETE SET NULL
- `decided_at`: TIMESTAMP NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

---

### I. Kedisiplinan Siswa & Surat Peringatan (SP)

#### 35. `discipline_settings`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `academic_year_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `academic_years(id)` ON DELETE CASCADE
- `initial_points`: INT NOT NULL DEFAULT 100
- `minimum_points`: INT NOT NULL DEFAULT 0
- `warning_threshold`: INT NULLABLE DEFAULT 75
- `sp1_threshold`: INT NULLABLE DEFAULT 50
- `sp2_threshold`: INT NULLABLE DEFAULT 30
- `sp3_threshold`: INT NULLABLE DEFAULT 10
- `created_at`, `updated_at`: TIMESTAMP

#### 36. `discipline_categories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(150) NOT NULL
- `type`: ENUM('VIOLATION', 'REWARD') NOT NULL
- `default_points`: INT NOT NULL
- `description`: TEXT NULLABLE
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 37. `discipline_records`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `academic_year_id`: BIGINT UNSIGNED NOT NULL, FK -> `academic_years(id)` ON DELETE RESTRICT
- `category_id`: BIGINT UNSIGNED NOT NULL, FK -> `discipline_categories(id)` ON DELETE RESTRICT
- `points_delta`: INT NOT NULL (Positif untuk reward, negatif untuk pelanggaran)
- `occurred_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `description`: TEXT NOT NULL
- `source_type`: VARCHAR(100) NULLABLE
- `source_id`: BIGINT UNSIGNED NULLABLE
- `created_by`: BIGINT UNSIGNED NOT NULL, FK -> `users(id)` ON DELETE RESTRICT
- `created_at`, `updated_at`: TIMESTAMP

#### 38. `disciplinary_letters`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `academic_year_id`: BIGINT UNSIGNED NOT NULL, FK -> `academic_years(id)` ON DELETE RESTRICT
- `type`: ENUM('SP1', 'SP2', 'SP3') NOT NULL
- `reason`: TEXT NOT NULL
- `issued_at`: DATE NOT NULL
- `issued_by`: BIGINT UNSIGNED NOT NULL, FK -> `users(id)` ON DELETE RESTRICT
- `notes`: TEXT NULLABLE
- `document_path`: VARCHAR(255) NULLABLE
- `status`: VARCHAR(30) NOT NULL DEFAULT 'ACTIVE'
- `created_at`, `updated_at`: TIMESTAMP

---

### J. Profil Sekolah & Department CMS

#### 39. `school_profile`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `school_name`: VARCHAR(150) NOT NULL
- `npsn`: VARCHAR(20) NULLABLE
- `principal_name`: VARCHAR(150) NULLABLE
- `address`: TEXT NULLABLE
- `phone`: VARCHAR(30) NULLABLE
- `email`: VARCHAR(100) NULLABLE
- `website`: VARCHAR(150) NULLABLE
- `description`: TEXT NULLABLE
- `vision`: TEXT NULLABLE
- `mission`: TEXT NULLABLE
- `history`: TEXT NULLABLE
- `logo`: VARCHAR(255) NULLABLE
- `hero_image`: VARCHAR(255) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

#### 40. `department_competencies`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `department_id`: BIGINT UNSIGNED NOT NULL, FK -> `departments(id)` ON DELETE CASCADE
- `title`: VARCHAR(150) NOT NULL
- `description`: TEXT NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

#### 41. `department_facilities`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `department_id`: BIGINT UNSIGNED NOT NULL, FK -> `departments(id)` ON DELETE CASCADE
- `name`: VARCHAR(150) NOT NULL
- `description`: TEXT NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

---

### K. Artikel, Berita, & Publikasi

#### 42. `article_categories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) NOT NULL
- `slug`: VARCHAR(120) NOT NULL UNIQUE
- `created_at`, `updated_at`: TIMESTAMP

#### 43. `articles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `category_id`: BIGINT UNSIGNED NOT NULL, FK -> `article_categories(id)` ON DELETE RESTRICT
- `author_id`: BIGINT UNSIGNED NOT NULL, FK -> `users(id)` ON DELETE RESTRICT
- `title`: VARCHAR(255) NOT NULL
- `slug`: VARCHAR(255) NOT NULL UNIQUE
- `excerpt`: TEXT NULLABLE
- `content`: LONGTEXT NOT NULL
- `thumbnail`: VARCHAR(255) NULLABLE
- `status`: ENUM('DRAFT', 'PUBLISHED', 'ARCHIVED') NOT NULL DEFAULT 'DRAFT'
- `published_at`: TIMESTAMP NULLABLE
- `views`: BIGINT UNSIGNED NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

---

### L. Prestasi & Alumni

#### 44. `achievement_categories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) NOT NULL
- `slug`: VARCHAR(120) NOT NULL UNIQUE
- `created_at`, `updated_at`: TIMESTAMP

#### 45. `achievements`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `achievement_category_id`: BIGINT UNSIGNED NOT NULL, FK -> `achievement_categories(id)` ON DELETE RESTRICT
- `title`: VARCHAR(200) NOT NULL
- `scope`: VARCHAR(50) NOT NULL (e.g. "KABUPATEN", "PROVINSI", "NASIONAL", "INTERNASIONAL")
- `level`: VARCHAR(50) NOT NULL (e.g. "SISWA", "SEKOLAH")
- `achievement_date`: DATE NOT NULL
- `organizer`: VARCHAR(150) NULLABLE
- `rank`: VARCHAR(50) NULLABLE (e.g. "Juara 1")
- `description`: TEXT NULLABLE
- `is_featured`: BOOLEAN NOT NULL DEFAULT FALSE
- `created_at`, `updated_at`: TIMESTAMP

#### 46. `achievement_participants` (Pivot)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `achievement_id`: BIGINT UNSIGNED NOT NULL, FK -> `achievements(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE CASCADE
- `role`: VARCHAR(100) NULLABLE
- `description`: TEXT NULLABLE
- UNIQUE (`achievement_id`, `student_id`)

#### 47. `alumni_profiles`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `student_id`: BIGINT UNSIGNED NOT NULL UNIQUE, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `graduation_year`: YEAR NOT NULL
- `current_occupation`: VARCHAR(150) NULLABLE
- `current_company`: VARCHAR(150) NULLABLE
- `city`: VARCHAR(100) NULLABLE
- `social_link`: VARCHAR(255) NULLABLE
- `is_featured`: BOOLEAN NOT NULL DEFAULT FALSE
- `created_at`, `updated_at`: TIMESTAMP

#### 48. `alumni_stories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `alumni_profile_id`: BIGINT UNSIGNED NOT NULL, FK -> `alumni_profiles(id)` ON DELETE CASCADE
- `title`: VARCHAR(200) NOT NULL
- `story`: LONGTEXT NOT NULL
- `career_story`: TEXT NULLABLE
- `quote`: TEXT NULLABLE
- `is_featured`: BOOLEAN NOT NULL DEFAULT FALSE
- `created_at`, `updated_at`: TIMESTAMP

---

### M. Statistik Situs & PPDB

#### 49. `site_statistics`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `section`: VARCHAR(50) NOT NULL DEFAULT 'HERO'
- `key`: VARCHAR(50) NOT NULL UNIQUE
- `label`: VARCHAR(100) NOT NULL
- `value`: VARCHAR(100) NOT NULL (e.g. "96%", "60+", "1200+")
- `description`: VARCHAR(255) NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 50. `admission_periods`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `academic_year_id`: BIGINT UNSIGNED NOT NULL, FK -> `academic_years(id)` ON DELETE RESTRICT
- `title`: VARCHAR(150) NOT NULL (e.g. "PPDB 2026/2027 Gelombang 1")
- `registration_start`: DATE NOT NULL
- `registration_end`: DATE NOT NULL
- `description`: TEXT NULLABLE
- `status`: ENUM('DRAFT', 'OPEN', 'CLOSED') NOT NULL DEFAULT 'DRAFT'
- `created_at`, `updated_at`: TIMESTAMP

#### 51. `admission_schedule_items`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `admission_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `admission_periods(id)` ON DELETE CASCADE
- `title`: VARCHAR(150) NOT NULL
- `description`: TEXT NULLABLE
- `start_date`: DATE NOT NULL
- `end_date`: DATE NOT NULL
- `step_number`: INT NOT NULL DEFAULT 1
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

#### 52. `admission_paths`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `admission_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `admission_periods(id)` ON DELETE CASCADE
- `name`: VARCHAR(100) NOT NULL (e.g. "Prestasi", "Zonasi", "Reguler")
- `slug`: VARCHAR(120) NOT NULL
- `description`: TEXT NULLABLE
- `quota`: INT UNSIGNED NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 53. `admission_requirements`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `admission_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `admission_periods(id)` ON DELETE CASCADE
- `title`: VARCHAR(200) NOT NULL
- `description`: TEXT NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

#### 54. `admission_fee_items`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `admission_period_id`: BIGINT UNSIGNED NOT NULL, FK -> `admission_periods(id)` ON DELETE CASCADE
- `name`: VARCHAR(150) NOT NULL
- `amount`: DECIMAL(12,2) NOT NULL DEFAULT 0.00
- `description`: TEXT NULLABLE
- `is_free`: BOOLEAN NOT NULL DEFAULT FALSE
- `sort_order`: INT NOT NULL DEFAULT 0
- `created_at`, `updated_at`: TIMESTAMP

---

### N. Produk Siswa & BKK Career Center

#### 55. `product_categories`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) NOT NULL
- `description`: TEXT NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

#### 56. `student_products`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `category_id`: BIGINT UNSIGNED NOT NULL, FK -> `product_categories(id)` ON DELETE RESTRICT
- `department_id`: BIGINT UNSIGNED NULLABLE, FK -> `departments(id)` ON DELETE SET NULL
- `name`: VARCHAR(150) NOT NULL
- `slug`: VARCHAR(180) NOT NULL UNIQUE
- `description`: TEXT NULLABLE
- `price`: DECIMAL(12,2) NULLABLE
- `contact`: VARCHAR(100) NULLABLE
- `status`: VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE'
- `created_at`, `updated_at`: TIMESTAMP

#### 57. `product_students` (Pivot)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `product_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_products(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE CASCADE
- UNIQUE (`product_id`, `student_id`)

#### 58. `career_services`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `title`: VARCHAR(150) NOT NULL
- `slug`: VARCHAR(180) NOT NULL UNIQUE
- `description`: TEXT NULLABLE
- `icon`: VARCHAR(100) NULLABLE
- `content`: LONGTEXT NULLABLE
- `sort_order`: INT NOT NULL DEFAULT 0
- `is_active`: BOOLEAN NOT NULL DEFAULT TRUE
- `created_at`, `updated_at`: TIMESTAMP

#### 59. `career_companies`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(150) NOT NULL
- `industry`: VARCHAR(100) NULLABLE
- `address`: TEXT NULLABLE
- `phone`: VARCHAR(30) NULLABLE
- `email`: VARCHAR(100) NULLABLE
- `website`: VARCHAR(150) NULLABLE
- `logo`: VARCHAR(255) NULLABLE
- `created_at`, `updated_at`: TIMESTAMP

#### 60. `career_opportunities`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `company_id`: BIGINT UNSIGNED NOT NULL, FK -> `career_companies(id)` ON DELETE CASCADE
- `type`: ENUM('JOB', 'INTERNSHIP') NOT NULL DEFAULT 'JOB'
- `title`: VARCHAR(200) NOT NULL
- `description`: TEXT NULLABLE
- `requirements`: TEXT NULLABLE
- `location`: VARCHAR(100) NULLABLE
- `open_date`: DATE NULLABLE
- `close_date`: DATE NULLABLE
- `application_link`: VARCHAR(255) NULLABLE
- `status`: ENUM('OPEN', 'CLOSED') NOT NULL DEFAULT 'OPEN'
- `created_at`, `updated_at`: TIMESTAMP

#### 61. `career_applications`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `opportunity_id`: BIGINT UNSIGNED NOT NULL, FK -> `career_opportunities(id)` ON DELETE CASCADE
- `student_id`: BIGINT UNSIGNED NOT NULL, FK -> `student_profiles(id)` ON DELETE RESTRICT
- `applied_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `status`: VARCHAR(30) NOT NULL DEFAULT 'SUBMITTED'
- `note`: TEXT NULLABLE
- `created_at`, `updated_at`: TIMESTAMP
- UNIQUE (`opportunity_id`, `student_id`)

---

### O. Media & Audit Trail

#### 62. `media` (Polymorphic Storage)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `mediable_type`: VARCHAR(150) NOT NULL
- `mediable_id`: BIGINT UNSIGNED NOT NULL
- `collection`: VARCHAR(50) NOT NULL DEFAULT 'default'
- `disk`: VARCHAR(50) NOT NULL DEFAULT 'public'
- `path`: VARCHAR(255) NOT NULL
- `original_name`: VARCHAR(255) NOT NULL
- `mime_type`: VARCHAR(100) NOT NULL
- `size`: BIGINT UNSIGNED NOT NULL
- `uploaded_by`: BIGINT UNSIGNED NULLABLE, FK -> `users(id)` ON DELETE SET NULL
- `created_at`, `updated_at`: TIMESTAMP
- INDEX (`mediable_type`, `mediable_id`)

#### 63. `audit_logs`
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NULLABLE, FK -> `users(id)` ON DELETE SET NULL
- `action`: VARCHAR(100) NOT NULL (e.g. "GRADE_UPDATED", "PERMIT_APPROVED", "DISCIPLINE_RECORD_CREATED")
- `auditable_type`: VARCHAR(150) NOT NULL
- `auditable_id`: BIGINT UNSIGNED NOT NULL
- `old_values`: JSON NULLABLE
- `new_values`: JSON NULLABLE
- `ip_address`: VARCHAR(45) NULLABLE
- `user_agent`: TEXT NULLABLE
- `created_at`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- INDEX (`auditable_type`, `auditable_id`)
- INDEX (`user_id`, `action`)

---

### P. Notifikasi

#### 64. `notifications`
Tabel standar Laravel untuk channel notifikasi database. Tidak memerlukan
dependency tambahan. Hanya modul siswa yang mengirim notifikasi saat ini,
tetapi kolom `notifiable` berupa morphs sehingga tabel ini dapat dipakai role
apa pun tanpa perubahan skema.

- `id`: UUID PRIMARY KEY
- `type`: VARCHAR(255) NOT NULL (nama kelas notifikasi, e.g. `App\Notifications\AssessmentPublished`)
- `notifiable_type`: VARCHAR(255) NOT NULL
- `notifiable_id`: BIGINT UNSIGNED NOT NULL
- `data`: TEXT NOT NULL (JSON berisi `title`, `body`, `url`, `icon`)
- `read_at`: TIMESTAMP NULLABLE (NULL berarti belum dibaca)
- `created_at`, `updated_at`: TIMESTAMP
- INDEX (`notifiable_type`, `notifiable_id`)

**Tiga event yang memicu notifikasi (dipicu dari model, bukan dari controller
Guru atau Guru BK, agar kedua modul tersebut tidak perlu diubah):**

| Notifikasi | Dipicu saat |
|---|---|
| `AssessmentPublished` | `assessments.status` berubah menjadi `PUBLISHED` → dikirim ke semua siswa aktif di kelas tersebut |
| `SubmissionGraded` | `gradebook_scores` dibuat atau `final_score` berubah → dikirim ke siswa pemilik nilai |
| `ExitPermitDecided` | `exit_permits.status` berubah menjadi `APPROVED` / `REJECTED` / `COMPLETED` / `LATE` → dikirim ke siswa pemilik izin |

Notifikasi hanya dikirim ke `User` yang terhubung dengan `student_profiles`.
Siswa tanpa akun login tidak menerima notifikasi.
