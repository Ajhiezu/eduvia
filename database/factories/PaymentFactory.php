<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'payment_number' => 'PAY/'.date('Ym').'/'.strtoupper(fake()->unique()->lexify('??????')),
            'invoice_id' => Invoice::factory(),
            'student_id' => User::factory(),
            'paid_by' => User::factory(),
            'amount' => 500000,
            'payment_date' => Carbon::today(),
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'reference_number' => 'TRX-'.fake()->numerify('##########'),
            'proof_path' => null,
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => User::factory(),
            'verified_at' => now(),
            'recorded_by' => User::factory(),
        ];
    }
}
