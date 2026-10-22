<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenant\TenantManager;
use App\Support\Tenant\TenantNotResolvedException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\TenantRecord;

beforeEach(function () {
    Schema::create('tenant_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->index();
        $table->string('name');
        $table->timestamps();
    });

    Route::middleware('tenant')->get('/_test/tenant', fn () => response()->json([
        'tenant' => app(TenantManager::class)->id(),
    ]));

    Route::middleware('tenant')->get('/_test/records', fn () => response()->json([
        'count' => TenantRecord::count(),
    ]));
});

afterEach(function () {
    Schema::dropIfExists('tenant_records');
});

describe('scoping', function () {
    it('auto-fills tenant_id from the active tenant', function () {
        $tenant = Tenant::factory()->create();

        app(TenantManager::class)->set($tenant);

        $record = TenantRecord::create(['name' => 'Kopi Susu']);

        expect($record->tenant_id)->toBe($tenant->id);
    });

    it('only returns records belonging to the active tenant', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        TenantRecord::create(['tenant_id' => $tenantA->id, 'name' => 'Punyaku']);
        TenantRecord::create(['tenant_id' => $tenantB->id, 'name' => 'Punyanya']);

        app(TenantManager::class)->set($tenantA);

        expect(TenantRecord::pluck('name')->all())->toBe(['Punyaku']);
    });

    it('cannot find, update, or delete another tenant\'s record', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $record = TenantRecord::create(['tenant_id' => $tenantB->id, 'name' => 'Rahasia']);

        app(TenantManager::class)->set($tenantA);

        expect(TenantRecord::find($record->id))->toBeNull()
            ->and(TenantRecord::whereKey($record->id)->exists())->toBeFalse()
            ->and(TenantRecord::whereKey($record->id)->update(['name' => 'Diretas']))->toBe(0)
            ->and(TenantRecord::whereKey($record->id)->delete())->toBe(0);

        $untouched = app(TenantManager::class)->bypass(
            fn () => TenantRecord::withoutTenantScope()->find($record->id)
        );

        expect($untouched->name)->toBe('Rahasia');
    });

    it('fails loudly when queried without a tenant context', function () {
        TenantRecord::create(['tenant_id' => Tenant::factory()->create()->id, 'name' => 'A']);

        expect(fn () => TenantRecord::count())->toThrow(TenantNotResolvedException::class);
    });

    it('refuses to create a record without a tenant context', function () {
        expect(fn () => TenantRecord::create(['name' => 'Yatim']))
            ->toThrow(TenantNotResolvedException::class);
    });
});

describe('explicit escaping', function () {
    it('sees every tenant only when bypassed', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        TenantRecord::create(['tenant_id' => $tenantA->id, 'name' => 'A']);
        TenantRecord::create(['tenant_id' => $tenantB->id, 'name' => 'B']);

        $total = app(TenantManager::class)->bypass(fn () => TenantRecord::count());

        expect($total)->toBe(2);
    });

    it('can target a single tenant without activating it', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        TenantRecord::create(['tenant_id' => $tenantA->id, 'name' => 'A']);
        TenantRecord::create(['tenant_id' => $tenantB->id, 'name' => 'B']);

        expect(TenantRecord::withoutTenantScope($tenantB->id)->pluck('name')->all())->toBe(['B']);
    });
});

describe('membership', function () {
    it('knows which tenants a user belongs to and their role', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $user = User::factory()->create();
        $user->tenants()->attach($tenantA, ['role' => 'manager', 'is_active' => true]);
        $user->tenants()->attach($tenantB, ['role' => 'cashier', 'is_active' => false]);

        expect($user->belongsToTenant($tenantA))->toBeTrue()
            ->and($user->belongsToTenant($tenantB))->toBeFalse()
            ->and($user->roleIn($tenantA))->toBe('manager');
    });
});

describe('resolver middleware', function () {
    it('resolves the active tenant for a member', function () {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->memberOf($tenant, 'owner')->create();

        $this->actingAs($user)
            ->getJson('/_test/tenant')
            ->assertOk()
            ->assertJson(['tenant' => $tenant->id]);
    });

    it('denies a user with no membership', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/_test/tenant')->assertForbidden();
    });

    it('lets super admins through without a tenant context', function () {
        $user = User::factory()->superAdmin()->create();

        $this->actingAs($user)
            ->getJson('/_test/tenant')
            ->assertOk()
            ->assertJson(['tenant' => null]);
    });

    it('isolates data end to end over HTTP', function () {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        TenantRecord::create(['tenant_id' => $tenantA->id, 'name' => 'A1']);
        TenantRecord::create(['tenant_id' => $tenantA->id, 'name' => 'A2']);
        TenantRecord::create(['tenant_id' => $tenantB->id, 'name' => 'B1']);

        $userA = User::factory()->memberOf($tenantA)->create();

        $this->actingAs($userA)
            ->getJson('/_test/records')
            ->assertOk()
            ->assertJson(['count' => 2]);
    });
});
