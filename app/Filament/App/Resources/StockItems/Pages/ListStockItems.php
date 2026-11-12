<?php

namespace App\Filament\App\Resources\StockItems\Pages;

use App\Filament\App\Resources\StockItems\StockItemResource;
use App\Models\Product;
use App\Modules\Inventory\Enums\StockMovementType;
use App\Modules\Inventory\Services\StockLedger;
use App\Support\Format;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListStockItems extends ListRecords
{
    protected static string $resource = StockItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Tambah stok')
                ->icon('heroicon-o-plus')
                ->visible(fn (): bool => auth()->user()?->can('inventory.manage') ?? false)
                ->modalHeading('Catat stok masuk')
                ->form([
                    Select::make('product_id')
                        ->label('Menu')
                        ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('product_variant_id', null)),
                    Select::make('product_variant_id')
                        ->label('Variasi')
                        ->options(fn (Get $get): array => Product::query()
                            ->find($get('product_id'))
                            ?->variants()
                            ->orderBy('sort_order')
                            ->pluck('name', 'id')
                            ->all() ?? [])
                        ->placeholder('Tanpa variasi')
                        ->searchable(),
                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(fn (Get $get): string => Product::query()->find($get('product_id'))?->isFractionalUnit() ? '0.001' : '1')
                        ->helperText(fn (Get $get): string => ($product = Product::query()->find($get('product_id'))) !== null
                            ? 'Satuan: '.$product->unitLabel().($product->isFractionalUnit()
                                ? ' — boleh pecahan, mis. 2,5.'
                                : ' — isi bilangan bulat, mis. 12.')
                            : 'Pilih menu terlebih dahulu untuk melihat satuannya.')
                        ->default(0),
                    TextInput::make('reorder_level')
                        ->label('Batas minimum')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Textarea::make('note')
                        ->label('Catatan')
                        ->rows(2)
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    $product = Product::query()->findOrFail($data['product_id']);
                    $variantId = $data['product_variant_id'] !== null && $data['product_variant_id'] !== ''
                        ? (int) $data['product_variant_id']
                        : null;

                    $ledger = app(StockLedger::class);
                    $stock = $ledger->forProduct($product, $variantId);

                    $stock->reorder_level = (float) ($data['reorder_level'] ?? 0);
                    $stock->save();

                    if ((float) $data['quantity'] > 0) {
                        $ledger->apply(
                            $stock,
                            StockMovementType::In,
                            (float) $data['quantity'],
                            $data['note'] ?? null,
                            auth()->id(),
                        );
                    }

                    $stock->refresh();

                    Notification::make()
                        ->title('Stok tersimpan')
                        ->body($stock->label().' kini '.Format::quantity($stock->quantity, $product->quantityDecimals()).' '.$product->unitLabel().'.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
