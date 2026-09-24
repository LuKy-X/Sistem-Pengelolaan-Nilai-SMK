<?php

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
