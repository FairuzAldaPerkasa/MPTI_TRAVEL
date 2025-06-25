# MPTI TRAVEL - Implementasi Nomor WhatsApp Dinamis

## 📱 Fitur yang Diimplementasikan

### 1. **Database Settings**
- Tabel `website_settings` berisi semua konfigurasi kontak:
  - `whatsapp_number`: Nomor WhatsApp untuk booking
  - `phone_number`: Nomor telepon display
  - `email`: Email perusahaan  
  - `whatsapp_message`: Template pesan WhatsApp default
  - `address`, `city`, `province`: Informasi alamat
  - `website_name`, `website_url`: Info website
  - `instagram`: Handle Instagram

### 2. **Backend API**
- `get_settings.php`: API untuk mengambil semua settings dalam format JSON
- `admin.php`: Form untuk mengubah semua settings via admin panel
- `update_settings.php`: Script utilitas untuk update settings

### 3. **Frontend Implementation**

#### **File yang Dimodifikasi:**
- `package-detail-loader.js`: Load settings dinamis untuk tombol WhatsApp/telepon
- `contact-loader.js`: Script universal untuk load kontak di semua halaman
- `Index.html`, `package_detail.html`, `profile.html`: Include contact-loader.js

#### **Fitur Frontend:**
- Tombol WhatsApp dengan nomor dan pesan dinamis
- Footer dengan kontak yang dinamis
- Nomor telepon yang dinamis
- Email dan social media yang dinamis

## 🔧 Cara Penggunaan

### **Untuk Admin:**
1. Login ke Admin Panel: `http://localhost/MPTI_TRAVEL/BackEnd/admin.php`
2. Klik tab "Pengaturan"
3. Ubah nomor WhatsApp, telepon, email, dll
4. Klik "Simpan Pengaturan"
5. Perubahan langsung berlaku di seluruh website

### **Format Nomor WhatsApp:**
- Gunakan format: `6281234567890` (tanpa + atau spasi)
- Sistem akan otomatis memformat untuk display

## 📋 File yang Terlibat

### **Backend:**
- `BackEnd/get_settings.php` - API settings
- `BackEnd/admin.php` - Form admin (section settings)
- `BackEnd/create_settings_table.php` - Setup database
- `BackEnd/update_settings.php` - Update utilitas

### **Frontend:**
- `assets/js/contact-loader.js` - Script utama untuk load kontak
- `FrontEnd/js/package-detail-loader.js` - Load kontak di detail paket
- `FrontEnd/html/Index.html` - Halaman utama
- `FrontEnd/html/package_detail.html` - Detail paket
- `FrontEnd/html/profile.html` - Halaman profil

## ⚡ Cara Kerja

1. **Page Load**: Script `contact-loader.js` otomatis load saat halaman dibuka
2. **API Call**: Fetch data dari `get_settings.php`
3. **Update Elements**: Update semua elemen kontak (WhatsApp, telepon, email, dll)
4. **Dynamic Links**: Tombol WhatsApp dan tel: link menggunakan nomor dari database

## 🛠️ Maintenance

### **Untuk Development:**
- Hapus file `create_settings_table.php` dan `update_settings.php` setelah deployment
- Backup database sebelum perubahan besar
- Test API `get_settings.php` setelah perubahan settings

### **Troubleshooting:**
- Jika kontak tidak update: Cek console browser untuk error
- Jika admin panel error: Cek koneksi database
- Jika nomor salah format: Gunakan format 62xxx tanpa +

## 📞 Default Settings
```
whatsapp_number: 6281234567890
phone_number: +62 812 3456 7890  
email: info@mptitravel.com
whatsapp_message: "Halo, saya tertarik dengan paket wisata dari MPTI Travel..."
website_name: MPTI Travel
```

---
*Update terakhir: Juni 2025*
*Status: ✅ SELESAI - Semua nomor WhatsApp dan kontak sudah dinamis*
