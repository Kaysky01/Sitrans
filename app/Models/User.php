<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Tenant\InteractsWithTenants;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'pin', 'avatar_path', 'is_super_admin', 'last_active_tenant_id'])]
#[Hidden(['password', 'pin', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, InteractsWithTenants, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function lastActiveTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'last_active_tenant_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_super_admin) {
            return $panel->getId() === 'admin';
        }

        return $panel->getId() === 'app' && $this->tenants()->wherePivot('is_active', true)->exists();
    }
}
