# SmartStock Pro - Tools dan Framework

- **Versi Dokumen**: 1.0
- **Terakhir Diperbarui**: Mei 2026
- **Proyek**: SmartStock Pro, Sistem Manajemen Inventaris
- **Klien**: PT Maju Bersama Digital

---

## Daftar Isi

1. [Pemilihan Tools dan Framework Utama](#pemilihan-tools-dan-framework-utama)
2. [Analisis Skalabilitas Aplikasi](#analisis-skalabilitas-aplikasi)
3. [Dokumentasi Library Pihak Ketiga](#dokumentasi-library-pihak-ketiga)

---

## 1. Pemilihan Tools dan Framework Utama

Pemilihan perangkat kerja telah disesuaikan dengan profil perusahaan distribusi yang membutuhkan stabilitas tinggi.

### 1.1 Laravel 11 (PHP 8.3)
Laravel memiliki ekosistem yang sangat matang untuk aplikasi skala enterprise (terdapat modul mandiri untuk Routing, ORM, Queue, Middleware, Job Scheduling). Fitur keamanan bawaan seperti proteksi CSRF (Cross Site Request Forgery), pencegahan injeksi SQL, dan penyaringan XSS (Cross Site Scripting) sangat krusial dan secara bawaan (out of the box) memenuhi spesifikasi keamanan ketat BNSP.

### 1.2 PostgreSQL 16
Sistem manajemen inventaris sangat bergantung pada integritas data (ACID compliance) dan performa konkurensi tinggi (menghindari fenomena balapan data atau Race Condition ketika dua gudang menarik stok bersamaan). PostgreSQL sangat unggul dalam menangani kunci baris (row level locking) pada saat sistem menjalankan komputasi algoritma FIFO yang kompleks.

### 1.3 Tailwind CSS & Alpine.js
Kombinasi ini memberikan antarmuka pengguna yang sangat responsif, modern, tanpa menbebani gawai klien. Tidak ada muatan kerangka kerja Javasript tebal (seperti React atau Vue) yang harus dimuat. Pendekatan minimalis khas New York Style dicapai sempurna menggunakan kelas utilitas yang dapat disesuaikan sesuka hati, sangat cocok untuk dasbor admin yang mengutamakan rasio muat cepat.

### 1.4 Docker & Docker Compose
Infrastruktur ini menjamin konsistensi absolut antara mesin lokal pemrogram (Localhost) dan mesin produksi. Tidak akan ada lagi alasan klise "Ini berjalan kok di komputer saya!". Kontainerisasi juga mengisolasi dependensi agar tidak bertabrakan dengan sistem operasi induk.

## 2. Analisis Skalabilitas Aplikasi

Sistem SmartStock Pro tidak hanya dibuat untuk melayani data masa kini, tetapi arsitekturnya disusun agar siap membengkak secara eksponensial (Scalability).

### 2.1 Skalabilitas Kinerja (Offloading)
Pekerjaan berat seperti ekspor lembar lajur (Excel), pembuatan laporan grafik PDF, dan pengiriman pesan surat elektronik tidak dieksekusi secara sinkronus. Permintaan dikerjakan oleh Redis Queue dan diselesaikan oleh barisan Worker di belakang layar (Background Jobs), mempertahankan latensi halaman utama yang super kilat bagi pengguna di berbagai penjuru kota.

### 2.2 Efisiensi Kueri (Eager Loading & Pagination)
Antarmuka sistem selalu menggunakan Pagination di tingkat basis data. Ini berarti tidak peduli ada sejuta transaksi pun, basis data hanya melempar 10 baris ke RAM server pada setiap permintaan halaman. Lebih lanjut, relasi entitas ditangani dengan teknik Eager Loading untuk memastikan tidak ada query anak (N+1 query) yang berjalan di dalam perulangan loop.

### 2.3 Horizontal vs Vertical Scaling
Peladen monolit lazimnya hanya bisa ditingkatkan kinerjanya dengan mengganti CPU (Vertical Scaling). Mengingat SmartStock Pro berjalan di dalam Docker dan aplikasi murni menganut desain Stateless (Sesi disimpan di Basis Data), manajemen kelak dapat dengan bebas melakukan Horizontal Scaling (Menambahkan node peladen web ganda) yang dijembatani oleh Load Balancer, tanpa takut sesi pengguna putus.

## 3. Dokumentasi Library Pihak Ketiga

Demi mempercepat masa pengembangan sembari mematuhi batasan hukum sumber terbuka, tiga perpustakaan (library) esensial disuntikkan ke dalam sistem:

### 3.1 Chart.js (v4.4)
* **Lisensi**: MIT License
* **Fungsi**: Bertanggung jawab penuh melukis visualisasi kurva pergerakan transaksi harian ke dalam format garis (Line Chart). Ini menjadi kunci kemudahan para petinggi tingkat C (C Level) memahami kesehatan inventaris dari Dasbor mereka secara waktu riil (Real time).

### 3.2 Leaflet.js (v1.9)
* **Lisensi**: BSD 2 Clause
* **Fungsi**: Memuat peta persebaran titik spasial dari 5 gudang inti tanpa harus membayar beban langganan peta berbayar. Ini memanfaatkan lapisan peta gratis OpenStreetMap dengan kemampuan geser dan perbesar layar interaktif.

### 3.3 Maatwebsite Excel (v3.1)
* **Lisensi**: MIT License
* **Fungsi**: Mesin perantara kuat yang membaca format usang file CSV maupun XLSX para administrator gudang, menyaring datanya secara leksikal, untuk kemudian diinjeksikan dalam jumlah masif (Batch Import) ke PostgreSQL melalui lapisan layanan (Service Layer) Laravel.
