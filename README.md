# Sitrans

Aplikasi kasir (POS) dan manajemen kafe multi-tenant: setiap kafe memiliki data menu, outlet, stok, dan laporan sendiri, sementara platform memiliki panel admin terpisah.

Dibangun dengan **Laravel 13**, **Filament 5**, **Livewire 4**, **Tailwind CSS 4**, dan **MySQL**.

---

## Prasyarat

Pastikan semua tools berikut sudah terpasang:

| Tool | Versi minimal | Catatan |
| --- | --- | --- |
| PHP | 8.3+ | Disarankan PHP 8.5. Ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `ctype`, `json`, `tokenizer`, `xml` |
| Composer | 2.x | |
| Node.js | 20.19+ atau 22.12+ | Vite 8 membutuhkan Node versi ini |
| npm | 10+ | |
| MySQL | 8.0+ / MariaDB 10.6+ | Bisa memakai MySQL bawaan Laragon/XAMPP |
| Git | 2.x | |

> Pengguna Windows + Laragon: cukup jalankan **Start All** di Laragon agar MySQL aktif.

---

## Langkah Menjalankan Project

### 1. Clone repository

```bash
git clone <url-repository> sitrans
cd sitrans
```

### 2. Install dependency PHP

```bash
composer install
```

### 3. Buat file `.env`

Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

macOS/Linux:

```bash
cp .env.example .env
```

Lalu sesuaikan bagian database di `.env`:

```dotenv
APP_URL=http://sitrans.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sitrans
DB_USERNAME=root
DB_PASSWORD=
```

> `DB_PASSWORD` biasanya kosong pada Laragon/XAMPP. Jika MySQL memakai password, isi sesuai konfigurasi lokal.

### 4. Buat database

Lewat phpMyAdmin/HeidiSQL: buat database bernama `sitrans` dengan collation `utf8mb4_unicode_ci`.

Atau lewat terminal:

```bash
mysql -u root -e "CREATE DATABASE sitrans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat data demo:

- Super admin platform
- Tenant **Kopi Senja** beserta outlet, pajak, metode pembayaran, dan role
- Menu contoh (kopi, non-kopi, makanan, camilan, modifier)

Jika ingin mengulang dari database kosong:

```bash
php artisan migrate:fresh --seed
```

> Perintah ini menghapus seluruh tabel lalu membangunnya ulang. Jangan dipakai jika sudah ada data penting.

### 7. Install dependency frontend dan build asset

```bash
npm install
npm run build
```

Saat development, `npm run build` boleh dilewati jika nanti menjalankan `npm run dev`.

### 8. Jalankan aplikasi

**Opsi A — satu terminal (paling mudah):**

```bash
composer run dev
```

Perintah ini menjalankan beberapa proses sekaligus:

- `php artisan serve` → http://127.0.0.1:8000
- `php artisan queue:listen` → pemroses queue
- `npm run dev` → Vite (hot reload)
- `php artisan reverb:start` → WebSocket server (port 8080)

**Opsi B — manual, dua terminal:**

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Buka **http://127.0.0.1:8000** (atau `http://sitrans.test` jika virtual host Laragon sudah aktif).

### 9. Login memakai akun demo

| Peran | Email | Password | URL |
| --- | --- | --- | --- |
| Super admin platform | `admin@sitrans.test` | `password` | `/admin` |
| Owner tenant demo | `owner@kopisenja.test` | `password` | `/app` |

Halaman lain yang bisa dicoba:

- Landing page: `/`
- Registrasi tenant baru: `/register`
- Storefront tenant demo: `/m/kopi-senja`
- Kasir (butuh izin `pos.use`): `/pos`

---

## Perintah Harian

```bash
# Menjalankan seluruh proses development
composer run dev

# Menjalankan test
php artisan test --compact

# Merapikan format kode PHP
vendor/bin/pint --dirty

# Membuat / mempromosikan super admin
php artisan sitrans:super-admin admin@example.com --name="Nama Admin"

# Sinkronisasi permission untuk semua tenant lama
php artisan sitrans:sync-permissions

# Membersihkan cache konfigurasi, route, dan view
php artisan optimize:clear

# Membuat symlink storage (sekali saja, untuk foto menu)
php artisan storage:link
```

---

## Troubleshooting

**`SQLSTATE[HY000] [2002] No connection could be made...`**
MySQL belum berjalan atau kredensial di `.env` salah. Jalankan MySQL/Laragon lalu cek `DB_HOST`, `DB_PORT`, `DB_USERNAME`, dan `DB_PASSWORD`.

**`Unable to locate file in Vite manifest`**
Asset frontend belum dibuild. Jalankan `npm run build`, atau jalankan `npm run dev` selama development.

**Perubahan tampilan tidak muncul**
Hentikan lalu jalankan ulang `npm run dev`, atau jalankan `npm run build` jika memakai mode production.

**Foto menu tidak tampil**
Jalankan `php artisan storage:link`, lalu cek kembali `APP_URL`.

**Error `APP_KEY` kosong**
Jalankan `php artisan key:generate`.

**Port 8000 / 8080 sudah dipakai**
Hentikan proses lain, atau jalankan server di port berbeda:

```bash
php artisan serve --port=8001
```

**Setelah mengubah `.env` tapi hasilnya tidak berubah**
Jalankan `php artisan optimize:clear`.

---

## Struktur Singkat

```
app/
├── Filament/Admin/       # Panel platform super admin (/admin)
├── Filament/App/         # Panel tenant / kafe (/app)
├── Livewire/             # Halaman POS, storefront, registrasi
├── Modules/
│   ├── Catalog/          # Menu, kategori, modifier, bundle
│   ├── Inventory/        # Stok dan pergerakan stok
│   ├── Pos/              # Transaksi, pembayaran, shift kasir
│   ├── Reporting/        # Laporan penjualan dan pajak
│   └── Tenancy/          # Tenant, outlet, registrasi
└── Support/              # Helper lintas modul (tenant, akses, dsb.)

database/
├── migrations/           # Skema database
├── factories/            # Factory untuk testing
└── seeders/              # Data demo

resources/
├── css/filament/app/     # Tema panel Filament
└── js/                   # Entry point Vite
```
