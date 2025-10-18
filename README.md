<div align="center">
  <img src="https://raw.githubusercontent.com/fairuzaldaperkasa/mpti_travel/main/assets/images/logompti.png" alt="Vacationland Logo" width="170"/>
  <h1><b>Vacationland Tour & Travel</b></h1>
  <p>
    <b>Platform Website Agen Travel Dinamis Berbasis PHP Native, dirancang untuk pengelolaan konten yang mudah dan pengalaman pengguna yang modern.</b>
  </p>
  <p>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
    <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
    <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  </p>
</div>

**Vacationland** adalah proyek sistem informasi untuk agen travel yang dibangun dari dasar menggunakan PHP native dan Vanilla JavaScript. Proyek ini menyediakan antarmuka yang bersih bagi pengguna untuk menjelajahi paket wisata, serta panel admin yang komprehensif bagi pengelola untuk mengontrol setiap aspek konten website secara dinamis.

## ✨ Fitur Unggulan

Proyek ini dilengkapi dengan serangkaian fitur yang membuatnya menjadi solusi lengkap untuk agen travel.

### Untuk Pengguna (Frontend)
- **🎨 Tampilan Modern & Responsif:** Desain antarmuka yang menarik dan beradaptasi dengan baik di berbagai ukuran layar, dari desktop hingga mobile.
- **✈️ Katalog Paket Dinamis:** Daftar paket wisata dimuat secara asinkron dari database, memastikan data selalu yang terbaru.
- **📄 Halaman Detail Interaktif:** Halaman detail paket yang kaya informasi, dilengkapi dengan tab untuk *overview*, galeri, *itinerary*, fitur, dan *highlights*.
- **🌍 Dukungan Multi-bahasa:** Website mendukung Bahasa Indonesia (id) dan Inggris (en) yang dapat diganti oleh pengguna. Sistem terjemahan berbasis JSON ini mencakup semua teks antarmuka.
- **📱 Tombol Kontak Dinamis:** Tombol WhatsApp dan telepon mengambil nomor tujuan langsung dari database, memudahkan pengguna untuk terhubung.
- **💳 Metode Pembayaran:** Menampilkan berbagai metode pembayaran yang didukung, yang juga dikelola secara dinamis dari admin panel.
- **📧 Berlangganan Newsletter:** Pengguna dapat mendaftarkan email mereka untuk mendapatkan pembaruan dan promo.

### Untuk Administrator (Backend)
- **🔐 Panel Admin Aman:** Halaman admin dilindungi oleh sistem login berbasis sesi dengan password yang di-hash.
- **📊 Dashboard Informatif:** Halaman utama admin menampilkan statistik kunci seperti total paket, jumlah foto, dan tanggal paket terbaru ditambahkan.
- **➕ Manajemen Paket (CRUD):**
    - **Tambah Paket:** Form komprehensif untuk membuat paket baru, termasuk detail dasar, harga, durasi, *highlights*, *inclusions*, *exclusions*, dan *itinerary* harian.
    - **Edit Paket:** Mengubah semua detail paket yang sudah ada, termasuk opsi untuk mengganti foto.
    - **Hapus Paket:** Menghapus paket beserta semua file foto terkait dari server.
- **🖼️ Manajemen Galeri Foto:**
    - Unggah foto tambahan untuk setiap paket melalui modal interaktif.
    - Tambah, edit, dan hapus *caption* untuk setiap foto di galeri.
    - Hapus foto dari galeri satu per satu.
- **⚙️ Pengaturan Website Dinamis:** Halaman khusus untuk mengubah informasi vital website seperti nomor WhatsApp, email, alamat, dan template pesan WhatsApp tanpa perlu menyentuh kode.
- **📋 Manajemen Booking:** Mencatat dan melihat riwayat pemesanan yang masuk, lengkap dengan detail pelanggan, status pembayaran, dan status booking.
- **💳 Manajemen Metode Pembayaran:** Menambah, mengedit, atau menonaktifkan metode pembayaran (seperti transfer bank, e-wallet) yang akan ditampilkan di frontend.
- **🔑 Ubah Password:** Admin dapat mengubah password login mereka sendiri demi keamanan.

## 🛠️ Teknologi yang Digunakan
- **Backend:** PHP 8+ (Native), MySQL / MariaDB
- **Frontend:** HTML5, CSS3 (Flexbox, Grid), Vanilla JavaScript (ES6+ Fetch API)
- **Web Server:** Apache (via XAMPP/WAMP)

## 📂 Struktur Proyek

Struktur folder utama proyek ini diorganisir sebagai berikut untuk memisahkan antara logika backend, tampilan frontend, dan aset.
fairuzaldaperkasa/mpti_travel/

├── admin/ # CSS & JS khusus untuk Admin Panel lama

├── assets/ # Aset publik (CSS, JS, gambar, video)

│ ├── css/ # File CSS utama

│ ├── images/ # Gambar untuk UI

│ ├── js/ # File JavaScript utama (termasuk terjemahan)

