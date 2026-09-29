<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\GradesController;
use App\Http\Controllers\Admin\GuidanceController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\StudentsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeachersController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\GradeNoteController;
use App\Http\Controllers\Teacher\JournalController;
use App\Http\Controllers\Teacher\ProfileController;
use App\Http\Controllers\Teacher\RubricController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Pengelolaan Nilai SMK
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC & SCHOOL CMS ROUTES
// ==========================================
Route::name('public.')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/profil', function () {
        return 'Profil Sekolah';
    })->name('profile');

    Route::get('/jurusan', function () {
        return 'Kompetensi Keahlian';
    })->name('departments.index');

    Route::get('/jurusan/{department:code}', function () {
        return 'Detail Jurusan';
    })->name('departments.show');

    Route::get('/berita', function () {
        return 'Berita & Artikel Sekolah';
    })->name('articles.index');

    Route::get('/berita/{article:slug}', function () {
        return 'Detail Artikel';
    })->name('articles.show');

    Route::get('/prestasi', function () {
        return 'Prestasi Sekolah & Siswa';
    })->name('achievements.index');

    Route::get('/alumni', function () {
        return 'Kisah Alumni & Lulusan';
    })->name('alumni.index');

    Route::get('/ppdb', function () {
        return 'Informasi PPDB';
    })->name('ppdb.index');

    Route::get('/produk-siswa', function () {
        return 'Katalog Produk Siswa';
    })->name('products.index');

    Route::get('/karir', function () {
        return 'BKK & Lowongan PKL / Kerja';
    })->name('career.index');
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
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Master Data Akademik
        Route::prefix('academic')->name('academic.')->group(function () {
            // Tahun Ajaran
            Route::get('/years', [AcademicYearController::class, 'index'])->name('years.index');
            Route::post('/years', [AcademicYearController::class, 'store'])->name('years.store');
            Route::put('/years/{year}', [AcademicYearController::class, 'update'])->name('years.update');
            Route::post('/years/{year}/toggle-active', [AcademicYearController::class, 'toggleActive'])->name('years.toggle-active');
            Route::delete('/years/{year}', [AcademicYearController::class, 'destroy'])->name('years.destroy');

            // Semester
            Route::get('/semesters', [SemesterController::class, 'index'])->name('semesters.index');
            Route::post('/semesters', [SemesterController::class, 'store'])->name('semesters.store');
            Route::put('/semesters/{semester}', [SemesterController::class, 'update'])->name('semesters.update');
            Route::post('/semesters/{semester}/toggle-active', [SemesterController::class, 'toggleActive'])->name('semesters.toggle-active');

            // Jurusan & Kompetensi
            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
            Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
            Route::post('/departments/{department}/competencies', [DepartmentController::class, 'storeCompetency'])->name('departments.competencies.store');
            Route::delete('/departments/competencies/{competency}', [DepartmentController::class, 'destroyCompetency'])->name('departments.competencies.destroy');

            // Kelas / Rombel
            Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
            Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
            Route::get('/classes/{class}', [ClassController::class, 'show'])->name('classes.show');
            Route::put('/classes/{class}', [ClassController::class, 'update'])->name('classes.update');
            Route::delete('/classes/{class}', [ClassController::class, 'destroy'])->name('classes.destroy');

            // Mata Pelajaran
            Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
            Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

            // Penugasan Guru Mengajar
            Route::get('/teaching-assignments', [TeachingAssignmentController::class, 'index'])->name('teaching-assignments.index');
            Route::post('/teaching-assignments', [TeachingAssignmentController::class, 'store'])->name('teaching-assignments.store');
            Route::put('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'update'])->name('teaching-assignments.update');
            Route::delete('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'destroy'])->name('teaching-assignments.destroy');

            // Jadwal Mengajar & Jam Pelajaran
            Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
            Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
            Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
            Route::post('/schedules/periods', [ScheduleController::class, 'storePeriod'])->name('schedules.periods.store');

            // Siswa (Rute Akademik Siswa)
            Route::get('/students', [StudentsController::class, 'index'])->name('students.index');
            Route::post('/students', [StudentsController::class, 'store'])->name('students.store');
            Route::get('/students/{student}', [StudentsController::class, 'show'])->name('students.show');
            Route::put('/students/{student}', [StudentsController::class, 'update'])->name('students.update');
            Route::delete('/students/{student}', [StudentsController::class, 'destroy'])->name('students.destroy');
            Route::post('/students/{student}/enroll', [StudentsController::class, 'enrollClass'])->name('students.enroll');
        });

        // Manajemen Pengguna & Guru
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('index');
            Route::post('/', [UsersController::class, 'store'])->name('store');
            Route::put('/{user}', [UsersController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [UsersController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/reset-password', [UsersController::class, 'resetPassword'])->name('reset-password');
            Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');

            // Guru
            Route::get('/teachers', [TeachersController::class, 'index'])->name('teachers.index');
            Route::post('/teachers', [TeachersController::class, 'store'])->name('teachers.store');
            Route::get('/teachers/{teacher}', [TeachersController::class, 'show'])->name('teachers.show');
            Route::put('/teachers/{teacher}', [TeachersController::class, 'update'])->name('teachers.update');
            Route::delete('/teachers/{teacher}', [TeachersController::class, 'destroy'])->name('teachers.destroy');
        });

        // Absensi Siswa
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        // Monitoring Buku Nilai
        Route::get('/grades', [GradesController::class, 'index'])->name('grades.index');
        Route::get('/grades/{gradebook}', [GradesController::class, 'show'])->name('grades.show');

        // Layanan BK & Kedisiplinan
        Route::get('/guidance', [GuidanceController::class, 'index'])->name('guidance.index');

        // CMS Management
        Route::prefix('cms')->name('cms.')->group(function () {
            Route::get('/profile', [CmsController::class, 'profile'])->name('profile');
            Route::post('/profile', [CmsController::class, 'updateProfile'])->name('profile.update');
            Route::get('/articles', [CmsController::class, 'articles'])->name('articles');
            Route::get('/ppdb', [CmsController::class, 'ppdb'])->name('ppdb');
            Route::post('/ppdb/periods', [CmsController::class, 'storeAdmissionPeriod'])->name('ppdb.periods.store');
            Route::get('/achievements', [CmsController::class, 'achievements'])->name('achievements');
            Route::post('/achievements', [CmsController::class, 'storeAchievement'])->name('achievements.store');
            Route::get('/products', [CmsController::class, 'products'])->name('products');
            Route::post('/products', [CmsController::class, 'storeProduct'])->name('products.store');
            Route::get('/career', [CmsController::class, 'career'])->name('career');
            Route::post('/career', [CmsController::class, 'storeCareer'])->name('career.store');
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
            Route::get('/{gradebook}', [GradebookController::class, 'show'])->name('show');
            Route::post('/{gradebook}/columns', [GradebookController::class, 'storeColumn'])->name('columns.store');
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
        Route::get('/dashboard', function () {
            return 'Student Dashboard';
        })->name('dashboard');

        Route::get('/jadwal', function () {
            return 'Jadwal Pelajaran Siswa';
        })->name('schedules.index');

        Route::get('/nilai', function () {
            return 'Rekap Nilai Siswa';
        })->name('grades.index');

        // Tugas & Pengumpulan
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', function () {
                return 'Daftar Tugas Siswa';
            })->name('index');

            Route::get('/{assessment}', function () {
                return 'Detail Tugas';
            })->name('show');

            Route::post('/{assessment}/submit', function () {
                return 'Kirim Jawaban Tugas';
            })->name('submit');
        });

        // Izin Keluar Sekolah
        Route::prefix('exit-permits')->name('exit-permits.')->group(function () {
            Route::get('/', function () {
                return 'Riwayat Izin Keluar Siswa';
            })->name('index');

            Route::get('/create', function () {
                return 'Form Pengajuan Izin Keluar';
            })->name('create');

            Route::get('/{permit}', function () {
                return 'Detail & Timer Izin Keluar';
            })->name('show');

            Route::post('/{permit}/appeal', function () {
                return 'Ajukan Banding Keterlambatan';
            })->name('appeal');
        });

        // Disiplin Siswa
        Route::get('/disiplin', function () {
            return 'Informasi Poin Disiplin Siswa';
        })->name('discipline.index');
    });

    // ==========================================
    // 6. COUNSELOR / BK ROUTES (role:counselor)
    // ==========================================
    Route::middleware('role:counselor')->prefix('bk')->name('counselor.')->group(function () {
        Route::get('/dashboard', function () {
            return 'Counselor BK Dashboard';
        })->name('dashboard');

        // Manajemen Izin Keluar & Timer
        Route::prefix('exit-permits')->name('exit-permits.')->group(function () {
            Route::get('/', function () {
                return 'Monitoring Izin Keluar & Siswa di Luar';
            })->name('index');

            Route::get('/{permit}', function () {
                return 'Detail Izin Keluar Siswa';
            })->name('show');

            Route::post('/{permit}/approve', function () {
                return 'Setujui Izin Keluar';
            })->name('approve');

            Route::post('/{permit}/reject', function () {
                return 'Tolak Izin Keluar';
            })->name('reject');

            Route::post('/{permit}/complete', function () {
                return 'Siswa Kembali ke Sekolah';
            })->name('complete');

            Route::post('/appeals/{appeal}/decide', function () {
                return 'Proses Keputusan Banding';
            })->name('appeals.decide');
        });

        // Kedisiplinan & Poin Siswa
        Route::prefix('discipline')->name('discipline.')->group(function () {
            Route::get('/', function () {
                return 'Rekap Poin & Pelanggaran Siswa';
            })->name('index');

            Route::get('/records/create', function () {
                return 'Catat Pelanggaran / Prestasi Perilaku';
            })->name('records.create');

            Route::post('/records', function () {
                return 'Simpan Catatan Disiplin';
            })->name('records.store');

            Route::get('/letters', function () {
                return 'Daftar Surat Peringatan (SP)';
            })->name('letters.index');

            Route::post('/letters', function () {
                return 'Terbitkan Surat Peringatan (SP)';
            })->name('letters.store');
        });
    });
});
