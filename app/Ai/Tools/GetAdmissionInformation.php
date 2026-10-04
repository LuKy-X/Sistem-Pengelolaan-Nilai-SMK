<?php

namespace App\Ai\Tools;

use App\Models\AdmissionPeriod;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetAdmissionInformation implements Tool
{
    public function description(): Stringable|string
    {
        return 'Mengambil periode PPDB terbaru beserta jadwal, jalur, persyaratan, dan biaya yang dicatat sekolah. Gunakan untuk pertanyaan tentang pendaftaran atau persiapan masuk sekolah.';
    }

    public function handle(Request $request): Stringable|string
    {
        $period = AdmissionPeriod::query()
            ->with([
                'academicYear',
                'scheduleItems',
                'paths' => fn ($query) => $query->where('is_active', true),
                'requirements',
                'feeItems',
            ])
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['OPEN'])
            ->orderByDesc('registration_start')
            ->first();

        if ($period === null) {
            return json_encode([
                'available' => false,
                'message' => 'Belum ada periode PPDB yang dicatat di sistem sekolah.',
            ]);
        }

        return json_encode([
            'available' => true,
            'period' => [
                'title' => $period->title,
                'status' => $period->status,
                'academic_year' => $period->academicYear?->name,
                'registration_start' => $period->registration_start?->toDateString(),
                'registration_end' => $period->registration_end?->toDateString(),
                'description' => $period->description,
                'schedule' => $period->scheduleItems->map(fn ($item): array => [
                    'title' => $item->title,
                    'description' => $item->description,
                ])->values()->all(),
                'paths' => $period->paths->pluck('name')->values()->all(),
                'requirements' => $period->requirements->map(fn ($requirement): array => [
                    'title' => $requirement->title,
                    'description' => $requirement->description,
                ])->values()->all(),
                'fees' => $period->feeItems->map(fn ($fee): array => [
                    'name' => $fee->name,
                    'amount' => (float) $fee->amount,
                    'is_free' => $fee->is_free,
                    'description' => $fee->description,
                ])->values()->all(),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
