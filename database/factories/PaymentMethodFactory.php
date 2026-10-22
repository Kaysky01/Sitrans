<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\Tenant;
use App\Modules\Pos\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(PaymentType::cases());

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $type->label(),
            'code' => Str::slug($type->value).'-'.Str::lower(Str::random(4)),
            'type' => $type,
            'requires_reference' => $type->requiresReference(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Tunai',
            'code' => 'cash',
            'type' => PaymentType::Cash,
            'requires_reference' => false,
        ]);
    }

    public function qris(): static
    {
        return $this->state(fn (): array => [
            'name' => 'QRIS',
            'code' => 'qris',
            'type' => PaymentType::Qris,
            'requires_reference' => true,
        ]);
    }
}
