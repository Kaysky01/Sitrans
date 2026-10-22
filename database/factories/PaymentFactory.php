<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Tenant;
use App\Modules\Pos\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'order_id' => Order::factory(),
            'payment_method_id' => null,
            'method_name' => 'Tunai',
            'method_type' => PaymentType::Cash,
            'amount' => 0,
            'reference' => null,
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (): array => [
            'order_id' => $order->getKey(),
            'tenant_id' => $order->tenant_id,
        ]);
    }
}
