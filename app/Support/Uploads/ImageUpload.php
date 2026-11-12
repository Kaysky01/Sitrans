<?php

namespace App\Support\Uploads;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;

/**
 * Satu definisi unggah foto untuk seluruh panel: disk terpusat, format yang
 * diterima, batas ukuran, dan kompresi client-side lewat FilePond.
 */
final class ImageUpload
{
    public static function make(string $name, string $directory, string $label, bool $required = false): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageEditor()
            ->disk(config('uploads.disk'))
            ->directory(config("uploads.directories.{$directory}", $directory))
            ->acceptedFileTypes(config('uploads.accepted_types'))
            ->maxSize(config('uploads.max_size_kb'))
            ->automaticallyResizeImagesToWidth(config('uploads.resize.width'))
            ->automaticallyResizeImagesToHeight(config('uploads.resize.height'))
            ->automaticallyUpscaleImagesWhenResizing(false)
            ->helperText(self::helperText())
            ->required($required)
            ->columnSpanFull();
    }

    public static function helperText(): string
    {
        $maxMb = (int) (config('uploads.max_size_kb') / 1024);
        $width = (int) config('uploads.resize.width');

        return "Format JPG, PNG, atau WEBP. Maksimal {$maxMb} MB. Foto otomatis dikompres di perangkat (sisi terpanjang {$width} px, sekitar di bawah 500 KB) sebelum diunggah.";
    }

    /**
     * URL publik untuk path yang tersimpan di database.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk(config('uploads.disk'))->url($path);
    }
}
