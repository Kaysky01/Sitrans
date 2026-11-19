<?php

namespace App\Support\Uploads;

use Closure;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Satu definisi unggah foto untuk seluruh panel: disk terpusat, format yang
 * diterima, batas ukuran, dan validasi isi file (magic bytes) supaya file
 * palsu dengan ekstensi gambar tidak lolos.
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
            ->rule(static fn (): Closure => self::imageContentRule())
            ->required($required)
            ->columnSpanFull();
    }

    public static function helperText(): string
    {
        $maxMb = (int) (config('uploads.max_size_kb') / 1024);
        $width = (int) config('uploads.resize.width');

        return "Format: JPG, PNG, atau WEBP. Maksimal {$maxMb} MB. Foto otomatis dikompres & di-resize (maks {$width} px) agar layar kasir ringan dan cepat dibuka. Foto tersimpan aman di server.";
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

    /**
     * Cek isi file (magic bytes), bukan cuma nama/ekstensi. File hasil
     * konversi FilePond (JPG/PNG dari canvas) tetap lolos karena berbasis
     * isi file, bukan tanda tangan aplikasi tertentu.
     */
    public static function imageContentRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            $files = is_array($value) ? $value : [$value];

            foreach ($files as $file) {
                if (! $file instanceof TemporaryUploadedFile) {
                    continue;
                }

                $realPath = $file->getRealPath();

                if ($realPath === false || ! is_file($realPath)) {
                    $fail('Foto gagal dibaca. Coba unggah ulang berkasnya.');

                    continue;
                }

                $head = @file_get_contents($realPath, false, null, 0, 16);

                if ($head === false || ! self::hasImageSignature($head)) {
                    $fail('File ini bukan gambar asli. Gunakan foto dengan format JPG, PNG, atau WEBP.');
                }
            }
        };
    }

    public static function hasImageSignature(string $head): bool
    {
        if ($head === '') {
            return false;
        }

        // JPEG: FF D8 FF
        if (str_starts_with($head, "\xFF\xD8\xFF")) {
            return true;
        }

        // PNG: 89 50 4E 47 0D 0A 1A 0A
        if (str_starts_with($head, "\x89PNG\r\n\x1A\n")) {
            return true;
        }

        // WEBP: "RIFF" + ukuran (4 byte) + "WEBP"
        if (strlen($head) >= 12 && str_starts_with($head, 'RIFF') && substr($head, 8, 4) === 'WEBP') {
            return true;
        }

        return false;
    }
}
