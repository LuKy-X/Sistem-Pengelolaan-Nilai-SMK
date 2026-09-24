# ARCHITECTURE & CODE CONVENTIONS - SISTEM PENGELOLAAN NILAI SMK

> **SINGLE SOURCE OF TRUTH UNTUK ARSITEKTUR KODE & PENGEMBANGAN**
> Setiap pengembang dan AI Agent wajib mematuhi panduan struktur, konvensi, dan pola perancangan di bawah ini.

---

## 1. Prinsip Dasar Arsitektur

1. **Monolit Modular Terintegrasi**: Sistem dibangun sebagai satu aplikasi Laravel terpadu untuk 4 role (Admin, Guru, Siswa, Guru BK) dan publik, bukan beberapa micro-app yang terpisah.
2. **Native Laravel-First**: Memaksimalkan fitur native: Eloquent ORM, Form Requests, Route Model Binding, Policies, Custom Middleware, Sessions, Notifications, dan Storage.
3. **No Frontend JavaScript Frameworks**: Dilarang keras menggunakan Livewire, Vue, React, Inertia, atau SPA frameworks. Seluruh antarmuka menggunakan **Blade + Tailwind CSS v4 + Vanilla JS seperlunya**.
4. **Clean Layered Separation**:
   - **Controllers**: Hanya bertugas menerima request, memanggil otorisasi/validasi, mendelegasikan ke service, dan mengembalikan view atau redirect. Tidak boleh menampung query database berat atau kalkulasi rumus kompleks.
   - **Form Requests**: Menangani seluruh validasi input form dan otorisasi dasar input.
   - **Services**: Menangani business logic kompleks lintas-entitas (kalkulasi nilai, kalkulasi denda keterlambatan, workflow persetujuan izin, dan kalkulasi saldo poin).
   - **Policies**: Menangani seluruh otorisasi akses server-side pada model.

---

## 2. Struktur Direktori Proyek

```
app/
├── Enums/                          # Domain Enums (PHP 8.4 Backed Enums)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/                  # Controller kelola master data & CMS
│   │   ├── Teacher/                # Controller guru (gradebook, tugas, jurnal, nilai)
│   │   ├── Student/                # Controller siswa (dashboard, tugas, izin)
│   │   ├── Counselor/              # Controller BK (izin, banding, poin disiplin, SP)
│   │   ├── Public/                 # Controller landing page, artikel, PPDB, karir
│   │   └── Auth/                   # Login, logout, credential profile
│   │
│   ├── Middleware/
│   │   └── RoleMiddleware.php      # Otorisasi role berbasis string parameter
│   │
│   └── Requests/
│       ├── Admin/
│       ├── Teacher/
│       ├── Student/
│       ├── Counselor/
│       └── Auth/
│
├── Models/                         # Eloquent Models (PHP 8.4 attributes)
├── Policies/                       # Resource Authorization Policies
├── Services/                       # Domain Business Logic Services
├── Notifications/                  # User notification channels
├── Jobs/                           # Async processing jobs
└── Events/ & Listeners/            # Domain events

bootstrap/
└── app.php                         # Modern Laravel 13 bootstrap & middleware config

config/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── views/
│   ├── layouts/                    # app, guest, admin, teacher, student, counselor
│   ├── components/                 # Reusable Blade UI components (card, modal, alert, etc.)
│   ├── auth/
│   ├── public/
│   ├── admin/
│   ├── teacher/
│   ├── student/
│   └── counselor/
│
├── css/
│   └── app.css                     # Tailwind v4 entrypoint
└── js/
    └── app.js                      # Vanilla JS entrypoint

routes/
└── web.php                         # Single web route file dengan route grouping rapi

tests/
├── Feature/
└── Unit/
```

---

## 3. Gaya Penulisan Model Eloquent (Laravel 13 Modern)

Setiap model menggunakan atribut PHP 8.4 modern:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'field1',
    'field2',
])]
class ExampleModel extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(RelatedModel::class, 'related_id');
    }
}
```

- **Dilarang** menggunakan `$guarded = ['*']` atau `$guarded = []`.
- Model yang menangani kredensial wajib menyematkan `#[Hidden(['password', 'remember_token'])]`.
- Definisikan tipe return secara eksplisit (`BelongsTo`, `HasMany`, `BelongsToMany`, `MorphMany`).

---

## 4. Otorisasi & Keamanan Server-Side

1. **Middleware Registrasi**:
   Middleware role didaftarkan pada `bootstrap/app.php` sesuai standar Laravel 13:
   ```php
   ->withMiddleware(function (Middleware $middleware): void {
       $middleware->alias([
           'role' => \App\Http\Middleware\RoleMiddleware::class,
       ]);
   })
   ```
2. **Policy Enforcement**:
   Semua controller yang memanipulasi resource wajib memanggil policy:
   - `GradebookPolicy`: Memastikan `auth()->user()->teacherProfile->id === $gradebook->teachingAssignment->teacher_id`.
   - `AssessmentPolicy`: Memastikan guru pengajar yang membuat/mengedit, dan siswa terdaftar di rombel yang dapat mengakses.
   - `AssessmentSubmissionPolicy`: Memastikan siswa hanya dapat melihat & mengirim submission atas namanya sendiri.
   - `ExitPermitPolicy`: Siswa hanya mengelola izin miliknya; BK memproses approval dan banding.
   - `DisciplinePolicy`: Hanya Admin dan Guru BK yang berhak mencatat mutasi poin disiplin.

---

## 5. Domain Service Layer

Gunakan Service untuk memisahkan logic berat dari Controller:

- **`GradeCalculationService`**:
  - `calculateSummaryColumn(GradebookColumn $summaryColumn, int $studentId): ?float`
  - Mendukung kalkulasi `AVERAGE`, `SUM`, dan `WEIGHTED_AVERAGE` dari baris sumber (`gradebook_column_sources`).
- **`LatePenaltyService`**:
  - `calculatePenalty(Assessment $assessment, Carbon $submittedAt): array`
  - Menghitung durasi telat (menit), batas grace period, nilai pengurangan (persentase atau poin tetap), serta batas minimum nilai maksimum (`minimum_max_score`).
- **`RubricGradingService`**:
  - `calculateRubricTotal(array $criterionScores): float`
  - Menyimpan rincian ke `rubric_scores` dan meng-update skor akhir di `gradebook_scores`.
- **`ExitPermitService`**:
  - Menangani alur perubahan status izin (`PENDING` -> `APPROVED` / `REJECTED` -> `COMPLETED` / `LATE`).
  - Menghitung selisih waktu keterlambatan terhadap `planned_return_at`.
- **`DisciplineService`**:
  - Mencatat mutasi poin di `discipline_records` dalam database transaction.
  - Memeriksa akumulasi saldo terhadap ambang batas peringatan (`warning_threshold`, `sp1_threshold`, `sp2_threshold`, `sp3_threshold`) untuk memicu penerbitan SP jika diperlukan.

---

## 6. Konvensi Penamaan Route

Gunakan named routes konsisten di `routes/web.php`:

```
admin.dashboard
admin.users.*
admin.teachers.*
admin.students.*
admin.classes.*
admin.subjects.*
admin.teaching-assignments.*

teacher.dashboard
teacher.gradebooks.*
teacher.gradebooks.columns.*
teacher.assessments.*
teacher.submissions.*
teacher.rubrics.*
teacher.journals.*

student.dashboard
student.assignments.*
student.submissions.*
student.exit-permits.*
student.exit-permits.appeals.*

counselor.dashboard
counselor.exit-permits.*
counselor.appeals.*
counselor.discipline-records.*
counselor.disciplinary-letters.*
```
