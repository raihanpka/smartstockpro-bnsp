# SmartStock Pro - Technical Guide

- **Versi Dokumen**: 1.0
- **Terakhir Diperbarui**: Mei 2026
- **Proyek**: SmartStock Pro, Sistem Manajemen Inventaris
- **Klien**: PT Maju Bersama Digital

---

## Daftar Isi

1. [Glosarium](#glosarium)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Spesifikasi Infrastruktur](#spesifikasi-infrastruktur)
4. [Tech Stack dan Justifikasi Pemilihan](#tech-stack-dan-justifikasi-pemilihan)
5. [Struktur Database](#struktur-database)
6. [Pola Arsitektur MVC](#pola-arsitektur-mvc)
7. [Modul dan Fitur](#modul-dan-fitur)
8. [Strategi Deployment](#strategi-deployment)
9. [Deployment dengan Docker Compose](#deployment-dengan-docker-compose)
10. [Migrasi dari Sistem Lama](#migrasi-dari-sistem-lama)
11. [Skalabilitas](#skalabilitas)
12. [Panduan Pengguna](#panduan-pengguna)
13. [Dokumentasi API](#dokumentasi-api)
14. [FAQ](#faq)
15. [Troubleshooting Guide](#troubleshooting-guide)

---

## Glosarium

| Istilah | Definisi |
|---|---|
| **Stok Minimum (Threshold)** | Batas jumlah stok yang memicu notifikasi alert jika terlampaui. |
| **Transfer Antar Gudang** | Perpindahan barang dari satu gudang ke gudang lain, dicatat sebagai transaksi keluar di gudang asal dan transaksi masuk di gudang tujuan. |
| **FIFO** | First In First Out, metode penghitungan stok di mana barang yang pertama masuk adalah yang pertama dikeluarkan. |
| **LIFO** | Last In First Out, kebalikan dari FIFO. |
| **Audit Log** | Catatan lengkap semua aktivitas pengguna di sistem, mencatat siapa melakukan apa dan kapan. |
| **Job Queue** | Antrian pekerjaan yang diproses di latar belakang tanpa memblokir antarmuka pengguna. |
| **Laravel Cache** | Sistem caching bawaan Laravel yang menyimpan hasil komputasi sementara di storage (database atau memory) untuk mempercepat response. Tidak memerlukan infrastruktur eksternal. |
| **Database Queue** | Driver queue bawaan Laravel yang menggunakan tabel database untuk menyimpan antrian job, tanpa memerlukan Redis atau layanan eksternal. |
| **Role** | Peran pengguna yang menentukan hak akses di sistem. |
| **SKU** | Stock Keeping Unit, kode unik untuk setiap produk. |
| **VPS** | Virtual Private Server, server virtual yang digunakan untuk deployment produksi. |
| **Herd** | Aplikasi Laravel untuk lingkungan pengembangan lokal di Mac. |
| **Migration** | Script terstruktur untuk membuat dan mengubah schema database secara terkontrol. |
| **Seeder** | Script untuk mengisi database dengan data awal atau data uji. |
| **MVC** | Model-View-Controller, pola arsitektur yang memisahkan data (Model), tampilan (View), dan logika request (Controller). |
| **Docker Compose** | Tool untuk mendefinisikan dan menjalankan multi-container Docker application dalam satu file konfigurasi. |

---

## Arsitektur Sistem

SmartStock Pro menggunakan arsitektur **modular monolith**, yaitu satu aplikasi yang di-deploy sebagai satu unit namun dibagi secara internal menjadi modul-modul yang jelas berdasarkan domain bisnis.

Pendekatan ini dipilih karena:

- Tim pengembang kecil, sehingga overhead operasional pendekatan microservices tidak sebanding manfaatnya
- Satu unit deployment lebih mudah di-monitor, di-debug, dan di-rollback
- Pemisahan modul yang jelas memungkinkan ekstraksi ke service terpisah di masa depan jika diperlukan

### Diagram Topologi

```
[Browser Pengguna]
        |
        v
[Nginx Web Server]
        |
        v
[PHP-FPM / Laravel Application]
    |
    v
[PostgreSQL]
(Data utama + Cache + Queue)
        |
        v
[Queue Worker]
(Transfer, Import, Report, Email)
```

### Komponen Utama

| Komponen | Fungsi |
|---|---|
| Nginx | Web server dan reverse proxy |
| PHP-FPM | Process manager untuk PHP |
| Laravel | Application framework, routing, ORM, queue |
| PostgreSQL | Penyimpanan data utama, cache (tabel `cache`), dan job queue (tabel `jobs`) |
| Queue Worker | Proses terpisah yang mengeksekusi job dari tabel queue di database |

---

## Spesifikasi Infrastruktur

### Spesifikasi Minimum Server (VPS Produksi)

| Komponen | Minimum | Rekomendasi |
|---|---|---|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4 GB | 8 GB |
| Storage | 50 GB SSD | 100 GB SSD |
| Bandwidth | 100 Mbps | 200 Mbps |
| OS | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |

### Alokasi Service di Server

Untuk beban awal (5 gudang, hingga 50 pengguna concurrent):

- Satu VPS cukup untuk menjalankan Nginx, PHP-FPM, PostgreSQL, Redis, dan Queue Worker sekaligus.
- Jika traffic meningkat signifikan, PostgreSQL dapat dipindahkan ke server database terpisah sebagai langkah scaling pertama.

---

## Tech Stack dan Justifikasi Pemilihan

### Backend: Laravel 12 (PHP 8.3)

Laravel dipilih karena kecepatan pengembangan yang tinggi. Laravel menyediakan autentikasi, validasi, ORM (Eloquent), queue, notifikasi email, dan sistem otorisasi secara bawaan tanpa perlu konfigurasi tambahan. Untuk skala PT Maju Bersama Digital, Laravel lebih dari cukup dan dokumentasinya sangat lengkap untuk tim pengembang baru.

### Database: PostgreSQL 16

PostgreSQL dipilih karena kemampuan penanganan data relasional yang kuat, dukungan untuk query kompleks (stok multi-gudang, laporan agregasi), dan fitur index yang efisien (partial index, BRIN index untuk log). MySQL merupakan alternatif yang valid, tetapi PostgreSQL lebih baik untuk query analitik yang diperlukan dashboard manajemen.

### Queue: Laravel Queue dengan Database Driver

Pekerjaan berat (transfer antar gudang, import CSV, generate laporan) diproses secara asinkron oleh queue worker agar antarmuka pengguna tetap responsif. Driver `database` dipilih sebagai backend queue karena menggunakan PostgreSQL yang sudah tersedia, tanpa memerlukan Redis atau infrastruktur eksternal tambahan. Untuk skala PT Maju Bersama Digital (5 gudang, puluhan pengguna concurrent), database driver sudah lebih dari cukup.

### Frontend: Blade + Alpine.js + Tailwind CSS

Blade adalah template engine bawaan Laravel yang menghindari kebutuhan membangun API terpisah untuk frontend. Alpine.js menangani interaktivitas UI ringan (dropdown, modal, validasi form) tanpa overhead framework SPA penuh. Tailwind CSS mempercepat styling dengan pendekatan utility-first.

### Library Pihak Ketiga

| Library | Versi | Lisensi | Fungsi |
|---|---|---|---|
| `barryvdh/laravel-dompdf` | ^3.0 | MIT | Generate laporan PDF |
| `maatwebsite/excel` | ^3.1 | MIT | Import/export CSV dan Excel |
| `leaflet` | ^1.9 | BSD-2 | Peta lokasi gudang interaktif |
| `chart.js` | ^4.4 | MIT | Grafik dashboard |
| `alpinejs` | ^3.13 | MIT | Interaktivitas frontend ringan |
| `tailwindcss` | ^3.4 | MIT | Utility CSS framework |

---

## Pola Arsitektur MVC

SmartStock Pro mengikuti pola **Model-View-Controller (MVC)** yang merupakan inti dari framework Laravel. Pola ini memisahkan tanggung jawab aplikasi menjadi tiga lapisan yang jelas, ditambah **Service Layer** di atas MVC standar.

### Diagram Alur Request

```
HTTP Request
     |
     v
[Route] (routes/web.php, routes/api.php)
     |
     v
[Middleware] (Auth, RoleMiddleware, AuditLogMiddleware)
     |
     v
[Controller] (app/Http/Controllers/)
  - Validasi input via Form Request
  - Otorisasi via Policy
  - Delegasi ke Service
     |
     v
[Service Layer] (app/Services/)
  - Business logic
  - Orkestrasi Model dan Job
     |
     |
     v                v
[Model/Eloquent]  [Job Queue]
(app/Models/)     (app/Jobs/)
     |                |
     v                v
[PostgreSQL]    [Queue Worker]
     |
     v
[View/Blade] (resources/views/)
HTTP Response
```

### Penjelasan Per Lapisan

#### Model (`app/Models/`)

Model merepresentasikan tabel database dan mendefinisikan relasi antar entitas menggunakan Eloquent ORM.

```php
// app/Models/Product.php
class Product extends Model
{
    protected $fillable = ['name', 'sku', 'stock', 'min_stock', 'category_id', 'warehouse_id'];

    // Relasi ke model lain
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    // Scope query yang sering digunakan
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<', 'min_stock');
    }
}
```

#### View (`resources/views/`)

View menggunakan Blade template engine. View hanya bertanggung jawab untuk menampilkan data, tidak mengandung logika bisnis.

```blade
{{-- resources/views/inventory/products/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div x-data="{ search: '' }">
    <h1>Daftar Produk</h1>

    @foreach ($products as $product)
        <x-product-row :product="$product" />
    @endforeach

    {{ $products->links() }}
</div>
@endsection
```

#### Controller (`app/Http/Controllers/`)

Controller menerima HTTP request, mendelegasikan ke Service, dan mengembalikan View atau JSON. Controller tidak mengandung business logic.

```php
// app/Http/Controllers/Inventory/ProductController.php
class ProductController extends Controller
{
    public function __construct(private StockCalculationService $stockService) {}

    public function index(Request $request): View
    {
        $products = Product::with(['category', 'warehouse'])
            ->filter($request->only(['search', 'warehouse_id', 'category_id']))
            ->paginate(25);

        return view('inventory.products.index', compact('products'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $this->stockService->createProduct($request->validated());
        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }
}
```

#### Service Layer (`app/Services/`)

Service berisi seluruh business logic dan menjadi satu-satunya titik masuk untuk operasi yang mengubah data.

```php
// app/Services/StockCalculationService.php
class StockCalculationService
{
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            AuditLog::record('product.created', $product);
            return $product;
        });
    }

    public function recordInbound(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = StockTransaction::create($data);
            $transaction->product->increment('stock', $data['quantity']);

            // Cek apakah stok sudah di atas minimum, batalkan alert jika ada
            AlertService::checkAndResolve($transaction->product);

            return $transaction;
        });
    }
}
```

#### Job Queue (`app/Jobs/`)

Job menangani proses berat secara asinkron. Didispatch dari Service, dieksekusi oleh Queue Worker di proses terpisah.

```php
// app/Jobs/TransferStockJob.php
class TransferStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $transferData) {}

    public function handle(StockCalculationService $stockService): void
    {
        $stockService->executeTransfer($this->transferData);
    }

    // Job otomatis di-retry jika gagal (maks 3x)
    public int $tries = 3;
}
```

### Mapping Domain ke MVC

| Domain | Model | Controller | Service | Job |
|---|---|---|---|---|
| Inventaris | `Product`, `Category`, `Supplier` | `Inventory/ProductController` | `StockCalculationService` | - |
| Transfer | `StockTransfer` | `Transfer/TransferController` | `TransferService` | `TransferStockJob` |
| Laporan | - | `Report/ReportController` | `ReportService` | `GenerateReportJob` |
| Notifikasi | `Notification` | `Notification/NotifController` | `AlertService` | - |
| Import | - | `Inventory/ImportController` | `ImportService` | `ImportProductsJob` |

---

## Struktur Database

### Tabel Utama

| Tabel | Deskripsi |
|---|---|
| `users` | Data pengguna dengan kolom `role` (admin, manager, staff, viewer) |
| `warehouses` | Data 5 gudang beserta koordinat untuk Leaflet |
| `categories` | Kategori produk |
| `suppliers` | Data supplier |
| `products` | Data produk dengan kolom `stock`, `min_stock`, `sku` |
| `stock_transactions` | Transaksi masuk dan keluar stok |
| `stock_transfers` | Transfer barang antar gudang |
| `audit_logs` | Log seluruh aktivitas pengguna |
| `notifications` | Notifikasi in-app per pengguna |
| `job_batches` | Status batch import dan generate laporan |

### Indeks yang Dipasang

- `products.sku` : unique index untuk pencarian cepat
- `stock_transactions.product_id` : index untuk filter transaksi per produk
- `stock_transactions.warehouse_id` : index untuk filter per gudang
- `audit_logs.created_at` : BRIN index untuk tabel append-only volume tinggi
- `audit_logs.user_id` : index untuk filter log per pengguna

---

## Modul dan Fitur

### Modul 1: Autentikasi dan Keamanan

Sistem login menggunakan Laravel Breeze dengan empat level akses:

| Role | Hak Akses |
|---|---|
| Admin | Akses penuh ke semua fitur dan konfigurasi sistem |
| Manajer Gudang | Akses ke gudang yang dikelola, persetujuan transfer |
| Staf Gudang | Input transaksi masuk/keluar di gudang sendiri |
| Viewer | Hanya baca (lihat laporan dan dashboard) |

Fitur keamanan:
- Password di-hash dengan `bcrypt` (cost factor 12)
- Proteksi CSRF aktif di semua form dan validasi SQL Injection via Eloquent
- Session timeout otomatis setelah 60 menit tidak aktif (dapat dikonfigurasi)
- Seluruh aksi tulis dicatat di `audit_logs` melalui `AuditObserver` secara global
- Dokumen mitigasi risiko dan arsitektur terlampir di `docs/Arsitektur_dan_Infrastruktur.md` dan `docs/Tools_dan_Framework.md`

### Modul 2: Dashboard dan Monitoring

Dashboard menampilkan:
- Ringkasan total stok per gudang
- Grafik tren barang masuk/keluar 30 hari terakhir (Chart.js)
- Nilai inventaris keseluruhan
- Peta lokasi gudang dengan status stok (Leaflet.js)
- Daftar produk dengan stok kritis (di bawah minimum)
- Panel monitoring resource server (CPU, RAM, response time), diperbarui secara langsung untuk Sysadmin

Laporan PDF dapat di-generate dan diunduh dari halaman laporan. PDF memuat logo perusahaan, tabel berwarna, dan grafik.

### Modul 3: Manajemen Inventaris

CRUD lengkap tersedia untuk:
- Produk (dengan upload gambar via Laravel Storage untuk galeri/multimedia)
- Kategori
- Gudang
- Supplier
- Transaksi Masuk/Keluar

Fitur tambahan:
- Pencarian produk dengan full-text search
- Pagination, sorting, dan filtering di semua tabel
- Perhitungan stok otomatis menerapkan algoritma abstraksi FIFO (First In First Out) di mana batch transaksi tertua dipotong terlebih dahulu saat pencatatan Outbound.

### Modul 4: Notifikasi dan Alert

- Alert in-app dan email otomatis ketika stok produk turun di bawah nilai `min_stock`
- Notifikasi error/exception dicatat oleh middleware `PerformanceMonitor`
- Dashboard log error di `/system-logs` untuk melihat parsing langsung dari `laravel.log` dengan kategorisasi severity: critical, warning, info

### Modul 5: Transfer Antar Gudang

Transfer barang diproses melalui job queue sehingga stok di gudang asal dan tujuan diperbarui secara paralel tanpa memblokir antarmuka pengguna.

Alur transfer:
1. Staf/Manajer membuat permintaan transfer
2. Job `TransferStockJob` di-dispatch ke queue
3. Worker memperbarui stok kedua gudang dalam satu database transaction
4. Notifikasi dikirimkan ke Manajer Gudang tujuan
5. Status transfer dapat dipantau di halaman Transfer

Batch import produk dari CSV/Excel juga diproses via queue agar tidak memblokir UI meski file memiliki ribuan baris.

---

## Strategi Deployment

### Development (Lokal)

Dijalankan di laptop developer menggunakan Laravel Herd (Mac), Laragon (Windows), atau `php artisan serve`. Data non-produktif, debug mode aktif.

### Staging & Production (VPS)

Menggunakan server VPS produksi dengan spesifikasi minimum 2 CPU, 4GB RAM. 
Untuk menjaga kebersihan dan konsistensi server, **seluruh deployment sangat dianjurkan untuk menggunakan Docker saja**. 
Tidak perlu melakukan instalasi Nginx, PHP, dan Supervisor secara manual di level OS. Pendekatan ini (Containerization) akan sangat mempermudah proses migrasi dan isolasi enviroment.

Jika Anda men-deploy ke VPS, abaikan instalasi manual dan ikuti bagian **Deployment dengan Docker Compose** di bawah ini. Terdapat instruksi spesifik untuk VPS biasa (dengan Nginx di dalam docker) maupun VPS yang menggunakan panel Dokploy.

---

## Deployment dengan Docker Compose

Docker Compose adalah cara termudah untuk menjalankan SmartStock Pro di environment baru tanpa instalasi PHP, Nginx, atau PostgreSQL secara manual. Terdapat dua pilihan konfigurasi utama yang disediakan:

1. `docker-compose.yml`: Lengkap dengan container Nginx untuk deployment standalone.
2. `docker-compose-dokploy.yml`: Tanpa Nginx, dirancang untuk deployment di Dokploy. Di dalam konfigurasi ini, Traefik bawaan Dokploy akan otomatis menangani traffic routing, sehingga kita hanya perlu mengekspos port internal aplikasi ke host.

### Struktur File Docker

```text
smartstock-pro/
├── docker-compose.yml            # Definisi semua service standalone (dengan Nginx)
├── docker-compose-dokploy.yml    # Definisi service tanpa Nginx untuk Dokploy
├── .dockerignore                 # File yang dikecualikan saat build image
├── Dockerfile                    # Build image PHP-FPM aplikasi
└── docker/
    └── nginx/
        └── default.conf          # Konfigurasi Nginx untuk container
```

### docker-compose.yml (Standalone)

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: smartstock_app
    restart: unless-stopped
    volumes:
      - .:/var/www/html
      - /var/www/html/vendor
      - /var/www/html/node_modules
    environment:
      - APP_ENV=production
    depends_on:
      - db
    networks:
      - smartstock_net

  nginx:
    image: nginx:1.25-alpine
    container_name: smartstock_nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - smartstock_net

  db:
    image: postgres:16-alpine
    container_name: smartstock_db
    restart: unless-stopped
    environment:
      POSTGRES_DB: smartstock_pro
      POSTGRES_USER: smartstock
      POSTGRES_PASSWORD: secret
    volumes:
      - db_data:/var/lib/postgresql/data
    networks:
      - smartstock_net

  worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: smartstock_worker
    restart: unless-stopped
    command: php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
    volumes:
      - .:/var/www/html
      - /var/www/html/vendor
    depends_on:
      - db
    networks:
      - smartstock_net

volumes:
  db_data:

networks:
  smartstock_net:
    driver: bridge
```

> **Catatan**: Tidak ada Redis container karena SmartStock Pro menggunakan PostgreSQL sebagai backend cache dan queue. Ini menyederhanakan topologi deployment.

### Dockerfile

```dockerfile
FROM php:8.3-fpm-alpine

# Install dependencies sistem
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    nodejs npm \
    && docker-php-ext-install pdo_pgsql mbstring gd zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci && npm run build \
    && php artisan storage:link

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### docker/nginx/default.conf

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### .dockerignore

File ini mengecualikan folder dan file yang tidak perlu disalin ke dalam image Docker, sehingga image lebih kecil dan build lebih cepat:

```
# Git
.git
.gitignore

# Dependencies (akan di-install di dalam container)
vendor/
node_modules/

# Build output
public/build/

# Environment dan secret
.env
.env.*
!.env.example

# Storage (di-mount sebagai volume)
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
storage/logs/

# Dokumentasi dan file development
docs/
*.md
!README.md
phpunit.xml
.phpunit.cache/
tests/

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db
```

### Perintah Docker Pertama Kali

```bash
# Salin dan sesuaikan env (gunakan DB_HOST=db sesuai nama service)
cp .env.example .env
# Edit .env: DB_HOST=db, DB_USERNAME=smartstock, DB_PASSWORD=secret, DB_DATABASE=smartstock_pro
# CACHE_STORE=database, QUEUE_CONNECTION=database

# Build dan jalankan semua service
docker compose up -d --build

# Setup aplikasi
docker compose exec app php artisan key:generate
docker compose exec app php artisan queue:table
docker compose exec app php artisan migrate --seed

# Akses di http://localhost:8080
```

### Perintah Operasional Docker

```bash
# Lihat status service
docker compose ps

# Lihat log realtime
docker compose logs -f app
docker compose logs -f worker

# Restart service tertentu
docker compose restart app

# Deploy update
git pull origin main
docker compose build app worker
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan queue:restart

# Hentikan semua service
docker compose down

# Hentikan dan hapus volume (data database ikut terhapus)
docker compose down -v
```

---

## Migrasi dari Sistem Lama

### Strategi Migrasi (Spreadsheet ke SmartStock Pro)

#### Fase 1: Persiapan (1 minggu)

- Audit seluruh spreadsheet yang digunakan di 5 gudang
- Standardisasi format: nama produk, satuan, kode gudang
- Tentukan field mapping (lihat tabel di bawah)
- Identifikasi data yang tidak lengkap atau inkonsisten

#### Mapping Field

| Field Spreadsheet | Field SmartStock Pro | Catatan |
|---|---|---|
| Nama Barang | `products.name` | Wajib unik per SKU |
| Kode/ID Barang | `products.sku` | Generate otomatis jika kosong |
| Kategori | `categories.name` | Dibuat dulu di master data |
| Jumlah Stok | `products.stock` | Nilai awal, diverifikasi ulang |
| Stok Minimum | `products.min_stock` | Default 10 jika kosong |
| Lokasi Gudang | `warehouses.id` | Mapping berdasarkan nama kota |
| Supplier | `suppliers.name` | Dibuat dulu di master data |

#### Fase 2: Import Data (2-3 hari)

1. Ekspor seluruh data spreadsheet ke format CSV per gudang.
2. Bersihkan data (hapus baris duplikat, isi field wajib yang kosong).
3. Gunakan fitur batch import di SmartStock Pro untuk meng-upload tiap file CSV.
4. Sistem memvalidasi data dan melaporkan baris yang bermasalah.

#### Fase 3: Validasi Pasca-Migrasi (2-3 hari)

- Cross-check jumlah produk: total produk di SmartStock Pro harus sama dengan total di spreadsheet
- Verifikasi stok awal: 5 sampel acak per gudang
- Uji coba transaksi masuk dan keluar
- Pastikan notifikasi alert berfungsi

#### Rollback Plan

Jika ada masalah kritis sebelum cutover resmi, kembali ke spreadsheet karena sistem lama tidak dimatikan selama fase migrasi. Setelah cutover resmi, backup database SmartStock Pro dibuat setiap hari.

### Cutover Plan

| Tahap | Aktivitas | Durasi |
|---|---|---|
| Pra-cutover | Backup spreadsheet terakhir, pastikan SmartStock Pro sudah berjalan di server produksi | 1 hari |
| Freeze Data | Stop input baru di spreadsheet, lakukan import final | 2-4 jam |
| Go-Live | Aktifkan SmartStock Pro untuk semua pengguna, training singkat | 1 hari |
| Monitoring | Pantau intensif selama 3 hari pertama, tim teknis standby | 3 hari |
| Closure | Arsipkan spreadsheet lama, sistem lama resmi nonaktif | 1 hari |

---

## Dokumentasi Tambahan

- [Panduan AI Agents](AGENTS.md)
- [Dokumen Arsitektur dan Infrastruktur](Arsitektur_dan_Infrastruktur.md)
- [Dokumen Tools dan Framework](Tools_dan_Framework.md)
- [Dokumen Kebutuhan Migrasi dan Pembaharuan](Kebutuhan_Migrasi_dan_Pembaharuan.md)
- [README Project Utama](../README.md)
| Closure | Arsipkan spreadsheet lama, sistem lama resmi nonaktif | 1 hari |

---

## Skalabilitas

### Skenario Peningkatan Beban

| Skenario | Solusi |
|---|---|
| Penambahan gudang baru | Tambah record di tabel `warehouses`, tidak ada perubahan kode |
| Penambahan pengguna | Tidak ada batas di aplikasi, perlu upgrade RAM jika concurrent users meningkat signifikan |
| Volume transaksi tinggi | Tambah index pada kolom filter, aktifkan query caching |
| Data historis sangat besar | Implementasi partitioning tabel `stock_transactions` per tahun |
| Traffic spike | Upgrade VPS (vertical scaling) atau tambah server queue worker terpisah |

### Analisis Dampak Perubahan

Setiap perubahan pada modul inti berdampak ke modul lain sebagai berikut:

| Modul Diubah | Dampak ke Modul Lain |
|---|---|
| `products` (schema) | Transfer, Transaksi, Laporan, Notifikasi |
| `warehouses` (schema) | Transfer, Dashboard, Laporan |
| `stock_transactions` | Perhitungan stok, Laporan, Dashboard |
| `users` / `roles` | Seluruh modul (otorisasi) |

Sebelum melakukan perubahan pada modul di atas, jalankan seluruh test suite dan komunikasikan ke tim terkait.

---

## Panduan Pengguna

### Login dan Navigasi

1. Buka URL SmartStock Pro di browser.
2. Masukkan email dan password yang diberikan oleh Admin.
3. Setelah login, pilih menu di sidebar kiri sesuai kebutuhan.
4. Session otomatis berakhir setelah 60 menit tidak aktif. Login ulang jika diperlukan.

### Menambahkan Produk Baru (Admin / Manajer Gudang)

1. Buka menu **Inventaris > Produk**.
2. Klik tombol **Tambah Produk**.
3. Isi formulir: nama, SKU, kategori, supplier, gudang, stok awal, stok minimum.
4. Upload gambar produk jika tersedia (opsional).
5. Klik **Simpan**.

### Mencatat Transaksi Masuk

1. Buka menu **Inventaris > Transaksi**.
2. Klik **Catat Barang Masuk**.
3. Pilih produk, gudang, jumlah, dan tanggal.
4. Klik **Simpan**. Stok produk diperbarui otomatis.

### Transfer Barang Antar Gudang

1. Buka menu **Transfer**.
2. Klik **Buat Transfer Baru**.
3. Pilih produk, gudang asal, gudang tujuan, dan jumlah.
4. Klik **Kirim**. Sistem akan memproses transfer di latar belakang.
5. Status transfer dapat dipantau di halaman **Transfer > Riwayat**.

### Import Data dari CSV

1. Buka menu **Inventaris > Import**.
2. Download template CSV yang tersedia.
3. Isi data sesuai format template.
4. Upload file CSV dan klik **Proses Import**.
5. Sistem akan memvalidasi dan mengimpor data. Notifikasi dikirimkan setelah selesai.

### Export Laporan PDF

1. Buka menu **Laporan**.
2. Tentukan rentang tanggal dan gudang yang ingin dilaporkan.
3. Klik **Generate Laporan**.
4. Setelah laporan siap (proses di latar belakang), unduh file PDF dari halaman yang sama.

---

## Dokumentasi API

SmartStock Pro menyediakan REST API JSON untuk integrasi dengan sistem eksternal. Dokumentasi API tersedia di:

```
https://smartstockpro.id/api/documentation
```

Endpoint yang tersedia:

| Endpoint | Metode | Deskripsi |
|---|---|---|
| `/api/v1/products` | GET | Daftar produk dengan filter dan pagination |
| `/api/v1/products/{id}` | GET | Detail satu produk |
| `/api/v1/warehouses` | GET | Daftar gudang |
| `/api/v1/transactions` | GET | Daftar transaksi |
| `/api/v1/transactions` | POST | Catat transaksi baru |
| `/api/v1/transfers` | POST | Buat transfer antar gudang |
| `/api/v1/stock/summary` | GET | Ringkasan stok per gudang |

Autentikasi API menggunakan Bearer Token yang dapat digenerate di halaman **Pengaturan > API Keys**.

---

## FAQ

**1. Apa yang terjadi jika saya lupa password?**
Klik tautan "Lupa Password" di halaman login. Sistem akan mengirimkan link reset password ke email yang terdaftar.

**2. Apakah data produk dan stok real-time?**
Ya. Data stok diperbarui secara langsung setiap ada transaksi masuk, keluar, atau transfer yang berhasil diproses.

**3. Berapa lama proses transfer antar gudang?**
Transfer diproses oleh sistem secara otomatis, biasanya selesai dalam hitungan detik. Pada kondisi server sibuk (misalnya sedang ada import massal), mungkin memerlukan waktu beberapa menit.

**4. Apakah ada batas jumlah produk yang bisa diimpor sekaligus?**
Tidak ada batas keras, tetapi disarankan tidak melebihi 5.000 baris per file untuk performa optimal. File yang lebih besar dapat dipecah menjadi beberapa batch.

**5. Bagaimana sistem menentukan kapan notifikasi stok kritis dikirim?**
Notifikasi dikirimkan secara otomatis setiap kali transaksi keluar menyebabkan stok produk turun ke bawah nilai "Stok Minimum" yang sudah dikonfigurasi di data produk.

**6. Bisakah saya mengubah role pengguna setelah akun dibuat?**
Ya, Admin dapat mengubah role pengguna di menu **Pengaturan > Manajemen Pengguna**.

**7. Di mana saya bisa melihat riwayat perubahan data?**
Admin dapat mengakses **Pengaturan > Audit Log** untuk melihat seluruh riwayat perubahan, termasuk siapa yang melakukan aksi dan kapan.

**8. Apakah laporan PDF bisa dikustomisasi?**
Saat ini template laporan PDF sudah terstandarisasi. Permintaan kustomisasi template perlu disampaikan ke tim pengembang.

**9. Bagaimana cara menambahkan gudang baru?**
Admin dapat menambahkan gudang baru di menu **Master Data > Gudang**. Setelah gudang ditambahkan, langsung tersedia untuk transaksi dan transfer.

**10. Apakah sistem bisa diakses dari ponsel?**
Ya. SmartStock Pro menggunakan Tailwind CSS yang responsif dan dapat diakses dari browser mobile.

**11. Berapa lama data audit log disimpan?**
Audit log disimpan selama 90 hari secara default. Admin dapat mengubah nilai ini di pengaturan sistem.

**12. Apa yang harus dilakukan jika ada transaksi yang salah dicatat?**
Transaksi tidak dapat dihapus untuk menjaga integritas data. Lakukan transaksi koreksi (kebalikan dari transaksi yang salah) dan tambahkan catatan pada kolom keterangan.

---

## Troubleshooting Guide

### Halaman Tidak Bisa Diakses (500 Internal Server Error)

Langkah pemeriksaan:
1. Periksa file log di `storage/logs/laravel.log` untuk pesan error spesifik.
2. Pastikan file `.env` ada dan memiliki nilai `APP_KEY` yang valid.
3. Jalankan `php artisan config:clear` lalu `php artisan config:cache`.
4. Pastikan permission folder `storage` dan `bootstrap/cache` dapat ditulis:
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

### Queue Job Tidak Diproses (Transfer/Import Tidak Berjalan)

Langkah pemeriksaan:
1. Pastikan queue worker berjalan:
   ```bash
   sudo supervisorctl status smartstock-worker
   ```
2. Jika berhenti, restart:
   ```bash
   sudo supervisorctl restart smartstock-worker
   ```
3. Periksa log worker di `/var/log/smartstock-worker.log`.
4. Periksa apakah ada job yang stuck di tabel `jobs`:
   ```bash
   php artisan queue:failed
   ```
5. Periksa koneksi database karena queue menggunakan PostgreSQL:
   ```bash
   php artisan tinker
   >>> DB::select('SELECT 1');
   ```

### Import CSV Gagal / Sebagian Data Tidak Masuk

Langkah pemeriksaan:
1. Pastikan format file CSV menggunakan delimiter koma (,), bukan titik koma (;).
2. Pastikan header baris pertama sesuai template yang disediakan.
3. Periksa kolom wajib tidak ada yang kosong (nama produk, SKU, gudang).
4. SKU yang duplikat akan di-skip otomatis. Periksa log import untuk daftar baris yang gagal.

### Notifikasi Email Tidak Terkirim

Langkah pemeriksaan:
1. Pastikan konfigurasi SMTP di `.env` sudah benar (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`).
2. Periksa apakah email masuk ke folder spam di penerima.
3. Test pengiriman email:
   ```bash
   php artisan tinker
   >>> Mail::raw('Test email', fn($m) => $m->to('test@example.com')->subject('Test'));
   ```
4. Periksa log Laravel untuk error pengiriman.

### Database Error: Connection Refused

Langkah pemeriksaan:
1. Pastikan PostgreSQL berjalan:
   ```bash
   sudo systemctl status postgresql
   sudo systemctl start postgresql   # Jika tidak berjalan
   ```
2. Verifikasi kredensial di `.env` (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
3. Coba koneksi manual:
   ```bash
   psql -h 127.0.0.1 -U smartstock -d smartstock_pro
   ```

### Stok Tidak Terupdate Setelah Transaksi

Kemungkinan penyebab:
1. Queue worker tidak berjalan (lihat bagian di atas).
2. Job gagal karena error validasi. Periksa tabel `failed_jobs` di database:
   ```bash
   php artisan queue:failed
   ```
3. Retry job yang gagal:
   ```bash
   php artisan queue:retry all
   ```

### Halaman Dashboard Lambat

Langkah optimasi:
1. Jalankan `php artisan cache:clear` untuk membersihkan cache yang kedaluarsa, lalu muat ulang halaman agar cache diisi ulang.
2. Pastikan eager loading sudah diterapkan di query dashboard (tidak ada N+1).
3. Periksa apakah ada query yang berjalan lama di log PostgreSQL (`/var/log/postgresql/`).
4. Hubungi tim teknis jika masalah berlanjut untuk audit query dan index.