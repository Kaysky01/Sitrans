<?php

namespace App\Filament\App\Resources\PaymentMethods\Tables;

use App\Filament\App\Resources\PaymentMethods\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Support\Catalog\PaymentMethodDeletionGuard;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('name'))
            ->reorderable(
                'sort_order',
                condition: fn (): bool => PaymentMethodResource::canCreate(),
            )
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->label($isReordering ? 'Selesai Mengurutkan' : 'Urutkan Metode')
                    ->tooltip('Aktifkan lalu seret baris untuk mengatur urutan metode bayar di POS.'),
            )
            ->recordActions([
                EditAction::make()
                    ->visible(fn (PaymentMethod $record): bool => PaymentMethodResource::canEdit($record)),
                DeleteAction::make()
                    ->visible(fn (PaymentMethod $record): bool => PaymentMethodResource::canDelete($record))
                    ->before(function (PaymentMethod $record, Action $action): void {
                        $reason = PaymentMethodDeletionGuard::rejectReason([$record]);

                        if ($reason !== null) {
                            Notification::make()
                                ->title('Metode tidak bisa dihapus')
                                ->body($reason)
                                ->warning()
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => PaymentMethodResource::canDeleteAny())
                        ->before(function (Collection $records, Action $action): void {
                            $reason = PaymentMethodDeletionGuard::rejectReason($records);

                            if ($reason !== null) {
                                Notification::make()
                                    ->title('Beberapa metode tidak bisa dihapus')
                                    ->body($reason)
                                    ->warning()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
