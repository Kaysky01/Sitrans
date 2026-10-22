<?php

namespace App\Filament\Admin\Resources\Tenants\Tables;

use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Kafe')->searchable()->weight('semibold'),
                TextColumn::make('slug')->label('Slug')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Tenant::STATUS_TRIAL => 'Uji coba',
                        Tenant::STATUS_ACTIVE => 'Aktif',
                        Tenant::STATUS_PAST_DUE => 'Menunggu',
                        Tenant::STATUS_SUSPENDED => 'Ditangguhkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Tenant::STATUS_ACTIVE => 'success',
                        Tenant::STATUS_TRIAL => 'warning',
                        Tenant::STATUS_PAST_DUE => 'info',
                        Tenant::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('trial_ends_at')->label('Akhir uji coba')->dateTime('d M Y')->placeholder('—')->sortable(),
                TextColumn::make('users_count')->label('Pengguna')->counts('users')->alignEnd(),
                TextColumn::make('outlets_count')->label('Outlet')->counts('outlets')->alignEnd(),
                TextColumn::make('created_at')->label('Terdaftar')->dateTime('d M Y')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Tenant::STATUS_TRIAL => 'Uji coba',
                        Tenant::STATUS_ACTIVE => 'Aktif',
                        Tenant::STATUS_PAST_DUE => 'Menunggu pembayaran',
                        Tenant::STATUS_SUSPENDED => 'Ditangguhkan',
                    ]),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Tenant $record): bool => $record->status !== Tenant::STATUS_ACTIVE)
                    ->requiresConfirmation()
                    ->action(function (Tenant $record): void {
                        $record->update(['status' => Tenant::STATUS_ACTIVE]);

                        Notification::make()->title($record->name.' diaktifkan')->success()->send();
                    }),
                Action::make('suspend')
                    ->label('Tangguhkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Tenant $record): bool => $record->status !== Tenant::STATUS_SUSPENDED)
                    ->requiresConfirmation()
                    ->action(function (Tenant $record): void {
                        $record->update(['status' => Tenant::STATUS_SUSPENDED]);

                        Notification::make()->title($record->name.' ditangguhkan')->warning()->send();
                    }),
                Action::make('extend')
                    ->label('Perpanjang')
                    ->icon('heroicon-o-calendar-days')
                    ->action(function (Tenant $record): void {
                        $start = $record->trial_ends_at !== null && $record->trial_ends_at->isFuture()
                            ? $record->trial_ends_at
                            : now();

                        $record->update([
                            'status' => Tenant::STATUS_TRIAL,
                            'trial_ends_at' => $start->addDays(config('sitrans.trial_days')),
                        ]);

                        Notification::make()
                            ->title('Uji coba '.$record->name.' diperpanjang')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
