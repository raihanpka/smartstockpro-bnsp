# Smartstock Pro - Kebutuhan Migrasi dan Pembaharuan

- **Versi Dokumen**: 1.0
- **Terakhir Diperbarui**: Mei 2026
- **Proyek**: SmartStock Pro, Sistem Manajemen Inventaris
- **Klien**: PT Maju Bersama Digital

---

## Daftar Isi

1. [Skenario Migrasi dari Sistem Lama](#skenario-migrasi-dari-sistem-lama)
2. [Dokumen Cutover Plan](#dokumen-cutover-plan)
3. [Skenario Pembaharuan Perangkat Lunak](#skenario-pembaharuan-perangkat-lunak)
4. [Analisis Dampak Perubahan](#analisis-dampak-perubahan)

---

## 1. Skenario Migrasi dari Sistem Lama

Latar belakang perusahaan yang sebelumnya sangat mengandalkan pencatatan lembar kerja (Spreadsheet) manual secara terdistribusi menghadirkan tantangan konsolidasi data. Berikut adalah rancangan migrasinya.

### 1.1 Strategi Migrasi Data
Migrasi harus dimulai dengan melakukan Audit Data Aktual (Stock Opname) fisik akhir, guna memastikan jumlah fiktif di lembar kerja disinkronkan dengan stok nyata di rak tiap gudang. Setelah itu, tim melakukan Standardisasi Data (menghilangkan duplikasi SKU, meluruskan penulisan satuan). 

### 1.2 Mapping Field Sistem
Agar data lama masuk sempurna, berikut adalah pemetaan struktur tabelnya:
* Kolom "Nama Barang" di spreadsheet dipetakan persis ke kolom tabel `products.name`.
* Kolom "Kode Referensi" menjadi `products.sku` (sistem akan menolak baris jika terjadi penggandaan SKU).
* Kolom "Kategori Barang" tidak bisa langsung diimpor teksnya, melainkan dikonversi menjadi ID master terkait pada tabel `categories`.
* Kolom "Jumlah Tersedia" dicatat sebagai stok pendaftaran awal di `products.stock`.

### 1.3 Validasi Data Pasca Migrasi
Begitu skrip penyuntikan (Import Script) menyatakan selesai, validasi silang dilakukan:
* Menjalankan kueri pencocokan matematika antara kalkulasi total volume persediaan di PostgreSQL dengan summasi di lembar kerja CSV.
* Pengambilan sampel fisik (Physical Sampling) acak sebanyak 10 item per gudang untuk diverifikasi langsung oleh tim Quality Assurance ke lapangan.

### 1.4 Rollback Plan
Apabila sistem menemukan lebih dari 10% kesalahan format (data korup) saat import, migrasi akan dibatalkan menggunakan instruksi teknis `php artisan migrate:refresh`. Seluruh staf akan diperintahkan untuk kembali memakai sistem lembar kerja harian (Fallback mechanism) tanpa ada penundaan bisnis operasional, sampai perbaikan algoritma impor selesai dievaluasi ulang.

## 2. Dokumen Cutover Plan

Langkah Cutover adalah momen dramatis saat saklar utama sistem baru dinyalakan dan sistem usang ditinggalkan.

### 2.1 Timeline dan Fase Pelaksanaan
* **Jumat Pukul 17:00 (Hari H minus 2)**: Freeze Data. Penginputan di Spreadsheet lama dihentikan secara absolut.
* **Sabtu Pukul 08:00 (Hari H minus 1)**: Migrasi awal (Initial Load) ke server prapeluncuran (Staging) untuk pengujian.
* **Minggu Pukul 10:00 (Hari H)**: Migrasi Basis Data ke mesin produksi final. Lingkungan aplikasi dapat diakses teknisi namun dikunci untuk staf umum.
* **Senin Pukul 06:00 (Go Live)**: Pembukaan perisai pembatasan hak cipta bagi semua akun operator dan kepala gudang.

### 2.2 Checklist Pra Cutover
* [ ] Spesifikasi perangkat keras kluster peladen terverifikasi sesuai rekomendasi Bab 1.
* [ ] Fail Docker image versi pamungkas terpasang (build).
* [ ] Otentikasi SSL HTTPS aktif sempurna (gembok hijau).
* [ ] Layanan server SMTP untuk surat elektronik peringatan dini diuji fungsinya.

### 2.3 Langkah Cutover Utama
1. Mengunci hak tulis (Write Permission) semua lembar kerja daring Google Spreadsheet usang menjadi mode Baca Saja (Read Only).
2. Mengeksekusi penarikan modul import Excel untuk injeksi final data yang terhenti sejak hari Jumat sore.
3. Membersihkan seluruh jejak sampah singgahan (Cache Clearance) menggunakan peramban `php artisan optimize:clear`.

### 2.4 Verifikasi Pasca Cutover
* Menjalankan simulasi mutasi kecil satu barang uji untuk memastikan modul pencatatan Audit Log, Transaksi, dan algoritma pemotongan FIFO mencatat relasi secara logis.
* Memonitor dasbor sistem panel (Nginx Access Log) untuk menyapu jejak galat kode status 500 selama 24 jam ke depan.

## 3. Skenario Pembaharuan Perangkat Lunak

Demi menjaga integritas, segala inovasi perangkat lunak (contohnya menambahkan modul Pemindai Kode Batang) harus mengikuti kerangka pikir Version Control yang tertib (Git Flow).

### 3.1 Penggunaan Version Control Git
* **Main Branch**: Cabang induk sakral berisikan kode yang sedang melayani publik. Dilarang keras menulis kode ke mari secara sembrono tanpa melalui persetujuan.
* **Develop Branch**: Cabang prapeluncuran (Staging) tempat mengumpulkan mahakarya pemrograman dari puluhan programer terpisah.
* **Feature Branch**: Lingkungan koding personal, semisal `git checkout -b feature/kode-batang`. Apabila koding tuntas, dibuatlah pengajuan penarikan silang (Pull Request) menuju Develop untuk diperiksa tim juri (Peer Review).

### 3.2 Strategi Rilis Tanpa Gangguan
Berbekal kontainer Docker, peluncuran (Deployment) cabang Main tidak membutuhkan sistem dihentikan (Zero Downtime). Prosesnya menggunakan prinsip Blue Green Deployment di mana kontainer baru akan disiapkan diam diam di balik layar. Baru setelah prosesnya utuh (Warmed Up), pengarah rute (Reverse Proxy) akan mengalirkan saluran masuk pengguna (Traffic Route) ke kotak peladen yang baru dalam sekian milidetik, nyaris tak disadari konsumen akhir.

## 4. Analisis Dampak Perubahan

Analisis Dampak Perubahan (Impact Analysis) adalah mitigasi antisipatif untuk mencegah fitur baru menabrak ekosistem kodingan lama yang sehat.

### 4.1 Studi Kasus Perubahan Skema
Sebagai contoh situasi teknis, Manajemen mendadak menuntut agar Sistem SmartStock Pro di masa depan mengakomodir atribut "Tenggat Waktu Kedaluwarsa (Expired Date)" karena peluasan lini bisnis ke komoditi bahan rentan basi.

### 4.2 Pemetaan Dampak ke Seluruh Lapisan
Perubahan tersebut tidak sebatas menaruh kolom di Basis Data. Ini rantai imbas kerusakannya jika tidak dianalisa:
1. **Lapisan Basis Data (Migrations)**: Butuh perlakuan khusus untuk mengubah tabel `stock_transactions` dan menambahkan `expired_at` tanpa menghapus riwayat jutaan log sebelumnya.
2. **Lapisan Pengawas API (Request Validation)**: Form Requests di kerangka Controller wajib membendung (validate) form, harus mewajibkan petugas gudang mengisi data kedaluwarsa ini jika yang dimutasi adalah barang rentan basi.
3. **Lapisan Logika Servis (Service Layer)**: Kelas `StockCalculationService` terdampak masif. Jika awalnya algoritma mendahulukan stok paling purba masuk (FIFO), maka barisan kalkulator harus direkonstruksi agar mengutamakan barang yang tanggal kedaluwarsanya paling mendekati masa kini (menjadi paradigma First Expired First Out).
4. **Lapisan Robot Asisten (Background Queue Job)**: Akan dituntut membuat agen inspektur (Cron Job Scheduled Task) baru di tengah malam buta untuk menyisir miliaran baris stok mana saja yang usianya tinggal 30 hari lagi untuk dikirimkan sinyal sirine (Alert) ke dasbor.
5. **Lapisan Estetika (Blade Template Views)**: Kebutuhan mendesain lencana (Badge) indikator berwarna Kuning untuk barang rentan dan Merah untuk barang kedaluwarsa di antarmuka Detail Produk.

Kesimpulannya, Analisis Dampak meminimalisir kemungkinan bahwa penambahan sebiji variabel baru malah menjadi blunder sistemik di titik modul lain.
