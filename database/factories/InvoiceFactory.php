<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = 500000;
        $discount = 0;
        $total = $subtotal - $discount;

        return [
            'school_id' => School::factory(),
            'invoice_number' => 'INV/'.date('Ym').'/'.strtoupper(fake()->unique()->lexify('??????')),
            'student_id' => User::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'fee_category_id' => FeeCategory::factory(),
            'billing_period_key' => fake()->year().'-'.fake()->month(),
            'title' => 'Tagihan SPP '.fake()->monthName(),
            'issue_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays(14),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'paid_amount' => 0,
            'status' => Invoice::STATUS_ISSUED,
            'notes' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
