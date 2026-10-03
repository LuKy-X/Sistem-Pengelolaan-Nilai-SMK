<?php

namespace App\Http\Requests\Student;

use App\Models\ExitPermit;
use App\Models\LessonPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExitPermitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExitPermit::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason_id' => [
                'required',
                Rule::exists('exit_permit_reasons', 'id')->where('is_active', true),
            ],
            'reason_detail' => ['required', 'string', 'min:10', 'max:1000'],
            'exit_period_id' => [
                'required',
                Rule::exists('lesson_periods', 'id')->where('is_break', false),
            ],
            'return_period_id' => [
                'required',
                Rule::exists('lesson_periods', 'id')->where('is_break', false),
            ],
        ];
    }

    /**
     * Jam kembali harus berada setelah jam keluar. Validasi ini memakai urutan
     * jam pelajaran, bukan perbandingan jam mentah, sehingga jam istirahat di
     * tengah hari tidak membuat hasil terasa tidak logis.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $exitId = $this->input('exit_period_id');
                $returnId = $this->input('return_period_id');

                if (! $exitId || ! $returnId) {
                    return;
                }

                $exitPeriod = LessonPeriod::find($exitId);
                $returnPeriod = LessonPeriod::find($returnId);

                if ($exitPeriod === null || $returnPeriod === null) {
                    return;
                }

                if ($returnPeriod->start_time === $exitPeriod->start_time) {
                    $validator->errors()->add(
                        'return_period_id',
                        'Jam kembali harus setelah jam keluar, bukan jam yang sama.'
                    );

                    return;
                }

                if ($returnPeriod->start_time < $exitPeriod->start_time) {
                    $validator->errors()->add(
                        'return_period_id',
                        'Jam kembali ('.$returnPeriod->displayLabel()
                        .') harus lebih akhir daripada jam keluar ('
                        .$exitPeriod->displayLabel().').'
                    );

                    return;
                }

                // Jam kembali yang sudah lewat tidak berguna: begitu izin
                // disetujui, siswa akan langsung tercatat terlambat.
                if (now()->setTimeFromTimeString($returnPeriod->start_time)->isPast()) {
                    $validator->errors()->add(
                        'return_period_id',
                        'Jam '.$returnPeriod->displayLabel()
                        .' sudah lewat hari ini. Pilih jam pelajaran yang belum dimulai.'
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason_id.required' => 'Pilih alasan izin.',
            'reason_id.exists' => 'Alasan izin yang dipilih tidak tersedia.',
            'reason_detail.required' => 'Tuliskan detail keperluan.',
            'reason_detail.min' => 'Detail keperluan minimal 10 karakter.',
            'reason_detail.max' => 'Detail keperluan maksimal 1000 karakter.',
            'exit_period_id.required' => 'Pilih jam pelajaran saat Anda keluar.',
            'exit_period_id.exists' => 'Jam pelajaran keluar tidak tersedia.',
            'return_period_id.required' => 'Pilih jam pelajaran paling akhir Anda harus sudah kembali.',
            'return_period_id.exists' => 'Jam pelajaran kembali tidak tersedia.',
        ];
    }
}
