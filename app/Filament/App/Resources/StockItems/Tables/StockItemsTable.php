<?php

namespace App\Filament\App\Resources\StockItems\Tables;

use App\Models\StockItem;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use App\Support\Format;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('product.name')->label('Menu')->searchable()->weight('semibold'),
                TextColumn::make('variant.name')->label('Variasi')->placeholder('—')->toggleable(),
                TextColumn::make('quantity')->label('Stok')
                    ->formatStateUsing(fn (StockItem $record, mixed $state): string => Format::quantity($state, $record->product?->quantityDecimals() ?? 0).' '.($record->product?->unit ?? ''))
                    ->sortable()->alignEnd(),
                TextColumn::make('reorder_level')->label('Min.')
                    ->formatStateUsing(fn (StockItem $record, mixed $state): string => Format::quantity($state, $record->product?->quantityDecimals() ?? 0).' '.($record->product?->unit ?? ''))
                    ->alignEnd()->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (StockItem $record): string => $record->statusLabel())
                    ->color(fn (StockItem $record): string => $record->statusColor()),
                TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('low')
                    ->label('Perlu restock')
                    ->query(fn (Builder $query): Builder => $query->whereColumn('quantity', '<=', 'reorder_level')),
            ])
            ->recordActions([
                Action::make('adjust')
                    ->label('Penyesuaian')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->visible(fn (): bool => auth()->user()?->can('inventory.manage') ?? false)
                    ->modalHeading(fn (StockItem $record): string => 'Penyesuaian stok — '.$record->label())
                    ->form([
                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                StockMovementType::In->value => StockMovementType::In->label(),
                                StockMovementType::Out->value => StockMovementType::Out->label(),
                                StockMovementType::Adjustment->value => StockMovementType::Adjustment->label(),
                                StockMovementType::Waste->value => StockMovementType::Waste->label(),
                            ])
                            ->default(StockMovementType::In->value)
                            ->required()
                            ->live(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->step(fn (StockItem $record): string => $record->product?->isFractionalUnit() ? '0.001' : '1')
                            ->helperText(fn (StockItem $record, callable $get): string => $get('type') === StockMovementType::Adjustment->value
                                ? 'Nilai koreksi, boleh negatif (mis. -3). Satuan: '.($record->product?->unitLabel() ?? '-').'.'
                                : 'Jumlah positif. Satuan: '.($record->product?->unitLabel() ?? '-').'.'),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(2)
                            ->maxLength(255),
                    ])
                    ->action(function (StockItem $record, array $data): void {
                        app(StockLedger::class)->apply(
                            $record,
                            StockMovementType::from($data['type']),
                            (float) $data['quantity'],
                            $data['note'] ?? null,
                            auth()->id(),
                        );

                        Notification::make()
                            ->title('Stok diperbarui')
                            ->body('Sisa stok '.Format::quantity($record->fresh()->quantity, $record->product?->quantityDecimals() ?? 0).' '.($record->product?->unitLabel() ?? '').'.')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
