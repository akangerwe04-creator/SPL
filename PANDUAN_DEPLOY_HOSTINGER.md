# 🚀 Panduan Lengkap Deploy Sistem SPL ke Hostinger

Dokumen ini memandu langkah demi langkah cara mendeploy aplikasi **SPL (Susi Parsel Lebaran) 2026** ke hosting **Hostinger** (baik paket Web Hosting, Cloud Hosting dengan hPanel, maupun VPS).

---

## 📦 Paket File yang Disiapkan

Kami telah menyiapkan paket arsip siap deploy:
- **`SPL_HOSTINGER_DEPLOY.zip`**
- Di dalamnya sudah lengkap berisi:
  1. Source code Laravel 11 lengkap.
  2. Seluruh folder pustaka **`vendor/`** (tidak perlu pusing menjalankan `composer install` lagi).
  3. File database **`DATABASE_spl2026_SIAP_IMPORT.sql`** (berisi seluruh 71 agen, admin, owner, paket, kemasan, barang, dan indeks optimasi 500+ pengguna).
  4. File konfigurasi keamanan **`.htaccess`** khusus Apache/LiteSpeed Hostinger.
  5. File **`DAFTAR_AKUN_LOGIN.txt`** (daftar seluruh username & password).

---

## 🛠️ Langkah 1: Buat Database MySQL di Hostinger hPanel

1. Login ke panel Hostinger Anda: **[hpanel.hostinger.com](https://hpanel.hostinger.com)**.
2. Pilih hosting/website Anda, lalu klik **Manage (Kelola)**.
3. Di bilah menu samping (atau pencarian), buka **Databases** ➔ **Management** (Manajemen Database).
4. Buat Database Baru:
   - **Database Name**: Masukkan nama (misal: `spl`). Hostinger akan menambahkan prefix akun Anda di depannya, contoh: `u123456789_spl`.
   - **Username**: Masukkan username (misal: `u123456789_spl` atau `u123456789_admin`).
   - **Password**: Masukkan password yang kuat dan aman.
   - **Catat ketiga data ini** (Nama DB, Username, Password) karena akan dimasukkan ke file `.env`.
5. Klik **Create (Buat)**.

---

## 📥 Langkah 2: Import Database via phpMyAdmin Hostinger

1. Pada halaman Manajemen Database yang sama, cari database yang baru saja Anda buat di tabel daftar database.
2. Klik tombol **Enter phpMyAdmin** di sebelah kanan database tersebut.
3. Setelah phpMyAdmin terbuka di tab baru:
   - Klik tab **Import** di menu atas.
   - Klik **Choose File / Pilih Berkas**.
   - Pilih file **`DATABASE_spl2026_SIAP_IMPORT.sql`** (yang ada di dalam paket arsip atau folder SPL Anda).
   - Gulir ke bawah lalu klik tombol **Go / Kirim**.
4. Tunggu beberapa detik sampai muncul notifikasi hijau bertuliskan *"Import has been successfully finished"*.
   *(Seluruh tabel, akun 71 agen, admin, owner, master barang, dan indeks performa tinggi sudah terpasang rapi)*.

---

## 📁 Langkah 3: Upload & Ekstrak File ke Hostinger

Ada 2 opsi cara deploy file ke Hostinger:

### Opsi A: Upload File ZIP via File Manager (Paling Direkomendasikan & Cepat)
1. Di hPanel Hostinger, buka menu **Files** ➔ **File Manager**.
2. Klik tombol **Access files of [Nama Domain Anda]**.
3. Masuk ke folder **`public_html`**.
4. Klik ikon **Upload** (tanda panah ke atas di pojok kanan atas) ➔ pilih **File**.
5. Pilih file **`SPL_HOSTINGER_DEPLOY.zip`** dari komputer Anda.
6. Tunggu proses upload hingga 100%.
7. Setelah selesai, klik kanan file `SPL_HOSTINGER_DEPLOY.zip` di File Manager ➔ pilih **Extract (Ekstrak)**.
8. Masukkan tanda titik (`.`) atau pilih folder `public_html` sebagai tujuan ekstrak ➔ klik **Extract**.
9. File `.zip` kini bisa Anda hapus untuk menghemat ruang disk hosting.

> [!NOTE]
> File `.htaccess` yang kami sediakan di root sudah otomatis mengatur pengalihan request ke folder `public/` secara transparan dan memblokir akses ke file rahasia seperti `.env`.

### Opsi B: Deploy Otomatis Menggunakan Git di hPanel
Jika Anda ingin terhubung langsung dengan GitHub:
1. Di hPanel Hostinger, cari menu **Advanced** ➔ **Git**.
2. Masukkan URL Repository:
   ```text
   https://github.com/akangerwe04-creator/susiparsel.git
   ```
3. Branch: `main`
4. Install Directory: `public_html`
5. Klik **Create / Deploy**.
6. Karena repository GitHub tidak menyertakan folder `vendor/` (standar Git), Anda cukup membuka menu **SSH Access** di hPanel ➔ hubungkan terminal ➔ jalankan:
   ```bash
   cd public_html
   composer install --no-dev --optimize-autoloader
   php artisan storage:link
   ```

---

## ⚙️ Langkah 4: Sesuaikan File Konfigurasi `.env`

1. Di dalam **File Manager** Hostinger (folder `public_html`), cari file bernama **`.env`**.
   *(Jika file tidak terlihat, pastikan pengaturan "Show Hidden Files" di pojok File Manager aktif).*
2. Double click atau klik kanan file **`.env`** ➔ pilih **Edit**.
3. Sesuaikan baris-baris berikut dengan data Hostinger Anda:

```dotenv
APP_NAME="SPL (Susi Parsel Lebaran)"
APP_ENV=production
APP_KEY=base64:M0o0iESd3n8v/S27Kr7D/CUiReVa1dJSSKUxp1L+oDQ=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta

# Masukkan domain Anda lengkap dengan https://
APP_URL=https://domainanda.com

# Masukkan data database Hostinger dari Langkah 1
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_spl
DB_USERNAME=u123456789_spl
DB_PASSWORD=PasswordDatabaseAndaYangTadiDibuat

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
FILESYSTEM_DISK=public
```
4. Klik tombol **Save (Simpan)** di pojok kanan atas.

---

## 🌐 Langkah 5: Atur Versi PHP di Hostinger (PHP 8.2+)

1. Di hPanel Hostinger, buka menu **Advanced** ➔ **PHP Configuration**.
2. Pilih versi **PHP 8.2** atau **PHP 8.3**.
3. Klik **Update / Save**.

---

## 🔒 Langkah 6: Hak Akses Folder (Permissions)

Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh web server:
1. Di File Manager Hostinger, klik kanan folder **`storage`** ➔ pilih **Permissions** ➔ pastikan bernilai **`0755`** atau centang Write permissions, dan pilih opsi *Recurse into subdirectories*.
2. Lakukan hal yang sama untuk folder **`bootstrap/cache`**.

---

## ✅ Langkah 7: Pengujian & Login

1. Buka browser dan ketik alamat domain Anda:
   `https://domainanda.com`
2. Halaman Login SPL akan langsung terbuka dengan logo resmi dan tampilan responsive.
3. Login menggunakan salah satu akun dari `DAFTAR_AKUN_LOGIN.txt`:
   - **Owner**: `owner` / `owner123`
   - **Admin**: `admin` / `admin123`
   - **Agen**: `agen01` s/d `agen71` / `agen123`
4. Selamat, aplikasi Susi Parsel Lebaran (SPL) 2026 Anda sudah online dan siap digunakan oleh 500+ pengguna secara bersamaan!
