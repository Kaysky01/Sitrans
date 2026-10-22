<?php

namespace App\Filament\App\Resources\StockItems\RelationManagers;

use App\Modules\Inventory\Enums\StockMovementType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Riwayat stok';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (StockMovementType $state): string => $state->label())
                    ->color(fn (StockMovementType $state): string => $state->color()),
                TextColumn::make('quantity')
                    ->label('Perubahan')
                    ->numeric(decimalPlaces: 2)
                    ->formatStateUsing(fn (float $state): string => ($state > 0 ? '+' : '').$state)
                    ->color(fn (float $state): string => $state < 0 ? 'danger' : 'success'),
                TextColumn::make('balance_after')->label('Sisa')->numeric(decimalPlaces: 2),
                TextColumn::make('reference')->label('Referensi')->placeholder('—')->toggleable(),
                TextColumn::make('user.name')->label('Oleh')->placeholder('Sistem')->toggleable(),
                TextColumn::make('note')->label('Catatan')->limit(40)->placeholder('—')->toggleable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
