# PANDUAN LENGKAP DEPLOYMENT SISTEM SPL (SUSI PARSEL LEBARAN) 2026

Dokumen ini berisi panduan langkah-demi-langkah untuk melakukan deployment folder aplikasi **SPL_SIAP_DEPLOY** ke server produksi (cPanel / Shared Hosting, VPS Linux, atau Windows Server / XAMPP).

---

## 📁 Isi Paket Deployment

Dalam folder ini sudah disertakan:
1. **Source Code Lengkap Laravel 11 (PHP 8.2+)**
2. **Folder `vendor/` Lengkap**: Seluruh library Composer sudah terinstal rapi, sehingga Anda tidak wajib menjalankan `composer install` jika di hosting tidak ada akses terminal.
3. **File Database SQL Siap Import**:
   - `DATABASE_spl2026_SIAP_IMPORT.sql` (terletak di root folder)
   - `database/spl2026_ready_to_deploy.sql` (salinan di folder database)
4. **File Konfigurasi Lingkungan**:
   - `.env` (sudah tersetel `APP_ENV=production` dan `APP_DEBUG=false`)
   - `.env.example`
5. **Daftar Akun Pengguna**:
   - `DAFTAR_AKUN_LOGIN.txt` (Owner, Admin, dan 71 Agen Lapangan).

---

## 🗄️ Langkah 1: Import Database MySQL

File SQL `DATABASE_spl2026_SIAP_IMPORT.sql` sudah mencakup:
- Struktur seluruh tabel lengkap dengan indeks performa tinggi (*high-concurrency index*).
- Akun Owner, Admin, dan 71 Agen Lapangan.
- Master 50+ Barang Eceran & Parsel, Master Kemasan, dan Seluruh Paket Musim 2026.

### Cara A: Melalui phpMyAdmin (cPanel / XAMPP)
1. Buka **phpMyAdmin**.
2. Buat database baru bernama `spl2026` (atau gunakan nama database yang Anda buat di cPanel, misal: `u12345_spl`).
3. Klik database tersebut, lalu pilih tab **Import**.
4. Klik **Choose File** / **Pilih Berkas**, lalu pilih file **`DATABASE_spl2026_SIAP_IMPORT.sql`**.
5. Klik tombol **Import** / **Kirim** di bagian bawah.
6. Tunggu hingga muncul notifikasi hijau tanda import sukses.

### Cara B: Melalui Terminal / SSH (VPS Linux)
Jalankan perintah:
```bash
mysql -u root -p spl2026 < DATABASE_spl2026_SIAP_IMPORT.sql
```

---

## ⚙️ Langkah 2: Konfigurasi File `.env`

Buka file `.env` di text editor, lalu sesuaikan koneksi database dan URL domain Anda:

```dotenv
APP_NAME="SPL (Susi Parsel Lebaran)"
APP_ENV=production
APP_KEY=base64:M0o0iESd3n8v/S27Kr7D/CUiReVa1dJSSKUxp1L+oDQ=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

# Ganti dengan domain / URL website Anda yang sebenarnya
APP_URL=https://nama-domain-anda.com

# Sesuaikan dengan database produksi Anda
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spl2026
DB_USERNAME=root
DB_PASSWORD=password_database_anda

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
FILESYSTEM_DISK=public
```

---

## 🌐 Langkah 3: Pilihan Deployment

### OPSI 1: Deploy ke cPanel / Shared Hosting
1. Compress seluruh isi folder `SPL_SIAP_DEPLOY` menjadi format `.zip`.
2. Buka **File Manager** di cPanel.
3. Buat folder untuk aplikasi, misal di luar `public_html`: `/home/username/spl/`.
4. Upload file `.zip` tersebut ke `/home/username/spl/` lalu ekstrak (**Extract**).
5. Pindahkan isi dari folder `spl/public/` ke folder **`public_html/`** (atau subdomain root).
6. Edit file `public_html/index.php`:
   - Ubah baris pemanggilan autoload:
     ```php
     require __DIR__.'/../spl/vendor/autoload.php';
     $app = require_once __DIR__.'/../spl/bootstrap/app.php';
     ```
7. Buat database di cPanel (Menu **MySQL Databases**), buat user database, dan berikan hak akses (**ALL PRIVILEGES**).
8. Import file `DATABASE_spl2026_SIAP_IMPORT.sql` via phpMyAdmin.
9. Edit file `.env` pada folder `/home/username/spl/` dengan nama database, user, password, dan domain Anda.

---

### OPSI 2: Deploy ke VPS Linux (Ubuntu / Debian / Nginx / Apache)
1. Upload folder `SPL_SIAP_DEPLOY` ke direktori web server, contoh: `/var/www/spl`.
2. Berikan hak akses kepemilikan kepada user web server (`www-data`):
   ```bash
   sudo chown -R www-data:www-data /var/www/spl
   sudo chmod -R 775 /var/www/spl/storage
   sudo chmod -R 775 /var/www/spl/bootstrap/cache
   ```
3. Arahkan Document Root Web Server (Virtual Host) ke folder **/var/www/spl/public**:
   - **Contoh Konfigurasi Nginx (`/etc/nginx/sites-available/spl`)**:
     ```nginx
     server {
         listen 80;
         server_name namadomain.com www.namadomain.com;
         root /var/www/spl/public;

         index index.php index.html;

         location / {
             try_files $uri $uri/ /index.php?$query_string;
         }

         location ~ \.php$ {
             include snippets/fastcgi-php.conf;
             fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
             fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
             include fastcgi_params;
         }

         location ~ /\.(?!well-known).* {
             deny all;
         }
     }
     ```
4. Jalankan perintah optimasi Laravel via terminal:
   ```bash
   cd /var/www/spl
   php artisan storage:link
   php artisan optimize
   ```

---

### OPSI 3: Menjalankan di Komputer / Server Windows (XAMPP)
1. Cukup letakkan folder `SPL_SIAP_DEPLOY` di dalam `C:\xampp\htdocs\`.
2. Buka browser dan akses:
   `http://localhost/SPL_SIAP_DEPLOY/public`
3. Aplikasi langsung berjalan siap pakai!

---

## 🔑 Informasi Akun Login Default

- **Akun Owner (Super Admin)**:
  - Username: `owner`
  - Password: `ownerspl`
- **Akun Administrator**:
  - Username: `admin`
  - Password: `admin123`
- **Akun Agen Lapangan**:
  - Contoh: `aikomariah` / password: `password`
  - Contoh: `sitinur` / password: `password123`
  - Contoh: `irasinta` / password: `agenspl`
  - *(Daftar lengkap 71 akun dapat dilihat di berkas `DAFTAR_AKUN_LOGIN.txt` atau di menu Owner > Ekspor Akun Agen)*.

---

## ⚡ Perintah Penting Pasca Deploy (Opsional)

Jika ada perubahan tampilan atau konfigurasi di kemudian hari pada server produksi:
```bash
# Menautkan folder penyimpanan publik untuk foto/gambar
php artisan storage:link

# Mengompilasi dan meng-cache konfigurasi, route, dan view agar super cepat
php artisan optimize

# Membersihkan cache jika ada perubahan kode
php artisan optimize:clear
```

