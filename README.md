Tentu, ini draf `README.md` yang menarik dan informatif untuk proyek GitHub Anda, dibuat berdasarkan file-file yang Anda berikan.

-----

````markdown
<div align="center">
  <img src="https://raw.githubusercontent.com/fairuzaldaperkasa/mpti_travel/main/assets/images/logompti.png" alt="Vacationland Logo" width="150"/>
  <h1><b>Vacationland Tour & Travel</b></h1>
  <p>
    <b>Sebuah platform website agen travel dinamis yang dibangun dengan PHP native dan Vanilla JavaScript.</b>
  </p>
  <p>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
    <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
    <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  </p>
</div>

**Vacationland** adalah proyek website travel yang dirancang untuk menjadi portal pemesanan paket wisata yang modern, responsif, dan mudah dikelola. Dilengkapi dengan **Admin Panel** yang lengkap, pengelola dapat dengan mudah mengatur konten paket, galeri, metode pembayaran, hingga melihat riwayat pemesanan.

## ✨ Fitur Utama

- **✈️ Manajemen Paket Wisata (CRUD):** Tambah, lihat, edit, dan hapus paket wisata dengan mudah melalui Admin Panel.
- **🖼️ Galeri Foto Dinamis:** Unggah banyak foto untuk setiap paket, tambahkan caption, dan atur urutannya.
- **💳 Pengaturan Dinamis:** Kelola informasi kontak, nomor WhatsApp, metode pembayaran, dan pengaturan website lainnya langsung dari admin.
- **🌍 Dukungan Multi-bahasa:** Sistem terjemahan JSON untuk mendukung Bahasa Indonesia (id) dan Inggris (en).
- **📋 Riwayat Pemesanan:** Catat dan lihat riwayat pemesanan paket wisata oleh pelanggan.
- **📧 Fitur Newsletter:** Kumpulkan email subscriber dan kirimkan newsletter promo langsung dari Admin Panel.
- **🔐 Otentikasi Admin:** Halaman login yang aman untuk melindungi akses ke Admin Panel.
- **🎨 Frontend Modern & Responsif:** Tampilan yang bersih, modern, dan dapat diakses dengan baik di berbagai perangkat (desktop & mobile).
- **🚀 Pemuatan Konten Asinkron:** Paket wisata dan detailnya dimuat secara dinamis menggunakan Fetch API, memberikan pengalaman pengguna yang cepat.

## 🛠️ Teknologi yang Digunakan

- **Backend:**
  - **PHP 8+** (Native, tanpa framework)
  - **MySQL / MariaDB** untuk database
- **Frontend:**
  - **HTML5**
  - **CSS3** (Flexbox, Grid)
  - **Vanilla JavaScript (ES6+)** untuk interaktivitas dan pemanggilan API
- **Web Server:** Apache (direkomendasikan via XAMPP/WAMP)

## ⚙️ Prasyarat & Instalasi

Pastikan Anda memiliki lingkungan pengembangan web lokal yang sudah terpasang.

1.  **Web Server Lokal:**
    - Unduh dan pasang **[XAMPP](https://www.apachefriends.org/index.html)** atau WAMP.
    - Jalankan modul **Apache** dan **MySQL**.

2.  **Clone Repository:**
    ```bash
    git clone [https://github.com/fairuzaldaperkasa/mpti_travel.git](https://github.com/fairuzaldaperkasa/mpti_travel.git)
    ```
    Atau unduh ZIP dan ekstrak ke direktori `htdocs` di dalam folder instalasi XAMPP Anda.

3.  **Setup Database:**
    - Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    - Buat database baru dengan nama `paket_travel`.
    - Pilih database `paket_travel`, lalu klik tab **Import**.
    - Unggah file `BackEnd/paket_travel.sql` untuk membuat semua tabel yang diperlukan beserta data contoh.

4.  **Jalankan Proyek:**
    - Buka browser dan akses halaman utama:
      `http://localhost/MPTI_TRAVEL/FrontEnd/html/Index.html`

## 📂 Susunan Proyek

````

/MPTI\_TRAVEL
├── admin/
│   ├── css/
│   └── js/
├── assets/
│   ├── css/
│   ├── images/
│   ├── js/
│   └── video/
├── BackEnd/
│   ├── uploads/
│   ├── admin.php         \# Halaman utama Admin Panel
│   ├── get\_paket.php     \# API untuk mengambil daftar paket
│   ├── get\_package\_detail.php \# API untuk detail paket
│   ├── ViewLoginAdmin.php \# Halaman & proses login
│   └── ...               \# File backend lainnya
├── FrontEnd/
│   ├── html/
│   │   ├── Index.html      \# Halaman utama
│   │   ├── package\_detail.html
│   │   └── profile.html
│   └── js/
│       ├── package-loader.js
│       └── package-detail-loader.js
└── README.md

```

## 🚀 Contoh Penggunaan

### Mengakses Website
- **Halaman Utama:** Buka `http://localhost/MPTI_TRAVEL/FrontEnd/html/Index.html` untuk melihat daftar paket wisata.
- **Detail Paket:** Klik tombol "Lihat Detail" pada salah satu paket untuk melihat informasi lengkapnya.

### Mengakses Admin Panel
1.  Buka halaman login admin:
    `http://localhost/MPTI_TRAVEL/BackEnd/ViewLoginAdmin.php`

2.  Gunakan kredensial default untuk login:
    - **Email:** `admin@vacationland.com` atau `admin@mptitravel.com`
    - **Password:** `admin123` atau `adminganteng16`

3.  Setelah login, Anda akan diarahkan ke **Dashboard Admin** dimana Anda dapat mulai mengelola konten website.

    - **Tambah Paket Baru:** Navigasi ke tab "Tambah Paket", isi semua detail, unggah 3-6 foto, lalu simpan.
    - **Edit Paket:** Navigasi ke tab "Paket Travel", lalu klik ikon pensil pada paket yang ingin diubah.

<div align="center">
  <img src="https://raw.githubusercontent.com/fairuzaldaperkasa/mpti_travel/main/Asset/Layout_Awal.png" alt="Tampilan Proyek" width="700"/>
</div>

## 🤝 Kontribusi

Kontribusi Anda sangat kami hargai! Jika Anda ingin berkontribusi pada proyek ini, silakan ikuti langkah-langkah berikut:

1.  **Fork** repository ini.
2.  Buat *branch* baru untuk fitur Anda (`git checkout -b fitur/FiturKeren`).
3.  Lakukan perubahan dan **commit** (`git commit -m 'Menambahkan FiturKeren'`).
4.  **Push** ke *branch* Anda (`git push origin fitur/FiturKeren`).
5.  Buka **Pull Request**.

## 📄 Lisensi

Proyek ini dilisensikan di bawah **Lisensi MIT**. Lihat file `LICENSE` untuk detail lebih lanjut.

---
<div align="center">
  Dibuat dengan ❤️ oleh Tim MPTI
</div>
```
