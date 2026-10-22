<?php

namespace Database\Factories;

use App\Models\TaxSetting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxSetting>
 */
class TaxSettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'outlet_id' => null,
            'pb1_enabled' => true,
            'pb1_rate' => 10,
            'service_enabled' => false,
            'service_rate' => 5,
            'prices_include_tax' => false,
        ];
    }
}
