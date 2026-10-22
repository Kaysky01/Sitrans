<?php

namespace App\Filament\App\Resources\Bundles;

use App\Filament\App\Resources\Bundles\Pages\CreateBundle;
use App\Filament\App\Resources\Bundles\Pages\EditBundle;
use App\Filament\App\Resources\Bundles\Pages\ListBundles;
use App\Filament\App\Resources\Bundles\RelationManagers\ItemsRelationManager;
use App\Filament\App\Resources\Bundles\Schemas\BundleForm;
use App\Filament\App\Resources\Bundles\Tables\BundlesTable;
use App\Models\Bundle;
use App\Support\Access\ChecksResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BundleResource extends Resource
{
    use ChecksResourcePermissions;

    protected static ?string $model = Bundle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu';

    protected static ?string $navigationLabel = 'Paket';

    protected static ?string $modelLabel = 'paket';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return BundleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BundlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBundles::route('/'),
            'create' => CreateBundle::route('/create'),
            'edit' => EditBundle::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
