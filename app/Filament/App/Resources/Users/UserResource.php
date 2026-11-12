<?php

namespace App\Filament\App\Resources\Users;

use App\Filament\App\Resources\Users\Pages\CreateUser;
use App\Filament\App\Resources\Users\Pages\EditUser;
use App\Filament\App\Resources\Users\Pages\ListUsers;
use App\Filament\App\Resources\Users\Schemas\UserForm;
use App\Filament\App\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Support\Access\ChecksResourcePermissions;
use App\Support\Access\RoleDefinitions;
use App\Support\Tenant\TenantManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    use ChecksResourcePermissions;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Staf';

    protected static ?string $modelLabel = 'staf';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = app(TenantManager::class)->current();

        return parent::getEloquentQuery()
            ->whereHas('tenants', fn (Builder $query) => $query->whereKey($tenant?->getKey()));
    }

    /**
     * Akun pemilik dikelola dari halaman Profil, bukan dari daftar staf.
     */
    public static function canEdit(Model $record): bool
    {
        return ! static::isTenantOwner($record) && static::allowsResourcePermission('manage');
    }

    public static function canDelete(Model $record): bool
    {
        return ! static::isTenantOwner($record) && static::allowsResourcePermission('manage');
    }

    protected static function isTenantOwner(Model $record): bool
    {
        $tenant = app(TenantManager::class)->current();

        return $record instanceof User
            && $tenant !== null
            && $record->roleIn($tenant) === RoleDefinitions::OWNER;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
