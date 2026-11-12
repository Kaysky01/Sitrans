<?php

namespace App\Filament\App\Resources\Products\Pages;

use App\Filament\App\Resources\Products\ProductResource;
use App\Modules\Catalog\Services\ProductCsv;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sembunyikan tambah & impor bagi role yang tidak dapat mengelola menu;
            // ekspor boleh bagi siapa pun yang dapat melihat.
            ...(ProductResource::canCreate() ? [CreateAction::make()->label('Tambah Menu')] : []),
            Action::make('import')
                ->label('Impor CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->visible(fn (): bool => auth()->user()?->can('menu.manage') ?? false)
                ->form([
                    FileUpload::make('file')
                        ->label('Berkas CSV')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->helperText('Kolom: name, sku, category, price, cost, is_active, track_stock.')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $stats = app(ProductCsv::class)->import($path);

                    Storage::disk('local')->delete($data['file']);

                    Notification::make()
                        ->title('Impor selesai')
                        ->body("{$stats['created']} baru, {$stats['updated']} diperbarui, {$stats['skipped']} dilewati.")
                        ->success()
                        ->send();
                }),
            Action::make('export')
                ->label('Ekspor CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $rows = app(ProductCsv::class)->rows();

                    return response()->streamDownload(function () use ($rows): void {
                        $handle = fopen('php://output', 'w');

                        fwrite($handle, "\xEF\xBB\xBF");

                        foreach ($rows as $row) {
                            fputcsv($handle, $row);
                        }

                        fclose($handle);
                    }, 'produk-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
                }),
        ];
    }
}
