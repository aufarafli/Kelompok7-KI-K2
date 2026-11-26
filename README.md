# Klinik Prestasi

Aplikasi web **Klinik Prestasi** dibangun menggunakan **PHP** (manajemen *session* dan kontrol akses berbasis peran) serta **PostgreSQL** sebagai basis data utama, dilengkapi dengan penerapan modul keamanan **AAA (Authentication, Authorization, and Accounting)** dan pencatatan aktivitas sistem.

---

## Identitas Kelompok

* **Kelompok:** Kelompok 7 KI - K2
* **Anggota:**
  * Kemas Adirangga Nayar (M0403241043)
  * Mickhael Keith Richard Simangunsong (M0403241061)
  * Muhammad Ammar Rizky (M0403241088)
  * Aufa Rafli Sofwan Pasya (M0403241133)

---

## Panduan Menjalankan Aplikasi di Localhost

Ikuti langkah-langkah berikut dari awal agar aplikasi dapat berjalan dengan baik di komputer lokal masing-masing anggota tim.

### 1. Persiapan Awal (Prasyarat)
Pastikan di laptop/komputer sudah terpasang:
* **Web server (Apache)**, misalnya melalui **XAMPP** (contoh direktori: `C:\xampp-absensi\htdocs` atau `C:\xampp\htdocs`).
* **PostgreSQL** beserta **pgAdmin 4** (atau terminal `psql`).
* **Git** untuk mengunduh (*clone*) repositori dari GitHub.

