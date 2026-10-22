<?php

namespace App\Filament\App\Resources\ModifierGroups;

use App\Filament\App\Resources\ModifierGroups\Pages\CreateModifierGroup;
use App\Filament\App\Resources\ModifierGroups\Pages\EditModifierGroup;
use App\Filament\App\Resources\ModifierGroups\Pages\ListModifierGroups;
use App\Filament\App\Resources\ModifierGroups\RelationManagers\ModifiersRelationManager;
use App\Filament\App\Resources\ModifierGroups\Schemas\ModifierGroupForm;
use App\Filament\App\Resources\ModifierGroups\Tables\ModifierGroupsTable;
use App\Models\ModifierGroup;
use App\Support\Access\ChecksResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModifierGroupResource extends Resource
{
    use ChecksResourcePermissions;

    protected static ?string $model = ModifierGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?string $navigationLabel = 'Modifier';

    protected static ?string $modelLabel = 'grup modifier';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ModifierGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModifierGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ModifiersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModifierGroups::route('/'),
            'create' => CreateModifierGroup::route('/create'),
            'edit' => EditModifierGroup::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
