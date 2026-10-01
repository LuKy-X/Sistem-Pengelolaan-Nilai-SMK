<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
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
use App\Http\Controllers\BK\AppealController;
use App\Http\Controllers\BK\CounselingController;
use App\Http\Controllers\BK\DashboardController as CounselorDashboardController;
use App\Http\Controllers\BK\DisciplinaryLetterController;
use App\Http\Controllers\BK\DisciplineController;
use App\Http\Controllers\BK\ExitPermitController;
use App\Http\Controllers\BK\StudentController as CounselorStudentController;
use App\Http\Controllers\BK\AppealController;
use App\Http\Controllers\BK\CounselingController;
use App\Http\Controllers\BK\DashboardController as CounselorDashboardController;
use App\Http\Controllers\BK\DisciplinaryLetterController;
use App\Http\Controllers\BK\DisciplineController;
use App\Http\Controllers\BK\ExitPermitController;
use App\Http\Controllers\BK\StudentController as CounselorStudentController;
use App\Http\Controllers\Public\AchievementController;
use App\Http\Controllers\Public\AdmissionController;
use App\Http\Controllers\Public\AlumniController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CareerController;
use App\Http\Controllers\Public\ChatbotController;
use App\Http\Controllers\Public\DepartmentController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SchoolProfileController;
use App\Http\Controllers\Public\StudentProductController;
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
// The root path is never a page of its own: it sends guests to the login form
// and authenticated users to the dashboard that matches their role. Keeping the
// dispatcher at "/" is also what the "guest" middleware redirects to, so an
// authenticated user who opens /login is forwarded here instead of being shown
// the welcome page.
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

    // ==========================================
    // 1b. PUBLIC CHATBOT (JSON, read-only)
    // ==========================================
    // `throttle` keeps a single visitor from hammering the CMS queries behind the
    // assistant. The endpoint stays outside the `auth` groups on purpose so both
    // guests and signed-in staff browsing the public site get the same answers.
    Route::prefix('tanya-ai')->middleware('throttle:30,1')->group(function () {
        Route::get('/', [ChatbotController::class, 'opening'])->name('chatbot.opening');
        Route::post('/', [ChatbotController::class, 'reply'])->name('chatbot.reply');
    });
});

// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    // GET is supported so visiting /logout directly in the browser also clears
    // the session; the navbar/profile dropdown still submits a POST form.
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

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
            Route::delete('/semesters/{semester}', [SemesterController::class, 'destroy'])->name('semesters.destroy');

            // Jurusan & Kompetensi
            Route::get('/departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
            Route::post('/departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
            Route::get('/departments/{department}', [AdminDepartmentController::class, 'show'])->name('departments.show');
            Route::put('/departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
            Route::delete('/departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');
            Route::post('/departments/{department}/competencies', [AdminDepartmentController::class, 'storeCompetency'])->name('departments.competencies.store');
            Route::delete('/departments/competencies/{competency}', [AdminDepartmentController::class, 'destroyCompetency'])->name('departments.competencies.destroy');

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
            Route::put('/schedules/{schedule}', [ScheduleController::class, 'update'])->name('schedules.update');
            Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
            Route::post('/schedules/periods', [ScheduleController::class, 'storePeriod'])->name('schedules.periods.store');
            Route::put('/schedules/periods/{period}', [ScheduleController::class, 'updatePeriod'])->name('schedules.periods.update');
            Route::delete('/schedules/periods/{period}', [ScheduleController::class, 'destroyPeriod'])->name('schedules.periods.destroy');

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

            // Siswa & Staff (Rute kompatibilitas skeleton)
            Route::get('/students', fn () => redirect()->route('admin.academic.students.index'))->name('students.index');
            Route::get('/staff', [UsersController::class, 'index'])->name('staff.index');
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
            // Profil Sekolah
            Route::get('/profile', [CmsController::class, 'profile'])->name('profile');
            Route::get('/school-profile', [CmsController::class, 'profile'])->name('school-profile.edit');
            Route::post('/profile', [CmsController::class, 'updateProfile'])->name('profile.update');

            // Berita & Artikel
            Route::get('/articles', [CmsController::class, 'articles'])->name('articles');
            Route::get('/articles/index', [CmsController::class, 'articles'])->name('articles.index');

            // PPDB
            Route::get('/ppdb', [CmsController::class, 'ppdb'])->name('ppdb');
            Route::get('/ppdb/index', [CmsController::class, 'ppdb'])->name('ppdb.index');
            Route::post('/ppdb/periods', [CmsController::class, 'storeAdmissionPeriod'])->name('ppdb.periods.store');

            // Prestasi
            Route::get('/achievements', [CmsController::class, 'achievements'])->name('achievements');
            Route::get('/achievements/index', [CmsController::class, 'achievements'])->name('achievements.index');
            Route::post('/achievements', [CmsController::class, 'storeAchievement'])->name('achievements.store');

            // Alumni
            Route::get('/alumni', [CmsController::class, 'career'])->name('alumni.index');

            // Produk Siswa
            Route::get('/products', [CmsController::class, 'products'])->name('products');
            Route::get('/products/index', [CmsController::class, 'products'])->name('products.index');
            Route::post('/products', [CmsController::class, 'storeProduct'])->name('products.store');

            // BKK & Mitra Perusahaan / Karir
            Route::get('/career', [CmsController::class, 'career'])->name('career');
            Route::get('/career/index', [CmsController::class, 'career'])->name('career.index');
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
