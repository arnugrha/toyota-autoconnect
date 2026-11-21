# Toyota AutoConnect — Integrated Dealer & After-Sales Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Midtrans-Snap_Gateway-002B49?style=for-the-badge" alt="Midtrans">
  <img src="https://img.shields.io/badge/Spatie-RBAC-F59E0B?style=for-the-badge" alt="Spatie RBAC">
  <img src="https://img.shields.io/badge/Tests-173_Passed-success?style=for-the-badge" alt="Tests">
</p>

---

## 📌 Tentang Proyek

**Toyota AutoConnect** adalah sistem informasi manajemen dan operasional terintegrasi untuk dealer resmi Toyota. Aplikasi ini mendigitalkan seluruh siklus bisnis otomotif secara *end-to-end*, menghubungkan calon pembeli, pelanggan setia, staf penjualan, teknisi bengkel resmi, hingga jajaran manajemen eksekutif dalam satu platform berbasis web.

### Fitur & Modul Utama:
1. **Showroom Digital & Katalog Mobil**: Filter kategori (SUV, MPV, Sedan, Hybrid, GR Sport), spesifikasi teknis, dan harga OTR per wilayah.
2. **Pemesanan Kendaraan (SPK Online)**: Formulir pemesanan resmi, alokasi unit fisik, dan integrasi uang tanda jadi (*booking fee*).
3. **Simulasi Kredit & Multi-Leasing**: Perhitungan DP dinamis, tenor 1–5 tahun, dan komparasi rekanan resmi (ACC, TAF, OTO).
4. **Payment Gateway (Midtrans Snap)**: Pembayaran booking fee via Virtual Account Bank (BCA, Mandiri, BRI) & QRIS dengan auto-reconciliation webhook.
5. **After-Sales & Booking Servis**: Sistem reservasi berbasis kuota slot waktu per jam (08:00–15:00 WIB, Senin–Sabtu) untuk mencegah penumpukan bengkel.
6. **Work Order (WO) 9 Tahapan**: Alur kerja teknisi transparan mulai dari inspeksi Service Advisor, pengerjaan mekanik, suku cadang asli TMO, QC Foreman, hingga serah terima unit.
7. **CRM & Pipeline Prospek (Leads)**: Pelacakan *sales funnel* (*New, Contacted, Qualified, Proposal, Won/Lost*) dan riwayat aktivitas komunikasi sales.
8. **Manajemen Inventaris**: Pengendalian stok mobil per cabang dealer, mutasi stok fisik, dan peringatan batas minimum.
9. **Notification Center Terpusat**: Notifikasi *in-app* pada navbar, pengiriman email otomatis, dan pencatatan log notifikasi WhatsApp.
10. **Executive Analytics & Business Intelligence (BI)**: Dashboard metrik KPI omzet penjualan, rasio konversi deal, utilitas bengkel, dan ekspor laporan CSV/cetak.
11. **Master Data & Audit Trail**: Pengelolaan multi-cabang, parameter global, serta pencatatan forensik mutasi data (*old vs new values*, IP, user-agent).
12. **Role-Based Access Control (RBAC)**: Spatie Permission dengan 10 Role pengguna hierarkis dan 56 Hak Akses terisolasi.

---

## 💻 Kebutuhan Sistem (Prerequisites)

Sebelum menjalankan aplikasi, pastikan komputer/server Anda telah terpasang:

- **PHP**: Versi **8.3** atau **8.4+**
  - Ekstensi PHP yang wajib aktif: `pdo`, `pdo_mysql` / `pdo_sqlite`, `bcmath`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `zip`, `gd`, `intl`, `fileinfo`.
- **Composer**: Versi **2.x+**
- **Node.js & NPM**: Node.js **>= 18.x** dan NPM **>= 9.x**
- **Database Server**: **MySQL >= 8.0** / **MariaDB >= 10.4** (atau **SQLite 3**)
- **Git**: Versi terbaru

---

## 🚀 Panduan Instalasi (Langkah demi Langkah)

Ikuti langkah-langkah di bawah ini untuk mengklon dan menjalankan proyek dari awal hingga dapat diakses di browser:

### 1. Klon Repositori
Buka terminal / command prompt, lalu clone repositori ini ke komputer Anda:
```bash
git clone https://github.com/username/dealer-toyota.git
cd dealer-toyota
```

### 2. Pasang Dependensi PHP (Composer)
Jalankan perintah berikut untuk mengunduh seluruh library PHP yang diperlukan:
```bash
composer install
```

### 3. Buat File Konfigurasi Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:

* **Di Linux / macOS / Git Bash:**
  ```bash
  cp .env.example .env
  ```
* **Di Windows PowerShell:**
  ```powershell
  copy .env.example .env
  ```

### 4. Generate Application Encryption Key
Buat kunci enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

### 5. Konfigurasi Database
Buka file `.env` menggunakan teks editor (VS Code, Notepad, dll.), lalu sesuaikan koneksi database Anda:

#### Opsi A: Menggunakan MySQL (Disarankan untuk Laragon / XAMPP)
1. Buat database baru di MySQL (misalnya melalui phpMyAdmin atau HeidiSQL) dengan nama `dealer_toyota`:
   ```sql
   CREATE DATABASE dealer_toyota CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Sesuaikan konfigurasi di file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=dealer_toyota
   DB_USERNAME=root
   DB_PASSWORD=
   ```

#### Opsi B: Menggunakan SQLite (Paling Cepat / Tanpa Perlu Setup MySQL)
1. Jika ingin menggunakan SQLite, sesuaikan konfigurasi di `.env`:
   ```env
   DB_CONNECTION=sqlite
   ```