│ └── video/ # Video untuk hero section

├── BackEnd/ # Semua logika sisi server (PHP)

│ ├── uploads/ # Direktori untuk foto paket yang diunggah

│ │ └── gallery/ # Foto galeri tambahan

│ ├── admin.php # Halaman utama Admin Panel

│ ├── ViewLoginAdmin.php # Skrip login admin

│ ├── get_paket.php # API untuk daftar paket

│ ├── get_package_detail.php # API untuk detail paket

│ ├── paket_travel.sql # File dump database

│ └── ... # File API dan skrip pendukung lainnya

└── FrontEnd/ # Semua file sisi klien (HTML & JS)

├── html/ # Halaman HTML

│ ├── Index.html # Halaman utama

│ ├── package_detail.html

│ └── profile.html

└── js/ # JavaScript khusus halaman

├── package-loader.js

└── package-detail-loader.js
## ⚙️ Panduan Instalasi

Ikuti langkah-langkah ini untuk menjalankan proyek di lingkungan lokal Anda.

1.  **Prasyarat:**
    - Pastikan **XAMPP** (dengan Apache dan MySQL) sudah terpasang.

2.  **Clone Repository:**
    - Clone repository ini ke dalam direktori `htdocs` pada folder instalasi XAMPP Anda.
    ```bash
    cd C:\xampp\htdocs
    git clone [https://github.com/fairuzaldaperkasa/mpti_travel.git](https://github.com/fairuzaldaperkasa/mpti_travel.git) MPTI_TRAVEL
    ```
    - Jika Anda mengunduh sebagai ZIP, ekstrak isinya dan ganti nama foldernya menjadi `MPTI_TRAVEL`.

3.  **Setup Database:**
    - Jalankan layanan Apache dan MySQL dari XAMPP Control Panel.
    - Buka browser dan pergi ke `http://localhost/phpmyadmin`.
    - Buat database baru dengan nama `paket_travel`.
    - Pilih database `paket_travel`, lalu buka tab **Import**.
    - Klik "Choose File" dan pilih file `MPTI_TRAVEL/BackEnd/paket_travel.sql`.
    - Klik tombol **Import** di bagian bawah halaman untuk menjalankan proses impor.

4.  **Konfigurasi Koneksi (Opsional):**
    - Sebagian besar file PHP menggunakan kredensial database default XAMPP (`host: "localhost"`, `user: "root"`, `password: ""`). Jika konfigurasi MySQL Anda berbeda, Anda perlu menyesuaikannya di setiap file PHP yang melakukan koneksi database.

5.  **Jalankan Proyek:**
    - Buka browser dan akses halaman utama proyek:
      `http://localhost/MPTI_TRAVEL/FrontEnd/html/Index.html`

## 🚀 Cara Penggunaan

### Akses Admin Panel
Hampir semua konten website dikelola melalui Admin Panel.

1.  **Buka Halaman Login:**
    `http://localhost/MPTI_TRAVEL/BackEnd/ViewLoginAdmin.php`

2.  **Gunakan Kredensial Default:**
    - **Email:** `admin@vacationland.com` (atau `admin@mptitravel.com`)
    - **Password:** `admin123` (atau `adminganteng16`)

    *Catatan: Kredensial ini mungkin bervariasi tergantung pada data yang ada di file `.sql` Anda. Anda dapat memeriksa tabel `admins` di phpMyAdmin untuk memastikan.*

3.  **Kelola Konten:** Setelah berhasil login, Anda dapat mulai mengelola paket wisata, pengaturan website, melihat riwayat pemesanan, dan fitur lainnya melalui menu navigasi yang tersedia.

## 📡 Endpoint API Utama

Proyek ini menggunakan beberapa endpoint API untuk komunikasi antara frontend dan backend.

-   `GET /BackEnd/get_paket.php`: Mengambil daftar semua paket wisata untuk ditampilkan di halaman utama.
-   `GET /BackEnd/get_package_detail.php`: Mengambil detail lengkap dari satu paket wisata berdasarkan `id`.
-   `GET /BackEnd/get_settings.php`: Mengambil semua konfigurasi website (kontak, sosial media, dll).
-   `GET /BackEnd/get_payment_methods.php`: Mengambil daftar metode pembayaran yang aktif.
-   `POST /BackEnd/admin.php`: Endpoint utama untuk semua aksi dari admin panel (tambah/edit paket, simpan pengaturan, dll).
-   `POST /BackEnd/upload_additional_photos.php`: Mengunggah foto untuk galeri paket.

## 🤝 Kontribusi

Kami sangat terbuka untuk kontribusi! Jika Anda ingin membantu mengembangkan proyek ini, silakan:

1.  **Fork** repository ini.
2.  Buat *branch* baru (`git checkout -b fitur/NamaFitur`).
3.  Lakukan perubahan dan **commit** (`git commit -m 'Menambahkan: NamaFitur'`).
4.  **Push** ke *branch* Anda (`git push origin fitur/NamaFitur`).
5.  Buat **Pull Request**.
