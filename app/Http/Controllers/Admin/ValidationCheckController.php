<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidationCheckController extends Controller
{
    /**
     * Memeriksa keunikan data (username, NIS, NISN, NIP, email, kode kelas/mapel) secara realtime.
     */
    public function checkUnique(Request $request): JsonResponse
    {
        $type = (string) $request->input('type');
        $value = trim((string) $request->input('value', ''));
        $ignoreId = $request->input('ignore_id');
        $extra = (array) $request->input('extra', []);

        if ($value === '') {
            return response()->json([
                'available' => true,
                'message' => '',
            ]);
        }

        $exists = false;
        $label = 'Data';

        switch ($type) {
            case 'username':
                $label = 'Username';
                $exists = User::where('username', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'email':
                $label = 'Email';
                $exists = User::where('email', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'nis':
                $label = 'NIS';
                $exists = StudentProfile::where('nis', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'nisn':
                $label = 'NISN';
                $exists = StudentProfile::where('nisn', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'nip':
                $label = 'NIP';
                $exists = TeacherProfile::where('nip', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'subject_code':
                $label = 'Kode Mata Pelajaran';
                $exists = Subject::where('code', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'class_code':
                $label = 'Kode Kelas';
                $academicYearId = $extra['academic_year_id'] ?? null;
                $exists = SchoolClass::where('code', $value)
                    ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            case 'academic_year_code':
                $label = 'Kode Tahun Ajaran';
                $exists = AcademicYear::where('code', $value)
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();
                break;

            default:
                return response()->json([
                    'available' => false,
                    'message' => 'Tipe validasi tidak didukung.',
                ], 422);
        }

        if ($exists) {
            return response()->json([
                'available' => false,
                'message' => "{$label} '{$value}' sudah terdaftar / digunakan.",
                'type' => $type,
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => "{$label} '{$value}' tersedia.",
            'type' => $type,
        ]);
    }
}
