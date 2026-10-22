<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Tenant;
use App\Modules\Tenancy\Actions\RegisterTenantAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $result = app(RegisterTenantAction::class)->execute([
            'cafe_name' => $data['name'],
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['owner_password'],
        ]);

        $tenant = $result['tenant'];

        $tenant->update([
            'status' => $data['status'] ?? Tenant::STATUS_TRIAL,
            'trial_ends_at' => $data['trial_ends_at'] ?? $tenant->trial_ends_at,
        ]);

        return $tenant;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
