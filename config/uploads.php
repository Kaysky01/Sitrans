<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Penyimpanan unggahan
    |--------------------------------------------------------------------------
    |
    | Semua foto menu, kategori, dan paket memakai satu disk dan aturan yang
    | sama. Database hanya menyimpan path relatif; URL publik dibentuk dari
    | disk ini sehingga berpindah ke S3/CDN cukup dengan mengubah konfigurasi.
    |
    */

    'disk' => env('UPLOAD_DISK', 'public'),

    'max_size_kb' => 2048,

    'accepted_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kompresi di perangkat (client-side)
    |--------------------------------------------------------------------------
    |
    | FilePond milik Filament mengecilkan gambar di browser sebelum diunggah.
    | Dengan sisi terpanjang 1200 px, foto ponsel biasanya turun jauh di bawah
    | 500 KB tanpa terlihat pecah di layar kasir.
    |
    */

    'resize' => [
        'width' => 1200,
        'height' => 1200,
    ],

    'directories' => [
        'products' => 'products',
        'categories' => 'categories',
        'bundles' => 'bundles',
        'branding' => 'branding',
    ],

];
