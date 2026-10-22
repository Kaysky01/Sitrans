<?php

namespace App\Filament\App\Resources\StockItems\Pages;

use App\Filament\App\Resources\StockItems\StockItemResource;
use Filament\Resources\Pages\EditRecord;

class EditStockItem extends EditRecord
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
