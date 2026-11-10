# PANDUAN DEPLOYMENT APLIKASI SPL 2026 (SUSI PARSEL LEBARAN)

Aplikasi SPL 2026 dibangun menggunakan framework **Laravel 11** dengan PHP 8.2+. Aplikasi ini sudah dikonfigurasi siap pakai (*out-of-the-box*) untuk lingkungan produksi di **Shared Hosting (cPanel)**, **VPS (Ubuntu / Nginx / Apache)**, maupun **Localhost (XAMPP)**.

---

## 1. Akun Login Resmi Bawaan

| Role | Username | Password | Deskripsi |
|---|---|---|---|
| **Owner Utama** | `owner` | `ownerspl` | Akses penuh dashboard owner, master paket, mini-POS, menu belanja grosir, approval setoran, dan pengaturan logo. |
| **Administrator** | `admin` | `admin123` | Otoritas tertinggi sistem untuk manajemen akun dan kontrol sistem. |

---

## 2. Kebutuhan Sistem Server (Server Requirements)

* **PHP Version**: PHP 8.2 atau 8.3
* **Ekstensi PHP Wajib**:
  * `pdo_sqlite` (untuk default database SQLite)
  * `pdo_mysql` (jika menggunakan database MySQL/MariaDB)
  * `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`, `gd`, `fileinfo`, `zip`
* **Web Server**: Apache (`mod_rewrite` aktif) atau Nginx

---

## 3. Pilihan Database

Aplikasi SPL 2026 mendukung 2 opsi penyimpanan database permanen:

### Opsi A: SQLite (Sangat Direkomendasikan - Langsung Jalan Tanpa Setup DB)
* Database tersimpan permanen di file `database/database.sqlite`.
* **Kelebihan**: Anda tidak perlu membuat database baru di cPanel / phpMyAdmin. Cukup upload, langsung bisa login dan berjalan 100%.
* Konfigurasi di file `.env`:
  ```ini
  DB_CONNECTION=sqlite
  SESSION_DRIVER=database
  CACHE_STORE=database
  ```

### Opsi B: MySQL / MariaDB (Untuk database terpusat di phpMyAdmin)
1. Buat database baru di cPanel / phpMyAdmin (misal: `spl2026`).
2. Sesuaikan konfigurasi di file `.env`:
  ```ini
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=nama_database_anda
  DB_USERNAME=user_database_anda
  DB_PASSWORD=password_database_anda
  SESSION_DRIVER=database
  CACHE_STORE=database
  ```
3. Jalankan migrasi dan seeder awal:
  ```bash
  php artisan migrate --force
  php artisan db:seed --force
  ```

---

## 4. Cara Deploy di cPanel / Shared Hosting

1. **Upload File ZIP**:
   * Upload file archive ZIP (`SPL2026_DEPLOY_READY.zip`) ke File Manager cPanel Anda.
   * Ekstrak ke direktori tujuan (misalnya di dalam `public_html/` atau subfolder `public_html/SPL2026/`).
2. **Pengaturan File `.env`**:
   * Pastikan file `.env` ada di root folder aplikasi.
   * Sesuaikan `APP_URL` dengan alamat domain/subdomain Anda, contoh:
     ```ini
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=https://parsel.domainanda.com
     ```
3. **Pastikan Permission Folder Writable**:
   * Folder `storage/` dan `bootstrap/cache/` harus memiliki izin tulis (`chmod 775` atau `chmod 755`).
4. **Symlink Storage Publik**:
   * Pastikan folder `public/storage` terhubung ke `storage/app/public`.
   * Jika memiliki akses terminal cPanel / SSH, jalankan:
     ```bash
     php artisan storage:link
     ```
5. **Akses Aplikasi**:
   * Aplikasi sudah dilengkapi `index.php` dan `.htaccess` di root yang otomatis mengarahkan pengunjung ke antarmuka aplikasi.
   * Buka browser dan login menggunakan username `owner` dan password `ownerspl`.

---

## 5. Cara Deploy di VPS (Nginx / Apache)

### Konfigurasi Nginx
Arahkan `root` ke folder `public`:
```nginx
server {
    listen 80;
    server_name parsel.domainanda.com;
    root /var/www/SPL2026/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Perintah Finalisasi di Terminal
```bash
cd /var/www/SPL2026
chown -R www-data:www-data storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache database
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Fitur Unggulan Siap Pakai

1. **Pendaftaran Paket Tabungan SPL Multi-Tier**:
   * Form pendaftaran tabungan fleksibel: cukup isi nama "Tabungan SPL", owner dapat mendaftarkan beberapa opsi nominal setor dan nominal uang tunai yang akan didapat, plus multi-item bonus hadiah gratis.
2. **Paket Parsel & Eceran**:
   * Parsel harian & mingguan dengan skema tagihan terstruktur.
   * Paket eceran mingguan dengan nominal setoran bebas (bisa Rp 1.100, Rp 2.500, dsb.) dan pilihan barang fleksibel sesuai keinginan konsumen.
3. **Rekapan Kebutuhan Paket & Kalkulator Belanja Kulakan**:
   * Rekapitulasi jumlah masing-masing paket yang harus disiapkan untuk memenuhi seluruh permintaan konsumen.
   * Rincian barang fisik yang harus dibeli kulakan grosir (konversi satuan dus/karton/bal).
   * Rincian belanja hadiah bonus paket terpisah.
   * Rincian belanja kemasan wadah (koper fiber, keranjang rotan, box hampers).
   * Rincian penyiapan uang tunai untuk pencairan tabungan konsumen per agen.
   * Lembar cetak siap print dengan kop resmi SPL.
4. **PWA (Progressive Web App)**:
   * Ikon aplikasi dan manifest terpasang, siap diinstall di HP Android / iOS agen dan owner.
5. **Penyimpanan Permanen**:
   * Logo tersimpan permanen di `public/permanent/` dan aman dari reset data.
   * Akun owner `owner` / `ownerspl` diproteksi permanen.