### 2. Mengambil Link & Melakukan `git clone` ke Folder `htdocs`
1. Buka halaman repositori **Klinik Prestasi** di GitHub (`Kelompok7-KI-K2`).
2. Klik tombol hijau **`<> Code`** di bagian kanan atas, lalu salin **HTTPS URL** (contoh: `https://github.com/aufarafli/Kelompok7-KI-K2.git`).
3. Buka **Terminal / Git Bash / PowerShell**, lalu arahkan ke folder *root* web server lokal (misalnya `htdocs`):
   ```bash
   cd C:\xampp-absensi\htdocs

### Jalankan perintah git clone menggunakan link yang sudah disalin:
git clone [https://github.com/aufarafli/Kelompok7-KI-K2.git](https://github.com/aufarafli/Kelompok7-KI-K2.git)

### Masuk ke folder hasil clone untuk memastikan struktur foldernya (klinik_prestasi/, sql/, config/, dll.) sudah ada.

### Menyalakan Layanan Apache & PostgreSQL
-Buka panel kontrol web server (misalnya XAMPP Control Panel) dan klik Start pada modul Apache hingga statusnya berjalan (Running).
-Pastikan layanan PostgreSQL juga aktif (bisa dicek lewat pgAdmin 4 atau aplikasi Services di Windows).

### Membuat File Konfigurasi Database (config/db.php)
File klinik_prestasi/config/db.php tidak ikut diunggah ke GitHub (diabaikan melalui .gitignore) agar kredensial lokal tiap anggota tetap aman. Oleh karena itu, buat file tersebut dari template yang tersedia:
1. Salin file klinik_prestasi/config/db.example.php menjadi klinik_prestasi/config/db.php di folder klinik_prestasi/config/.
2. Buka klinik_prestasi/config/db.php, lalu sesuaikan pengaturan koneksi PostgreSQL di komputer lokal masing-masing:
    $host (umumnya 'localhost')
    $port (sesuaikan dengan port PostgreSQL di laptop, misalnya '5433' atau '5432')
    $dbname (nama database, misalnya 'klinikprestasi')
    $user (username PostgreSQL, misalnya 'postgres')
    $password (password PostgreSQL lokal masing-masing)
3. Pertahankan pengaturan zona waktu (Asia/Jakarta) dan SET search_path TO public agar waktu serta skema tabel terbaca konsisten.

### Membuat Database & Menjalankan File SQL Secara Berurutan
1. Buka pgAdmin 4 dan buat database baru (misalnya bernama klinikprestasi) apabila belum ada.
2. Buka Query Tool pada database tersebut, lalu jalankan file skema awal terlebih dahulu:
    Jalankan klinik_prestasi/klinikprestasi.sql untuk membuat struktur tabel dasar, relasi (foreign key), dan data awal.
3. Setelah skema awal selesai, jalankan file pembaruan database:
    Jalankan klinik_prestasi/sql/update_aaa.sql untuk menambahkan kolom tanggal_pengajuan pada tabel peer_review serta membuat tabel audit_log beserta indeksnya.

### Membuka Aplikasi di Browser
1. Buka browser (Chrome, Edge, Firefox, dll.).
2. Akses alamat localhost sesuai lokasi folder proyek di htdocs, contoh:
    http://localhost/Database-Klinik-Prestasi/klinik_prestasi/

3. Aplikasi akan mengarahkan ke halaman login (login.php). Masuk menggunakan akun yang terdaftar dan berstatus aktif untuk mulai menggunakan sistem.

## Penerapan Modul Keamanan AAA
Sistem keamanan aplikasi dirancang menerapkan prinsip AAA (Authentication, Authorization, and Accounting) agar akses pengguna terkendali dan setiap aktivitas penting tercatat.

### Authentication (Autentikasi / Verifikasi Identitas)
Authentication memastikan bahwa pengguna yang masuk ke sistem adalah benar-benar pemilik akun yang sah dan akunnya berstatus aktif.
    -Proses login & validasi akun: Memeriksa kredensial pengguna saat masuk ke sistem serta mengecek status keaktifan akun sebelum memberikan akses sesi.
    -Keamanan session: Menggunakan manajemen session PHP termasuk regenerasi ID session (session_regenerate_id(true)) saat login untuk membantu mencegah session fixation, serta membersihkan session saat logout.
    -Lokasi file terkait:
        -Klinik_prestasi/login.php — alur proses login dan pembentukan session pengguna.
        -klinik_prestasi/logout.php — proses keluar/penghancuran session pengguna.
        -klinik_prestasi/config/auth.php — helper autentikasi dan pengecekan status login/session.
    
### Authorization (Autorisasi / Hak Akses Berbasis Peran)
Authorization menentukan apa saja yang boleh diakses atau dilakukan oleh pengguna setelah berhasil login, berdasarkan perannya (seperti Admin, Mentor, dan Mahasiswa).
    -Pengecekan peran & pembatasan akses: Menyediakan fungsi/helper pengecekan peran untuk membatasi halaman, aksi, maupun menu sesuai hak akses tiap peran sehingga pengguna tidak bisa membuka fitur di luar wewenangnya.
    -Penyesuaian tampilan & navigasi: Menyembunyikan atau menyesuaikan menu, tombol aksi, dan informasi pada antarmuka sesuai peran pengguna yang sedang aktif.
    -Lokasi file terkait:
        -klinik_prestasi/config/auth.php — fungsi pembantu pengecekan peran (seperti hasRole() dan wajibRole()) serta penjagaan akses halaman.
        -klinik_prestasi/index.php — halaman utama/dashboard dengan penyesuaian tampilan/statistik sesuai akses.
        -klinik_prestasi/layout/ — komponen tata letak bersama (seperti header/navbar dan menu navigasi) yang menyesuaikan tampilan berdasarkan peran.
        -Halaman-halaman modul (peer_review/, karya/, mahasiswa/, laporan_review/, audit_log/) — penerapan pembatasan akses pada masing-masing fitur/tampilan.

### Accounting (Akuntansi / Pencatatan Audit Log)
Accounting mencatat jejak aktivitas penting pengguna di sistem sebagai bentuk transparansi, pemantauan, dan penelusuran riwayat kejadian.
    -Pencatatan peristiwa sistem: Menyimpan log aktivitas seperti proses login, logout, maupun aksi administratif ke dalam tabel audit_log di PostgreSQL beserta informasi pengguna, peran, aksi, modul, target, keterangan, alamat IP, dan waktu kejadian.
    -Pemantauan log: Menyediakan tampilan riwayat log agar aktivitas sistem dapat ditinjau kembali.
    -Lokasi file terkait:
        -klinik_prestasi/sql/update_aaa.sql — definisi struktur tabel audit_log dan indeks pendukungnya.
        -klinik_prestasi/config/audit.php — fungsi pencatatan log aktivitas ke database (seperti catatAuditLog()).
        -klinik_prestasi/login.php dan klinik_prestasi/logout.php — pencatatan aktivitas masuk dan keluar pengguna.
        -klinik_prestasi/audit_log/ — halaman/modul untuk melihat dan memantau catatan audit_log.

---

### Catatan Penyesuaian Sebelum Di-commit
* Jika di repositori kalian nama *helper* atau nama *file* di dalam `audit_log/` / `peer_review/` sedikit berbeda (misalnya `index.php` di dalam `audit_log/`), tinggal sesuaikan nama filenya pada bagian **Lokasi file terkait** agar 100% cocok dengan struktur folder aktual.
* Jika ada bagian tertentu yang ingin ditambahkan—misalnya daftar akun uji coba atau alur *workflow* pengajuan *peer review*—beri tahu saja supaya bisa langsung digabungkan.

