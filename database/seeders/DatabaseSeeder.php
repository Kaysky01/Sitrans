<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use App\Support\Tenant\TenantManager;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin Sitrans',
            'email' => 'admin@sitrans.test',
        ]);

        $result = app(RegisterTenantAction::class)->execute([
            'cafe_name' => 'Kopi Senja',
            'name' => 'Budi Pemilik',
            'email' => 'owner@kopisenja.test',
            'phone' => '081234567890',
            'password' => 'password',
            'outlet_name' => 'Kopi Senja Kemang',
        ]);

        /** @var Tenant $tenant */
        $tenant = $result['tenant'];

        $manager = app(TenantManager::class);
        $manager->set($tenant);

        try {
            $this->call(DemoMenuSeeder::class);
        } finally {
            $manager->forget();
        }
    }
}
