<?php

namespace App\Filament\App\Resources\PaymentMethods\Tables;

use App\Filament\App\Resources\PaymentMethods\PaymentMethodResource;
use App\Models\PaymentMethod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('semibold'),
                TextColumn::make('code')->label('Kode')->badge(),
                TextColumn::make('type')->label('Tipe')->badge(),
                IconColumn::make('requires_reference')->label('Ref')->boolean(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('sort_order')->label('Urutan')->numeric(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()
                    ->visible(fn (PaymentMethod $record): bool => PaymentMethodResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (PaymentMethod $record): bool => PaymentMethodResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => PaymentMethodResource::canDeleteAny()),
                ]),
            ]);
    }
}