2. Buat file database SQLite kosong jika belum ada:
   * **Linux / macOS:** `touch database/database.sqlite`
   * **Windows PowerShell:** `New-Item -ItemType File -Force -Path database/database.sqlite`

### 6. Jalankan Migrasi & Seeder Database
Perintah ini akan membuat seluruh tabel, mengisi data master mobil, cabang dealer, skema kredit, jenis servis, peran RBAC, dan akun login default:
```bash
php artisan migrate --seed
```
> **Catatan Penting**: Seeder (`--seed`) wajib dijalankan agar akun Super Admin, Sales, Service Advisor, dan Customer otomatis terbuat.

### 7. Buat Symbolic Link Penyimpanan Berkas (Storage Link)
Hubungkan folder penyimpanan publik agar foto katalog kendaraan dan avatar pengguna dapat ditampilkan:
```bash
php artisan storage:link
```

### 8. Pasang Dependensi Frontend & Kompilasi Aset (Vite & Tailwind)
Jalankan perintah berikut untuk memasang paket Node.js dan mengompilasi aset antarmuka:
```bash
npm install
npm run build
```
*(Opsional: Jika Anda sedang dalam mode pengembangan aktif dan ingin live-reload, Anda dapat menjalankan `npm run dev` pada terminal terpisah).*

### 9. Jalankan Server Lokal (Local Development Server)
Jalankan server aplikasi Laravel:
```bash
php artisan serve
```
Aplikasi kini siap diakses melalui browser di: **`http://localhost:8000`**

---

## 🔐 Akun Demo & Kredensial Login Bawaan

Database seeder telah menyiapkan akun bawaan untuk menguji berbagai peran pengguna dalam sistem:

| Peran (Role) | Email Login | Password | Akses Halaman |
|---|---|---|---|
| **Super Admin** | `superadmin@toyota-autoconnect.id` | `password123` | Dashboard Lengkap, Master Data, Audit Trail & Pengaturan |
| **Dealer Manager** | `dealermanager@toyota-autoconnect.id` | `password123` | Manajemen Operasional Cabang & BI Analytics |
| **Sales Manager** | `salesmanager@toyota-autoconnect.id` | `password123` | Pipeline Penjualan, SPK & Laporan Sales |
| **Sales Executive** | `salesexec@toyota-autoconnect.id` | `password123` | Manajemen Prospek Leads & Test Drive |
| **CRM Officer** | `crmofficer@toyota-autoconnect.id` | `password123` | Customer Relationship & Tiket Bantuan |
| **Customer (Pelanggan)** | `customer@toyota-autoconnect.id` | `password123` | Customer Portal, Garasi Mobil, Booking Servis & SPK |

### 🌐 Pemisahan Rute Login Sistem:
Untuk alasan keamanan dan isolasi hak akses (*defense-in-depth*):
* **Portal Pelanggan (Customer Login)**: [`http://localhost:8000/login`](http://localhost:8000/login)
* **Portal Staf & Manajemen (Staff/Admin Login)**: [`http://localhost:8000/admin/login`](http://localhost:8000/admin/login)

---

## 💳 Konfigurasi Tambahan (Opsional)

### 1. Midtrans Payment Gateway
Jika ingin menguji transaksi nyata via Midtrans Sandbox:
1. Daftar akun di [Midtrans Sandbox](https://dashboard.sandbox.midtrans.com/).
2. Salin Server Key dan Client Key ke file `.env`:
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_IS_SANITIZED=true
   MIDTRANS_IS_3DS=true
   ```

### 2. Notifikasi Email & WhatsApp
Secara default, notifikasi email menggunakan driver `log` (tersimpan di file `storage/logs/laravel.log`) dan WhatsApp menggunakan mode `mock`. Jika ingin menghubungkan SMTP email nyata (seperti Mailtrap atau Gmail):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan rangkaian pengujian otomatis lengkap (Feature & Unit Tests) yang mencakup seluruh 12 tahap pengembangan:

```bash
php artisan test
```

* **Status Pengujian**: **173 tests passed (100%)**, **775 assertions**, **0 failed**, **0 errors**.

---

## 🛠️ Perintah Berguna (Useful Artisan Commands)

```bash
# Membersihkan cache aplikasi, konfigurasi, dan rute
php artisan optimize:clear

# Menjalankan migrasi ulang dari awal beserta seeder (Reset Database)
php artisan migrate:fresh --seed

# Melihat daftar rute terdaftar
php artisan route:list

# Membuka konsol interaktif Laravel Tinker
php artisan tinker
```

---

## ❓ Penyelesaian Masalah Umum (Troubleshooting)

1. **`No application encryption key has been specified`**
   * Solusi: Jalankan `php artisan key:generate`.
2. **`Vite manifest not found` atau tampilan CSS berantakan**
   * Solusi: Jalankan `npm install` lalu `npm run build`.
3. **`SQLSTATE[HY000] [1049] Unknown database`**
   * Solusi: Pastikan database MySQL dengan nama yang tertera di `.env` sudah Anda buat di MySQL server Anda.
4. **`Permission denied` pada direktori `storage` atau `bootstrap/cache` (Linux/macOS)**
   * Solusi: Jalankan `chmod -R 775 storage bootstrap/cache`.
5. **Gambar / Logo tidak muncul di web**
   * Solusi: Jalankan `php artisan storage:link`.

---

## 📄 Lisensi & Kontributor

Aplikasi ini dikembangkan sebagai bagian dari proyek **Toyota AutoConnect (AHA Project)**.  
Dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
