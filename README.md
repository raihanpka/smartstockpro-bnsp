# SmartStock Pro

Sistem Manajemen Inventaris berbasis web untuk PT Maju Bersama Digital, dibangun di atas Laravel dan PostgreSQL.
Dikembangkan oleh **Raihan Putra Kirana** untuk memenuhi prasyarat sertifikasi BNSP.

---

## Deskripsi

SmartStock Pro menggantikan proses pencatatan stok manual berbasis spreadsheet dengan satu platform terpusat yang dapat diakses oleh seluruh gudang secara bersamaan. Sistem ini mencakup manajemen stok real-time, transfer barang antar gudang, notifikasi otomatis, dan pelaporan berbasis data untuk mendukung pengambilan keputusan manajemen.

PT Maju Bersama Digital mengoperasikan 5 gudang di Jakarta, Surabaya, Bandung, Medan, dan Makassar. SmartStock Pro dirancang untuk menyatukan operasional kelima lokasi tersebut ke dalam satu antarmuka yang konsisten.

## Fitur Utama

**Autentikasi dan Keamanan**
- Login multi-level: Admin, Manajer Gudang, Staf Gudang, Viewer
- Password hashing dengan bcrypt, proteksi CSRF, dan validasi SQL Injection via Eloquent
- Session timeout otomatis setelah 60 menit tidak aktif
- Audit log seluruh aktivitas pengguna dengan Global Audit Log Observer
- Rekaman dokumen mitigasi keamanan (*Security Risk Analysis*)

**Dashboard dan Monitoring**
- Grafik stok, tren barang masuk/keluar, dan nilai inventaris secara real-time via Chart.js
- Peta lokasi gudang interaktif menggunakan Leaflet.js
- Panel monitoring resource server (CPU, RAM, response time) secara real-time
- Ekspor laporan PDF dengan grafik dan tabel berwarna
- *System Logs UI* interaktif untuk *tracking exceptions*

**Manajemen Inventaris**
- CRUD lengkap: Produk (dengan Upload Gambar), Kategori, Gudang, Supplier, Transaksi Masuk/Keluar
- Algoritma perhitungan stok *First-In-First-Out* (FIFO) *Batch Deduction*
- Pencarian produk dengan pagination, sorting, dan filtering

**Notifikasi dan Alert**
- Alert otomatis ketika stok di bawah minimum threshold (in-app dan email)
- Middleware pencatat *Slow Request* dan *Error Notification*
- Dashboard log error dengan kategorisasi severity

**Transfer dan Pemrosesan Paralel**
- Transfer barang antar gudang sinkron via database transaction yang ter-lock
- Batch import data produk dari file CSV/Excel
- Background job untuk generate laporan besar

---

## Tech Stack

| Layer | Teknologi | Versi |
|---|---|---|
| Backend | Laravel | 12.x |
| Runtime | PHP | 8.3+ |
| Database | PostgreSQL | 16+ |
| Queue | Laravel Queues | Native |
| Frontend | Blade + Alpine.js | Alpine 3.x |
| Styling | Tailwind CSS | 3.x |
| Maps | Leaflet.js | 1.9.x |
| Charts | Chart.js | 4.x |
| PDF Export | barryvdh/laravel-dompdf | Latest |
| File Import | Maatwebsite/Laravel-Excel | 3.x |
| Package Manager | Composer + NPM | Composer 2.x |

---

## Instalasi

### Prasyarat

Pastikan versi berikut tersedia sebelum memulai:

| Prasyarat | Versi Minimum |
|---|---|
| PHP | 8.3 |
| Composer | 2.x |
| Node.js | 20.x |
| NPM | 10.x |
| PostgreSQL | 16 |
| Redis | 7 |

### Langkah Awal (Semua Platform)

```bash
git clone https://github.com/your-org/smartstock-pro.git
cd smartstock-pro

cp .env.example .env

composer install
npm install

php artisan key:generate
php artisan migrate --seed

npm run build
```

Isi `.env` sesuai konfigurasi database dan Redis pada platform masing-masing (lihat bagian di bawah).

---

### Mac (Laravel Herd)

1. Install [Laravel Herd](https://herd.laravel.com/) dan pastikan sudah aktif.
2. Pindahkan folder project ke `~/Herd/`. Herd otomatis mendeteksi project Laravel dan melayaninya di `http://smartstock-pro.test`.
3. Install PostgreSQL dan Redis via [DBngin](https://dbngin.com/) atau Homebrew:

```bash
brew install postgresql@16 redis
brew services start postgresql@16
brew services start redis
```

4. Buat database:

```bash
psql -U postgres -c "CREATE DATABASE smartstock_pro;"
```

5. Update `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smartstock_pro
DB_USERNAME=postgres
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

6. Jalankan queue worker di terminal terpisah:

```bash
php artisan queue:work
```

---

### Windows

1. Install [Laragon](https://laragon.org/) untuk lingkungan PHP lokal.
2. Install PHP 8.3 dari [windows.php.net](https://windows.php.net/download/) dan tambahkan ke PATH.
3. Install Composer dari [getcomposer.org](https://getcomposer.org/).
4. Install PostgreSQL 16 dari [postgresql.org](https://www.postgresql.org/download/windows/).
5. Install Redis menggunakan [Memurai](https://www.memurai.com/) (native Windows) atau aktifkan via WSL2.
6. Install Node.js 20 dari [nodejs.org](https://nodejs.org/).
7. Buat database di psql atau pgAdmin:

```sql
CREATE DATABASE smartstock_pro;
```

8. Update `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=smartstock_pro
DB_USERNAME=postgres
DB_PASSWORD=your_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

9. Jalankan server dan queue worker di dua terminal terpisah:

```bash
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:work
```

Akses aplikasi di `http://localhost:8000`.

---

### VPS (Ubuntu 22.04+)

Untuk deployment di server produksi/VPS (misalnya Ubuntu), sangat disarankan untuk **hanya menggunakan Docker** agar lebih bersih dan konsisten, daripada meng-install PHP/Nginx secara manual.

Jika Anda menggunakan VPS standar (tanpa panel Dokploy):
1. Install Docker & Docker Compose di server Anda.
2. Clone repository:
   ```bash
   git clone https://github.com/your-org/smartstock-pro.git /var/www/smartstock-pro
   cd /var/www/smartstock-pro
   ```
3. Copy environment file dan sesuaikan kredensialnya:
   ```bash
   cp .env.example .env
   ```
4. Jalankan aplikasi menggunakan docker-compose standalone (yang sudah *include* Nginx):
   ```bash
   docker compose up -d --build
   ```
5. Inisialisasi awal database (cukup sekali):
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```

Aplikasi Anda kini sudah berjalan lewat Docker secara penuh!

---

## Dokumentasi

- [Dokumentasi Teknis untuk Klien](docs/Dokumentasi_Teknis.md) - Dokumentasi teknis, arsitektur, panduan pengguna, dan troubleshooting untuk klien
- [Arsitektur & Infrastruktur](docs/Arsitektur_dan_Infrastruktur.md) - Detail topologi perangkat keras dan jaringan
- [Tools & Framework](docs/Tools_dan_Framework.md) - Analisis skalabilitas dan peranti pihak ketiga
- [Migrasi & Pembaharuan](docs/Kebutuhan_Migrasi_dan_Pembaharuan.md) - Strategi cutover, version control, dan analisis dampak

---

## Kredit

Dikembangkan oleh **Raihan Putra Kirana (G6401231027)** untuk memenuhi prasyarat sertifikasi BNSP.