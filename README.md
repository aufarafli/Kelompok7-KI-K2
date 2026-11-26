# Sistem Informasi Manajemen Data & Review — Klinik Prestasi

Aplikasi web berbasis **PHP Native** dan **PostgreSQL** untuk mengelola data keanggotaan mahasiswa, pengajuan karya (Esai, KTI, Bisnis, dan Desain), serta pemantauan proses *peer review* oleh mentor pada **Klinik Prestasi UKM Forces IPB**.

---

## Fitur Utama

* **Dashboard Real-Time:** Menampilkan ringkasan statistik jumlah mahasiswa aktif, total karya terdaftar, antrean review yang menunggu, serta review yang telah selesai langsung dari PostgreSQL.
* **Manajemen Data Mahasiswa (`mahasiswa/`):** Pencatatan biodata mahasiswa (`NIM`, `Nama`, `Angkatan`, `Kontak`, dan `Status_Mhs`) dengan transaksi atomik (`BEGIN`–`COMMIT`–`ROLLBACK`) yang otomatis mendaftarkan akun ke tabel `user_member` serta menerapkan mekanisme *soft-delete*.
* **Manajemen Data Karya (`karya/`):** Pengelolaan daftar karya mahasiswa beserta kategori bidang dan tautan dokumen penyimpanan eksternal.
* **Pemantauan Peer Review (`peer_review/`):** Penugasan mentor penilai untuk setiap karya, pembaruan status peninjauan (`Menunggu` / `Selesai`), serta pencatatan catatan evaluasi dan tanggal review.
* **Keamanan Kueri:** Seluruh interaksi basis data menggunakan *parameterized queries* (`pg_query_params`) untuk mencegah celah *SQL Injection*.

---

## Struktur Direktori

```text
Database-Klinik-Prestasi/
├── klinik_prestasi/
│   ├── config/
│   │   ├── db.example.php      # Template konfigurasi koneksi PostgreSQL
│   │   └── db.php              # Koneksi database lokal (diabaikan oleh Git)
│   ├── karya/                  # Modul CRUD Data Karya
│   ├── laporan_review/         # Modul arsip laporan review
│   ├── layout/
│   │   ├── header.php          # Navigasi utama & sistem desain UI global
│   │   └── footer.php          # Footer global
│   ├── mahasiswa/              # Modul CRUD Data Mahasiswa & User Member
│   ├── peer_review/            # Modul CRUD Penugasan & Hasil Peer Review
│   ├── index.php               # Halaman utama & statistik dashboard
│   └── klinikprestasi.sql      # Skema tabel & data awal PostgreSQL
├── .gitignore
└── README.md