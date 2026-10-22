<?php

namespace App\Filament\App\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('user.name')->label('Pelaku')->placeholder('Sistem')->searchable(),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'order.void' => 'Pembatalan',
                        'discount.approved' => 'Diskon',
                        'inventory.adjust' => 'Stok',
                        'settings.updated' => 'Pengaturan',
                        'impersonation.start' => 'Masuk sebagai',
                        'impersonation.stop' => 'Kembali',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'order.void' => 'danger',
                        'discount.approved' => 'warning',
                        'inventory.adjust' => 'info',
                        'settings.updated', 'impersonation.start', 'impersonation.stop' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('description')->label('Keterangan')->wrap()->searchable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'order.void' => 'Pembatalan',
                        'discount.approved' => 'Diskon',
                        'inventory.adjust' => 'Stok',
                        'settings.updated' => 'Pengaturan',
                        'impersonation.start' => 'Masuk sebagai',
                        'impersonation.stop' => 'Kembali',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
