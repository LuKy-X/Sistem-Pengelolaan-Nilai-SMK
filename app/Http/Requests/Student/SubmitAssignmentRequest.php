<?php

namespace App\Http\Requests\Student;

use App\Models\AssessmentSubmission;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $assessment = $this->route('assessment');

        return $assessment !== null
            && $this->user()?->can('create', [AssessmentSubmission::class, $assessment]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:10000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:10240', 'required_without:content', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required_without' => 'Isi jawaban teks atau unggah file lampiran.',
            'attachment.required_without' => 'Unggah file lampiran atau isi jawaban teks.',
            'attachment.max' => 'Ukuran file maksimal 10 MB.',
            'attachment.mimes' => 'Format file harus PDF, Office, gambar, atau ZIP.',
        ];
    }
}
