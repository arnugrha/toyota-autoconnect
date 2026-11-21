# PANDUAN & NASKAH PRESENTASI KELAS
## Proyek: Toyota AutoConnect (Sistem Dealer Terpadu)
**File Presentasi**:
- [`Presentasi_Toyota_AutoConnect_Update.pptx`](file:///d:/Project/dealer-toyota/Presentasi_Toyota_AutoConnect_Update.pptx) *(Versi terbaru dengan flowchart standar ANSI/ISO)*
- [`Presentasi_Toyota_AutoConnect.pptx`](file:///d:/Project/dealer-toyota/Presentasi_Toyota_AutoConnect.pptx)

**Diagram Flowchart Standar (ANSI/ISO Notation)**:
- [`flowchart_pengajuan_servis.png`](file:///d:/Project/dealer-toyota/docs/flowcharts/flowchart_pengajuan_servis.png)
- [`flowchart_pemesanan_kendaraan.png`](file:///d:/Project/dealer-toyota/docs/flowcharts/flowchart_pemesanan_kendaraan.png)

> **Catatan Penggunaan Notasi Flowchart**:
> - 🟢 **Oval (Pink)**: Terminator (`Start / Mulai` dan `End / Selesai`).
> - 🔷 **Jajar Genjang / Parallelogram (Biru Muda)**: Operasi Input / Output data (Input pilihan mobil, terima kode booking, cetak faktur, serah kunci).
> - 🟨 **Persegi Panjang / Rectangle (Kuning Lembut)**: Operasi Proses Komputasi / Tindakan Staf (Validasi AJAX kuota slot, buka Work Order, eksekusi teknisi, QC foreman).
> - 🔶 **Belah Ketupat / Diamond (Oranye Muda)**: Keputusan / Decision Gate (`Slot Waktu Tersedia?`, `Konfirmasi Booking?`, `Ada Part Tambahan?`) dengan percabangan **Yes (Ya)** dan **No (Tidak)**.
> - ⭕ **Lingkaran 'X'**: Junction Connector perpindahan antar tahap kolom tanpa garis bertumpuk.

---

## 🎯 Struktur & Estimasi Waktu Presentasi (Total: 15–20 Menit)

| No. Slide | Topik Bahasan | Durasi Ideal | Poin Kunci yang Harus Ditekankan |
|---|---|---|---|
| **Slide 1** | Judul & Pengantar Tim | 1 Menit | Salam pembuka, perkenalan tim pengembang, dan pengantar tema sistem otomotif modern. |
| **Slide 2** | Latar Belakang & Gambaran Umum | 2 Menit | Masalah silo data konvensional vs solusi ekosistem terpadu Toyota AutoConnect. |
| **Slide 3** | Arsitektur & Tech Stack | 2 Menit | Laravel 13, PHP 8.4+, Spatie RBAC (10 Roles & 56 Hak Akses), Midtrans Gateway, Tailwind CSS. |
| **Slide 4** | Peta 12 Modul Aplikasi | 2 Menit | Menyebutkan tahapan pengembangan dari Showroom, CRM, Finansial, After-Sales, hingga Testing. |
| **Slide 5** | Penjualan & Simulasi Finansial | 1.5 Menit | Filter spesifikasi mobil, kalkulator kredit multi-leasing, dan pembayaran uang tanda jadi. |
| **Slide 6** | **Flowchart Pemesanan Kendaraan** | 2.5 Menit | Menjelaskan alur gambar flowchart: peran Pelanggan vs peran Admin/Sales/Finance & Webhook Midtrans. |
| **Slide 7** | Modul Bengkel & After-Sales | 1.5 Menit | Sistem booking kuota slot per jam (08:00-15:00), 9 tahapan pengerjaan WO resmi, dan CS helpdesk. |
| **Slide 8** | **Flowchart Pengajuan Servis** | 2.5 Menit | Menjelaskan alur gambar flowchart: peran Pelanggan vs peran Service Advisor, Mekanik & Kasir. |
| **Slide 9** | CRM & Pipeline Leads | 1 Menit | Tahapan prospek (*New, Contacted, Qualified, Proposal, Won, Lost*) & tracking performa sales. |
| **Slide 10** | Executive BI Dashboard & Laporan | 1 Menit | Metrik omzet OTR, rasio konversi deal, utilitas bengkel, dan ekspor laporan CSV/cetak. |
| **Slide 11** | Master Data & Audit Trail | 1 Menit | Keamanan audit trail (IP, User Agent, Diff JSON old/new values) & multi-cabang dealer. |
| **Slide 12** | Keamanan & Pengujian Otomatis | 1.5 Menit | Pemisahan rute login, rate limiting, anti-N+1 query, serta 173 automated tests (100% lulus). |
| **Slide 13** | Kesimpulan & Future Roadmap | 1 Menit | Dampak efisiensi sistem, rencana mobile apps, dan telematika IoT OBD-II. |
| **Slide 14** | Penutup & Sesi Q&A | Sisa Waktu | Membuka kesempatan tanya jawab dengan dosen dan rekan mahasiswa. |

---

## 🎙️ Naskah & Panduan Berbicara per Slide (Speaker Notes)

### Slide 1: Cover Presentasi
> *"Selamat pagi/siang kepada Bapak/Ibu Dosen dan rekan-rekan sekalian. Pada kesempatan ini, kami mempresentasikan proyek pengembangan aplikasi bernama **Toyota AutoConnect**, sebuah Sistem Informasi dan Operasional Terpadu untuk Dealer Resmi Toyota. Aplikasi ini kami bangun menggunakan fondasi **Laravel 13** dan **PHP 8.4+**, dengan integrasi payment gateway **Midtrans Snap**, kontrol hak akses **Spatie RBAC**, serta desain antarmuka modern menggunakan **Tailwind CSS**."*

---

### Slide 2: Latar Belakang Masalah & Gambaran Umum
> *"Dalam operasional dealer otomotif konvensional, sering kali terjadi pemisahan data antara divisi penjualan mobil baru dan bengkel purna jual. Masalah yang sering dihadapi adalah: proses pemesanan kendaraan yang birokratis dengan formulir kertas, antrean bengkel yang tidak terprediksi karena pelanggan datang tanpa jadwal teratur, serta minimnya transparansi status bagi pelanggan.  
Toyota AutoConnect hadir sebagai solusi digital menyeluruh yang menyatukan seluruh interaksi pelanggan, staf penjualan, teknisi bengkel, dan jajaran manajemen dalam satu platform waktu nyata (real-time)."*

---

### Slide 3: Arsitektur Sistem & Tech Stack
> *"Dari sisi teknologi, kami menggunakan standar enterprise:
> 1. **Backend**: Laravel 13 dan PHP 8.4+ dengan *strict typing*, *constructor property promotion*, serta arsitektur Service Pattern.
> 2. **Database & RBAC**: Relational Database yang diperkuat oleh Spatie Laravel Permission dengan 10 Role pengguna (mulai dari Super Admin, Branch Manager, Sales, Service Advisor, Mekanik, Kasir, hingga Pelanggan) dan 56 Hak Akses terisolasi.
> 3. **Frontend**: Blade Templating dan Tailwind CSS responsif dengan fitur scroll horizontal di seluruh tabel data.
> 4. **Integrasi Eksternal**: Midtrans Snap Payment Gateway untuk penerimaan tanda jadi instan, serta Notification Center terpusat multi-channel."*

---

### Slide 4: Cakupan 12 Modul Inti Aplikasi
> *"Proyek ini mencakup 12 modul lengkap yang dikembangkan secara bertahap: mulai dari Showroom Publik, Autentikasi Multi-Role, Customer Portal, CRM Leads, Manajemen Inventaris Stok Cabang, Test Drive & SPK, Simulasi Kredit & Midtrans, Booking Servis & Work Order 9 Tahap, Notification Center, Executive BI Analytics, Master Data & Audit Trail, hingga Security Hardening dan Automated Testing."*

---

### Slide 5: Modul Penjualan Kendaraan & Finansial
> *"Pada modul penjualan, pelanggan dapat mengeksplorasi katalog mobil resmi Toyota, membandingkan spesifikasi, serta menjalankan simulasi kredit interaktif dengan memilih uang muka dan tenor 1 hingga 5 tahun dari berbagai mitra leasing resmi (seperti ACC, TAF, OTO). Uang tanda jadi atau booking fee resmi dapat dibayarkan langsung secara digital melalui Midtrans Snap tanpa perlu mengunggah bukti transfer manual."*

---

### Slide 6: Flowchart Pemesanan Kendaraan (Diagram Gambar)
> *(Tunjukkan gambar diagram pada layar)*  
> *"Mari kita perhatikan flowchart kolaborasi dua peran pada slide ini:  
> - **Dari Sisi Pelanggan**: Alur dimulai dari eksplorasi katalog, simulasi kredit, pengisian formulir SPK dan upload identitas, lalu melakukan pembayaran booking fee melalui Midtrans. Begitu pembayaran berhasil dalam batas 24 jam, sistem memicu notifikasi konfirmasi.
> - **Dari Sisi Dealer (Admin/Sales/Finance)**: Sistem menerima webhook Midtrans secara otomatis, mencatat SPK di dashboard, mengunci alokasi nomor rangka unit fisik di inventaris cabang, menerbitkan Surat Penawaran Resmi, hingga pelaksanaan Pre-Delivery Inspection (PDI) dan serah terima unit beserta BASTK ke pelanggan."*

---

### Slide 7: Modul Layanan Purna Jual & Bengkel (After-Sales)
> *"Pada layanan purna jual, kami menghadirkan sistem booking servis berbasis kuota slot waktu per jam (mulai 08:00 hingga 15:00 WIB, Senin-Sabtu). Ini menjamin tidak terjadi overcapacity di bengkel. Selain itu, pengerjaan servis dipandu oleh **9 Tahapan Work Order (WO)** resmi yang transparan, dilengkapi modul Customer Service helpdesk berbasis tiket aduan."*

---

### Slide 8: Flowchart Pengajuan & Pengerjaan Servis (Diagram Gambar)
> *(Tunjukkan gambar diagram pada layar)*  
> *"Pada diagram purna jual ini:  
> - **Dari Sisi Pelanggan**: Pemilik kendaraan memilih mobil dari garasi akunnya, memilih cabang bengkel, menentukan jenis servis (misal Servis Berkala atau Ganti Oli TMO Synthetic), memilih jam kedatangan yang masih tersedia, dan mencatat keluhan fisik kendaraan.
> - **Dari Sisi Tim Bengkel**: Service Advisor (SA) memvalidasi ketersediaan stall, melakukan walk-around inspection saat mobil tiba, dan membuka Work Order (WO). Mekanik mengeksekusi perbaikan dan suku cadang asli Toyota. Jika ada temuan kerusakan baru di luar pesanan awal, SA melakukan konfirmasi persetujuan biaya ke pelanggan terlebih dahulu sebelum melanjutkan. Setelah lolos Quality Control oleh Foreman, kasir menerbitkan faktur resmi, dan unit diserahterimakan kepada pelanggan."*

---

### Slide 9: CRM & Pipeline Prospek Penjualan
> *"Modul CRM membantu dealer mengelola prospek calon pembeli secara sistematis melalui 5 tahapan sales funnel: New Leads, Contacted, Qualified, Proposal Sent, hingga Deal Won atau Lost. Seluruh interaksi sales (telepon, meeting, penawaran harga) tercatat kronologis, memudahkan evaluasi conversion rate tenaga penjual."*

---

### Slide 10: Executive Analytics & Business Intelligence
> *"Bagi level pimpinan eksekutif dan Branch Manager, tersedia Executive BI Dashboard yang menyajikan grafik tren penjualan bulanan, rasio utilitas bengkel harian, dan metrik pendapatan OTR per cabang. Data dapat difilter fleksibel dan diekspor ke berkas CSV atau format cetak resmi."*

---

### Slide 11: Master Data Management & Audit Trail Keamanan
> *"Aplikasi mendukung multi-cabang dealer di berbagai kota, konfigurasi jam kerja, dan harga servis. Untuk menjamin integritas data dan mencegah kecurangan, kami mengimplementasikan **Audit Trail** yang mencatat setiap mutasi data kritis, lengkap dengan User ID, timestamp, alamat IP, user-agent, serta komparasi nilai sebelum dan sesudah perubahan (diff old/new values)."*

---

### Slide 12: Keamanan Sistem & Pengujian Otomatis
> *"Keamanan aplikasi diperkuat dengan pemisahan rute login pelanggan dan staf operasional, rate limiting pencegah serangan brute-force, dan proteksi CSRF. Seluruh query database telah dioptimalkan dengan Eager Loading guna mengeliminasi masalah N+1 queries.  
Sebagai bukti keandalan, kami membangun pengujian otomatis: **173 test cases** dengan **775 assertions** yang semuanya **100% lulus tanpa kegagalan (0 errors, 0 failures)**."*

---

### Slide 13: Kesimpulan & Rencana Pengembangan
> *"Kesimpulannya, Toyota AutoConnect berhasil mendigitalkan ekosistem dealer Toyota secara terpadu, memangkas birokrasi pemesanan, dan meniadakan antrean tak teratur di bengkel.  
Untuk pengembangan selanjutnya, sistem ini siap dihubungkan dengan aplikasi mobile native, telematika IoT OBD-II Toyota T-Intouch untuk deteksi kendala mesin otomatis, serta AI Predictive Maintenance."*

---

### Slide 14: Penutup & Tanya Jawab
> *"Demikian presentasi dari kelompok kami. Terima kasih atas perhatian Bapak/Ibu Dosen dan rekan-rekan. Kami dengan senang hati menyambut pertanyaan, tanggapan, maupun saran."*

---

## 💡 Antisipasi Pertanyaan Dosen & Jawaban Teknis (Q&A Preparation)

#### Q1: *"Bagaimana sistem menangani jika dua pelanggan memesan slot servis di jam yang sama pada detik yang bersamaan (Race Condition / Overbooking)?"*
* **Jawaban**:  
  *"Sistem menggunakan validasi dinamis di layer service dan database transaction (`DB::transaction`). Pada saat formulir booking disubmit, sistem menghitung ulang jumlah reservasi berstatus aktif pada cabang dan tanggal-jam tersebut dengan `lockForUpdate()`. Jika kuota maksimum (misal 3 slot per jam) telah tercapai, permintaan kedua akan ditolak secara anggun dengan pesan informatif agar pelanggan memilih slot lain, sehingga overcapacity berhasil dicegah."*

#### Q2: *"Bagaimana cara kerja integrasi pembayaran Midtrans dalam memvalidasi status SPK?"*
* **Jawaban**:  
  *"Kami menggunakan Midtrans Snap Core API. Saat pelanggan membuat SPK, sistem meng-generate Snap Token. Pelanggan membayar via Virtual Account atau QRIS. Setelah pembayaran selesai, Midtrans mengirimkan HTTP POST Webhook ke endpoint rute aplikasi kami. Endpoint tersebut memverifikasi keaslian webhook menggunakan SHA-512 Signature Key (kombinasi Order ID, Status Code, Gross Amount, dan Server Key). Jika valid, status SPK otomatis diperbarui menjadi 'Confirmed' tanpa intervensi manual staf."*

#### Q3: *"Mengapa rute login antara pelanggan dan admin dibedakan, padahal menggunakan tabel users yang sama?"*
* **Jawaban**:  
  *"Pemisahan rute login (`/login` untuk customer dan `/admin/login` untuk staf/admin) merupakan praktik *defense-in-depth* dan isolasi konteks UX. Meskipun bermuara pada entitas user yang sama, pemisahan rute memungkinkan penerapan middleware rate limiting yang lebih ketat pada rute staf internal, isolasi redirection otomatis sesuai role Spatie RBAC, serta mencegah pengguna biasa mengakses dashboard manajerial secara keliru."*

#### Q4: *"Bagaimana pengujian otomatis (automated testing) dilakukan dalam proyek ini?"*
* **Jawaban**:  
  *"Kami menulis 173 file skenario pengujian fitur dan unit menggunakan PHPUnit dan Laravel TestSuite. Cakupan pengujian mencakup autentikasi multi-role, skenario simulasi kredit, kalkulasi biaya servis berkala, pengujian alur Work Order 9 tahapan, webhook Midtrans, hingga audit trail. Seluruh 173 pengujian telah dijalankan dan 100% lulus (775 assertions)."*

