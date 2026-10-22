<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Modules\Tenancy\Actions\SetupTenantRolesAction;
use App\Support\Tenant\TenantManager;
use Illuminate\Console\Command;

class SyncTenantPermissionsCommand extends Command
{
    protected $signature = 'sitrans:sync-permissions';

    protected $description = 'Backfill global permissions and role assignments for every existing tenant';

    public function handle(SetupTenantRolesAction $setup): int
    {
        return app(TenantManager::class)->bypass(function () use ($setup): int {
            $tenants = Tenant::query()->orderBy('id')->get();
            $bar = $this->output->createProgressBar($tenants->count());

            foreach ($tenants as $tenant) {
                $setup->execute($tenant);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info($tenants->count().' tenant disinkronkan.');

            return self::SUCCESS;
        });
    }
}
