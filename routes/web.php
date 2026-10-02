<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\AchievementController;
use App\Http\Controllers\Public\AdmissionController;
use App\Http\Controllers\Public\AlumniController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Public\DepartmentController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SchoolProfileController;
use App\Http\Controllers\Public\StudentProductController;
use App\Http\Controllers\BK\AppealController;
use App\Http\Controllers\BK\CounselingController;
use App\Http\Controllers\BK\DashboardController as CounselorDashboardController;
use App\Http\Controllers\BK\DisciplinaryLetterController;
use App\Http\Controllers\BK\DisciplineController;
use App\Http\Controllers\BK\ExitPermitController;
use App\Http\Controllers\BK\StudentController as CounselorStudentController;
use App\Http\Controllers\Student\AppealController as StudentAppealController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\DisciplineController as StudentDisciplineController;
use App\Http\Controllers\Student\ExitPermitController as StudentExitPermitController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\GradeNoteController;
use App\Http\Controllers\Teacher\GradingController;
use App\Http\Controllers\Teacher\JournalController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\RubricController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Pengelolaan Nilai SMK
|--------------------------------------------------------------------------
*/

// ==========================================
// 0. ROOT DISPATCHER
// ==========================================
Route::get('/', function (Request $request) {
    if (! $request->user()) {
        return redirect()->route('login');
    }

    return redirect()->route($request->user()->dashboardRouteName());
})->name('home');

// ==========================================
// 1. PUBLIC & SCHOOL CMS ROUTES
// ==========================================
Route::name('public.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/profil', [SchoolProfileController::class, 'show'])->name('profile');

    Route::get('/jurusan', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/jurusan/{department:code}', [DepartmentController::class, 'show'])->name('departments.show');

    Route::get('/berita', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/berita/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

    Route::get('/prestasi', [AchievementController::class, 'index'])->name('achievements.index');

    Route::get('/alumni', [AlumniController::class, 'index'])->name('alumni.index');

    Route::get('/ppdb', [AdmissionController::class, 'index'])->name('ppdb.index');

    Route::get('/produk-siswa', [StudentProductController::class, 'index'])->name('products.index');
    Route::get('/produk-siswa/{studentProduct:slug}', [StudentProductController::class, 'show'])->name('products.show');

    // The original public URL was misspelled `/karir`; keep it working.
    Route::redirect('/karir', '/karier')->name('career.legacy');

    Route::get('/karier', [CareerController::class, 'index'])->name('career.index');
});

// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ==========================================
    // 3. ADMIN ROUTES (role:admin)
    // ==========================================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return 'Admin Dashboard';
        })->name('dashboard');

        // Master Data Akademik
        Route::prefix('academic')->group(function () {
            Route::get('/years', function () {
                return 'Kelola Tahun Ajaran';
            })->name('years.index');

            Route::get('/semesters', function () {
                return 'Kelola Semester';
            })->name('semesters.index');

            Route::get('/departments', function () {
                return 'Kelola Jurusan';
            })->name('departments.index');

            Route::get('/classes', function () {
                return 'Kelola Rombel / Kelas';
            })->name('classes.index');

            Route::get('/subjects', function () {
                return 'Kelola Mata Pelajaran';
            })->name('subjects.index');

            Route::get('/teaching-assignments', function () {
                return 'Kelola Penugasan Guru Mengajar';
            })->name('teaching-assignments.index');

            Route::get('/schedules', function () {
                return 'Kelola Jadwal Mengajar';
            })->name('schedules.index');
        });

        // Manajemen Pengguna
        Route::prefix('users')->group(function () {
            Route::get('/teachers', function () {
                return 'Kelola Guru';
            })->name('teachers.index');

            Route::get('/students', function () {
                return 'Kelola Siswa';
            })->name('students.index');

            Route::get('/staff', function () {
                return 'Kelola Staff & BK';
            })->name('staff.index');
        });

        // CMS Management
        Route::prefix('cms')->group(function () {
            Route::get('/school-profile', function () {
                return 'Profil Sekolah CMS';
            })->name('school-profile.edit');

            Route::get('/articles', function () {
                return 'Kelola Berita & Artikel';
            })->name('articles.index');

            Route::get('/achievements', function () {
                return 'Kelola Prestasi';
            })->name('achievements.index');

            Route::get('/alumni', function () {
                return 'Kelola Data Alumni';
            })->name('alumni.index');

            Route::get('/ppdb', function () {
                return 'Kelola PPDB CMS';
            })->name('ppdb.index');

            Route::get('/products', function () {
                return 'Kelola Produk Siswa';
            })->name('products.index');

            Route::get('/career', function () {
                return 'Kelola BKK & Mitra Perusahaan';
            })->name('career.index');
        });
    });

    // ==========================================
    // 4. TEACHER / GURU ROUTES (role:teacher)
    // ==========================================
    Route::middleware('role:teacher')->prefix('guru')->name('teacher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Buku Nilai Digital
        Route::prefix('gradebooks')->name('gradebooks.')->group(function () {
            Route::get('/', [GradebookController::class, 'index'])->name('index');
            Route::get('/create', [GradebookController::class, 'create'])->name('create');
            Route::post('/', [GradebookController::class, 'store'])->name('store');
            Route::get('/{gradebook}', [GradebookController::class, 'show'])->name('show');
            Route::get('/{gradebook}/edit', [GradebookController::class, 'edit'])->name('edit');
            Route::put('/{gradebook}', [GradebookController::class, 'update'])->name('update');
            Route::delete('/{gradebook}', [GradebookController::class, 'destroy'])->name('destroy');
            Route::post('/{gradebook}/columns', [GradebookController::class, 'storeColumn'])->name('columns.store');
            Route::delete('/{gradebook}/columns/{column}', [GradebookController::class, 'destroyColumn'])->name('columns.destroy');
            Route::post('/{gradebook}/scores', [GradebookController::class, 'updateScores'])->name('scores.store');
            Route::get('/{gradebook}/export', [GradebookController::class, 'export'])->name('export');
        });

        // Tugas & Ulangan & Assessment
        Route::prefix('assessments')->name('assessments.')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])->name('index');
            Route::get('/create', [AssessmentController::class, 'create'])->name('create');
            Route::post('/', [AssessmentController::class, 'store'])->name('store');
            Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('show');
            Route::post('/{assessment}/submissions/{submission}/grade', [AssessmentController::class, 'gradeSubmission'])->name('submissions.grade');
        });

        // Penilaian Siswa (Input Nilai Tugas/Ulangan/Remidi)
        Route::prefix('penilaian')->name('grading.')->group(function () {
            Route::get('/', [GradingController::class, 'index'])->name('index');
            Route::post('/', [GradingController::class, 'store'])->name('store');
        });

        // Catatan Nilai Siswa
        Route::prefix('grade-notes')->name('grade-notes.')->group(function () {
            Route::get('/', [GradeNoteController::class, 'index'])->name('index');
            Route::post('/', [GradeNoteController::class, 'store'])->name('store');
            Route::delete('/{gradeNote}', [GradeNoteController::class, 'destroy'])->name('destroy');
        });

        // Rubrik Penilaian
        Route::prefix('rubrics')->name('rubrics.')->group(function () {
            Route::get('/', [RubricController::class, 'index'])->name('index');
            Route::get('/create', [RubricController::class, 'create'])->name('create');
            Route::post('/', [RubricController::class, 'store'])->name('store');
            Route::get('/{rubric}', [RubricController::class, 'show'])->name('show');
            Route::get('/{rubric}/edit', [RubricController::class, 'edit'])->name('edit');
            Route::put('/{rubric}', [RubricController::class, 'update'])->name('update');
            Route::delete('/{rubric}', [RubricController::class, 'destroy'])->name('destroy');
        });

        // Jurnal Kelas & Presensi Mengajar
        Route::prefix('journals')->name('journals.')->group(function () {
            Route::get('/', [JournalController::class, 'index'])->name('index');
            Route::get('/create', [JournalController::class, 'create'])->name('create');
            Route::post('/', [JournalController::class, 'store'])->name('store');
            Route::get('/{journal}', [JournalController::class, 'show'])->name('show');
        });

        // Profil & Pengaturan Guru
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        });
    });

    // ==========================================
    // 5. STUDENT / SISWA ROUTES (role:student)
    // ==========================================
    Route::middleware('role:student')->prefix('siswa')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

        Route::get('/jadwal', [StudentScheduleController::class, 'index'])->name('schedules.index');
        Route::get('/jadwal/{schedule}', [StudentScheduleController::class, 'show'])->name('schedules.show');

        // Nilai & Buku Nilai
        Route::prefix('nilai')->name('grades.')->group(function () {
            Route::get('/', [StudentGradeController::class, 'index'])->name('index');
            // "rekap" harus didaftarkan sebelum "{gradebook}" agar tidak tertangkap
            // sebagai parameter buku nilai.
            Route::get('/rekap', [StudentGradeController::class, 'recap'])->name('recap');
            Route::get('/{gradebook}', [StudentGradeController::class, 'show'])->name('show');
        });

        // Tugas & Pengumpulan
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [StudentAssignmentController::class, 'index'])->name('index');
            Route::get('/{assessment}', [StudentAssignmentController::class, 'show'])->name('show');
            Route::post('/{assessment}/submit', [StudentAssignmentController::class, 'submit'])->name('submit');
        });

        // Izin Keluar Sekolah
        Route::prefix('exit-permits')->name('exit-permits.')->group(function () {
            Route::get('/', [StudentExitPermitController::class, 'index'])->name('index');
            Route::get('/create', [StudentExitPermitController::class, 'create'])->name('create');
            Route::post('/', [StudentExitPermitController::class, 'store'])->name('store');
            Route::get('/{permit}', [StudentExitPermitController::class, 'show'])->name('show');
            Route::post('/{permit}/appeal', [StudentExitPermitController::class, 'appeal'])->name('appeal');
        });

        // Banding Keterlambatan
        Route::get('/banding', [StudentAppealController::class, 'index'])->name('appeals.index');
        Route::get('/banding/{appeal}', [StudentAppealController::class, 'show'])->name('appeals.show');

        // Buku Saku (Poin Disiplin)
        Route::get('/disiplin', [StudentDisciplineController::class, 'index'])->name('discipline.index');

        // Profil & Pengaturan Akun
        Route::prefix('profil')->name('profile.')->group(function () {
            Route::get('/', [StudentProfileController::class, 'index'])->name('index');
            Route::put('/', [StudentProfileController::class, 'update'])->name('update');
            Route::put('/password', [StudentProfileController::class, 'updatePassword'])->name('password');
        });
    });

    // ==========================================
    // 6. COUNSELOR / BK ROUTES (role:counselor)
    // ==========================================
    Route::middleware('role:counselor')->prefix('bk')->name('counselor.')->group(function () {
        Route::get('/dashboard', [CounselorDashboardController::class, 'index'])->name('dashboard');

        // Manajemen Izin Keluar & Timer
        Route::prefix('exit-permits')->name('exit-permits.')->group(function () {
            Route::get('/', [ExitPermitController::class, 'index'])->name('index');
            Route::get('/{permit}', [ExitPermitController::class, 'show'])->name('show');
            Route::post('/{permit}/approve', [ExitPermitController::class, 'approve'])->name('approve');
            Route::post('/{permit}/reject', [ExitPermitController::class, 'reject'])->name('reject');
            Route::post('/{permit}/complete', [ExitPermitController::class, 'complete'])->name('complete');
        });

        // Banding Keterlambatan
        Route::prefix('appeals')->name('appeals.')->group(function () {
            Route::get('/', [AppealController::class, 'index'])->name('index');
            Route::post('/{appeal}/decide', [AppealController::class, 'decide'])->name('decide');
        });

        // Kedisiplinan & Poin Siswa
        Route::prefix('discipline')->name('discipline.')->group(function () {
            Route::get('/', [DisciplineController::class, 'index'])->name('index');
            Route::get('/create', [DisciplineController::class, 'create'])->name('create');
            Route::post('/', [DisciplineController::class, 'store'])->name('store');
            Route::delete('/{record}', [DisciplineController::class, 'destroy'])->name('destroy');
        });

        // Surat Peringatan (SP)
        Route::prefix('disciplinary-letters')->name('disciplinary-letters.')->group(function () {
            Route::get('/', [DisciplinaryLetterController::class, 'index'])->name('index');
            Route::post('/', [DisciplinaryLetterController::class, 'store'])->name('store');
            Route::get('/{letter}', [DisciplinaryLetterController::class, 'show'])->name('show');
            Route::delete('/{letter}', [DisciplinaryLetterController::class, 'destroy'])->name('destroy');
        });

        // Rekam Konseling
        Route::prefix('counseling')->name('counseling.')->group(function () {
            Route::get('/', [CounselingController::class, 'index'])->name('index');
            Route::post('/', [CounselingController::class, 'store'])->name('store');
        });

        // Rekap Siswa
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/', [CounselorStudentController::class, 'index'])->name('index');
            Route::get('/{student}', [CounselorStudentController::class, 'show'])->name('show');
        });
    });
});
