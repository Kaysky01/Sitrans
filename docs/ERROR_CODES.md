# Daftar Kode Error — Sitrans

Semua response error dari API dan dari aksi domain aplikasi memakai format
seragam. Pesan selalu ramah pengguna (bahasa Indonesia); detail teknis (stack
trace, user id, endpoint) hanya masuk ke log server, tidak pernah ke pengguna.

## Format Response Error

```json
{
  "success": false,
  "code": "STOK_TIDAK_CUKUP",
  "message": "Stok Es Teh Manis tersisa 2. Sesuaikan jumlah di keranjang.",
  "errors": { "items": "Stok Es Teh Manis tersisa 2. Sesuaikan jumlah di keranjang." }
}
```

- `success` — selalu `false` untuk error.
- `code` — kode mesin yang stabil untuk dikonsumsi integrasi/klien.
- `message` — pesan ramah yang boleh langsung ditampilkan ke pengguna.
- `errors` — peta per-field untuk validasi (kosong bila bukan error field).

## Status HTTP & Kelas Error

| Status | Kelas (app/Exceptions) | Kapan dipakai |
|---|---|---|
| 401 | `AuthenticationException` | Sesi berakhir / API key salah |
| 403 | `ForbiddenError`, `AuthorizationException` | Tidak punya izin |
| 404 | `NotFoundError`, `ModelNotFoundException` | Data tidak ada |
| 409 | `ConflictError` | State berubah (order sudah dibayar, transisi status tidak valid) |
| 422 | `ValidationError`, `ValidationException` | Validasi gagal |
| 429 | `RateLimitedError`, `ThrottleRequestsException` | Batas percobaan terlampaui |
| 500 | fallback | Server error — pesan generik saja |

## Daftar Kode

| Kode | Status | Arti & pesan ke pengguna |
|---|---|---|
| `VALIDASI_GAGAL` | 422 | Ada field yang tidak valid. "Periksa kembali data yang kamu isi." |
| `KESALAHAN_SERVER` | 500 | "Server sedang bermasalah, coba beberapa saat lagi." |
| `SESI_BERAKHIR` | 401 | "Sesi berakhir, silakan login kembali." |
| `TANPA_IZIN` | 403 | "Kamu tidak punya izin untuk aksi ini." |
| `TIDAK_DITEMUKAN` | 404 | "Data yang kamu cari tidak ditemukan. Mungkin sudah dihapus atau tidak pernah ada." |
| `KONFLIK_STATUS` | 409 | "Perubahan tidak bisa diproses karena data sudah berubah. Muat ulang lalu coba lagi." |
| `TERLALU_BANYAK_PERCOBAAN` | 429 | "Terlalu banyak percobaan. Tunggu beberapa menit lalu coba lagi." |
| `KAFE_TIDAK_TERHUBUNG` | 403 | Kafe/tenant tidak dikenali dari request (API key atau login bermasalah). |
| `PERMINTAAN_TIDAK_VALID` | 4xx | "Permintaan tidak valid." |
| `STOK_TIDAK_CUKUP` | 422 | Stok tidak mencukupi saat bayar/pesan; menyebut produk & sisa stok. |
| `PEMBAYARAN_KURANG` | 422 | "Uang diterima kurang Rp X." (field `amountPaid`) |
| `PESANAN_SUDAH_DIBAYAR` | 409 | Percobaan bayar kedua pada order yang sudah dibayar ditolak. |
| `STATUS_TIDAK_VALID` | 409 | Transisi status tidak valid (mis. membayar order yang sudah void/fulfilled). |
| `PESANAN_TIDAK_TERBUKA` | 409 | Diskon hanya bisa diubah saat pesanan masih terbuka. |
| `PIN_SALAH` | 422 | "PIN salah. Sisa percobaan: N." (maks 5 percobaan) |
| `PIN_TERKUNCI` | 422 | 5 percobaan salah → PIN terkunci 5 menit. |
| `MENU_TIDAK_DITEMUKAN` | 404 | Menu yang dipesan lewat API tidak ada (id menu tidak valid). |
| `MENU_TIDAK_TERSEDIA` | 422 | Menu nonaktif / sedang habis. |
| `JUMLAH_TIDAK_VALID` | 422 | Jumlah item di luar rentang 1–999. |
| `HARGA_TIDAK_VALID` | 422 | Harga item negatif. |
| `DISKON_TIDAK_VALID` | 422 | Diskon negatif, persentase > 100, atau pada pesanan nonaktif. |
| `APP_ERROR` | 500 | Error domain tanpa kode lebih spesifik. |

## Di Frontend (Livewire/Blade)

- Request Livewire gagal (5xx/jaringan/sesi 419) → overlay "Terjadi kesalahan"
  dengan tombol **Muat ulang** (`resources/js/app.js`, Livewire
  `interceptRequest`). Detail teknis tetap di console.
- Banner "Tidak ada koneksi internet" otomatis saat `navigator.onLine === false`.
- Request yang berjalan lebih dari 15 detik → toast peringatan koneksi lambat.
- Toast di kasir: hijau (sukses), merah (error), kuning (peringatan); auto-tutup
  ±4 detik, error bisa ditutup manual.

## Konvensi untuk Pengembang

1. Untuk error domain baru, buat/kembangkan `AppError` (atau `ValidationError`
   dengan kode) — jangan pernah `abort(500, detail-teknis)` atau lempar
   `RuntimeException` yang pesannya bocor ke klien.
2. Setiap `try/catch` di Livewire harus menghasilkan state yang jelas:
   toast/error inline/loading, jangan diam-diam mengabaikan.
3. Semua pesan ke pengguna ditulis dalam bahasa Indonesia yang ramah dan
   singkat; bahasa teknis hanya di log server.
