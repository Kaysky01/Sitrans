<?php

namespace App\Filament\App\Resources\Orders\Tables;

use App\Models\Order;
use App\Modules\Pos\Actions\CompleteOrderAction;
use App\Modules\Pos\Actions\VerifySupervisorPinAction;
use App\Modules\Pos\Actions\VoidOrderAction;
use App\Modules\Pos\Enums\OrderFulfillmentStatus;
use App\Modules\Pos\Enums\OrderStatus;
use App\Support\Format;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('No.')->searchable()->weight('semibold'),
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn ($state) => $state?->shortLabel()),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::Open => 'warning',
                        OrderStatus::Paid, OrderStatus::Completed => 'success',
                        OrderStatus::Voided => 'danger',
                        OrderStatus::Refunded => 'gray',
                    }),
                TextColumn::make('fulfillment_status')->label('Pesanan')->badge()
                    ->formatStateUsing(fn (?OrderFulfillmentStatus $state): string => $state?->label() ?? 'Pesanan Baru')
                    ->color(fn (?OrderFulfillmentStatus $state): string => $state?->color() ?? 'warning'),
                TextColumn::make('grand_total')->label('Total')
                    ->formatStateUsing(fn ($state): string => Format::rupiah($state))
                    ->alignEnd(),
                TextColumn::make('cashier.name')->label('Kasir'),
                TextColumn::make('outlet.name')->label('Outlet')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status pembayaran')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(
                        fn (OrderStatus $status) => [$status->value => $status->label()]
                    )->all()),
                SelectFilter::make('fulfillment_status')
                    ->label('Status pesanan')
                    ->options(collect(OrderFulfillmentStatus::cases())->mapWithKeys(
                        fn (OrderFulfillmentStatus $status) => [$status->value => $status->label()]
                    )->all()),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat')
                    ->modalHeading(fn (Order $record): string => 'Transaksi '.$record->number)
                    ->modalDescription('Rincian pesanan, pelanggan, dan nilai tagihan.')
                    ->modalWidth(Width::FourExtraLarge)
                    ->schema([
                        Section::make('Ringkasan pesanan')
                            ->icon('heroicon-o-receipt-percent')
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'xl' => 4,
                            ])
                            ->schema([
                                TextEntry::make('status')->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                                    ->color(fn (OrderStatus $state): string => match ($state) {
                                        OrderStatus::Open => 'warning',
                                        OrderStatus::Paid, OrderStatus::Completed => 'success',
                                        OrderStatus::Voided => 'danger',
                                        OrderStatus::Refunded => 'gray',
                                    }),
                                TextEntry::make('type')->label('Tipe pesanan')
                                    ->formatStateUsing(fn ($state): string => $state?->label() ?? '-'),
                                TextEntry::make('fulfillment_status')->label('Status pesanan')
                                    ->badge()
                                    ->formatStateUsing(fn (?OrderFulfillmentStatus $state): string => $state?->label() ?? 'Pesanan Baru')
                                    ->color(fn (?OrderFulfillmentStatus $state): string => $state?->color() ?? 'warning'),
                                TextEntry::make('created_at')->label('Dibuat pada')->dateTime('d M Y, H:i'),
                                TextEntry::make('cashier.name')->label('Kasir')->placeholder('-'),
                                TextEntry::make('customer_name')->label('Pelanggan')->placeholder('Pelanggan langsung'),
                                TextEntry::make('table_number')->label('Nomor meja')->placeholder('-'),
                                TextEntry::make('outlet.name')->label('Outlet')->placeholder('-'),
                                TextEntry::make('notes')->label('Catatan')->placeholder('-'),
                            ]),
                        Section::make('Item pesanan')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                RepeatableEntry::make('items')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('name_snapshot')->label('Item')->weight('bold'),
                                        TextEntry::make('variant_name')->label('Varian')->placeholder('-'),
                                        TextEntry::make('quantity')->label('Qty')->suffix(' item'),
                                        TextEntry::make('unit_price')->label('Harga satuan')->money('IDR'),
                                        TextEntry::make('line_total')->label('Total')->money('IDR')->weight('bold')->color('primary'),
                                    ])
                                    ->grid([
                                        'default' => 1,
                                        'sm' => 2,
                                        'xl' => 5,
                                    ]),
                            ]),
                        Section::make('Ringkasan pembayaran')
                            ->icon('heroicon-o-banknotes')
                            ->columns([
                                'default' => 2,
                                'lg' => 5,
                            ])
                            ->schema([
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                                TextEntry::make('discount_total')->label('Diskon')->money('IDR')->color('danger'),
                                TextEntry::make('tax_amount')->label('PB1')->money('IDR'),
                                TextEntry::make('service_amount')->label('Service')->money('IDR'),
                                TextEntry::make('grand_total')->label('Total tagihan')->money('IDR')->weight('bold')->color('primary'),
                            ]),
                    ]),
                Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid
                        && (auth()->user()?->can('orders.view') ?? false))
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        app(CompleteOrderAction::class)->execute($record);

                        Notification::make()
                            ->title('Transaksi diselesaikan')
                            ->success()
                            ->send();
                    }),
                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => ! $record->isVoided()
                        && $record->status !== OrderStatus::Refunded
                        && (auth()->user()?->can('orders.void') ?? false))
                    ->modalHeading('Batalkan transaksi')
                    ->form([
                        TextInput::make('pin')
                            ->label('PIN supervisor')
                            ->password()
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $supervisor = app(VerifySupervisorPinAction::class)
                            ->execute((string) $data['pin']);

                        app(VoidOrderAction::class)->execute($record, $supervisor, (string) $data['reason']);

                        Notification::make()
                            ->title('Transaksi dibatalkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
