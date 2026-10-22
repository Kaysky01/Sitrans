<?php

namespace App\Filament\App\Resources\Orders\Pages;

use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        // Pesanan dibuat di layar kasir (POS); back office hanya membaca.
        return OrderResource::canCreate() ? [CreateAction::make()] : [];
    }
}
