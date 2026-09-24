<?php

namespace Database\Factories;

use App\Enums\GradebookColumnType;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradebookColumnFactory extends Factory
{
    protected $model = GradebookColumn::class;

    public function definition(): array
    {
        return [
            'gradebook_id' => Gradebook::factory(),
            'category_id' => null,
            'name' => 'Tugas '.fake()->word(),
            'code' => strtoupper(fake()->lexify('TUGAS-??')),
            'column_type' => GradebookColumnType::Score,
            'calculation_type' => null,
            'max_score' => 100.00,
            'weight' => 10.00,
            'sort_order' => 1,
            'is_visible' => true,
            'is_included_in_average' => true,
        ];
    }
}
