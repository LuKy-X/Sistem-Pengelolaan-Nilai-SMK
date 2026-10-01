<?php

namespace App\Http\Requests\BK;

use App\Enums\DisciplineCategoryType;
use App\Http\Requests\BK\Concerns\ValidatesCounselorStudent;
use App\Models\DisciplineCategory;
use App\Models\DisciplineRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreDisciplineRecordRequest extends FormRequest
{
    use ValidatesCounselorStudent;

    /**
     * Sumber catatan yang tersedia untuk pencatatan oleh Guru BK.
     *
     * @var list<string>
     */
    public const SOURCES = [
        'MANUAL',
        'HOMEROOM',
        'CLASS_JOURNAL',
        'EXIT_PERMIT',
        'APPEAL',
        'COUNSELING',
        'INTERVIEW',
        'HOME_VISIT',
        'PARENT_MEETING',
    ];

    public function authorize(): bool
    {
        return Gate::allows('create', DisciplineRecord::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', $this->counselorStudentRule()],
            'category_id' => ['required', 'integer', Rule::exists(DisciplineCategory::class, 'id')->where('is_active', true)],
            'points_delta' => ['required', 'integer', 'not_in:0', 'min:-1000', 'max:1000'],
            'occurred_at' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'source_type' => ['nullable', Rule::in(self::SOURCES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Pilih siswa yang akan dicatat.',
            'student_id.exists' => 'Siswa yang dipilih tidak ditemukan.',
            'category_id.required' => 'Pilih jenis pelanggaran atau penghargaan.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan atau tidak aktif.',
            'points_delta.required' => 'Delta poin wajib diisi.',
            'points_delta.not_in' => 'Delta poin tidak boleh nol.',
            'occurred_at.before_or_equal' => 'Tanggal kejadian tidak boleh melewati hari ini.',
            'description.required' => 'Uraian kejadian wajib diisi.',
            'description.min' => 'Uraian kejadian minimal 5 karakter.',
            'source_type.in' => 'Sumber catatan tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('source_type')) {
            $this->merge(['source_type' => 'MANUAL']);
        }

        $this->normalizePointsDelta();
    }

    /**
     * Normalisasi arah delta poin agar konsisten dengan tipe kategori.
     */
    protected function normalizePointsDelta(): void
    {
        $delta = (int) $this->input('points_delta');

        if ($delta === 0) {
            return;
        }

        $category = DisciplineCategory::find($this->input('category_id'));

        if (! $category instanceof DisciplineCategory) {
            return;
        }

        $normalized = $category->type === DisciplineCategoryType::Reward
            ? abs($delta)
            : -abs($delta);

        $this->merge(['points_delta' => $normalized]);
    }
}
