<?php

namespace App\Filament\App\Resources\StockItems;

use App\Filament\App\Resources\StockItems\Pages\EditStockItem;
use App\Filament\App\Resources\StockItems\Pages\ListStockItems;
use App\Filament\App\Resources\StockItems\RelationManagers\MovementsRelationManager;
use App\Filament\App\Resources\StockItems\Schemas\StockItemForm;
use App\Filament\App\Resources\StockItems\Tables\StockItemsTable;
use App\Models\StockItem;
use App\Support\Access\ChecksResourcePermissions;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockItemResource extends Resource
{
    use ChecksResourcePermissions;

    protected static ?string $model = StockItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventaris';

    protected static ?string $navigationLabel = 'Stok';

    protected static ?string $modelLabel = 'stok';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return StockItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockItems::route('/'),
            'edit' => EditStockItem::route('/{record}/edit'),
        ];
    }
}
