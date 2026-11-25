<?php
include 'config/db.php';
include 'layout/header.php';

// Mengambil ringkasan statistik real-time dari database
$q_mhs = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM MAHASISWA WHERE Status_Mhs = 'Aktif'"));
$q_karya = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM KARYA"));
$q_review = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE Status_Review = 'Menunggu'"));
$q_selesai = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE Status_Review = 'Selesai'"));
?>

<div class="hero-header">
    <h1>Klinik Prestasi UKM Forces IPB</h1>
    <p>Sistem Informasi Manajemen Data Mahasiswa, Pengajuan Karya, dan Pemantauan Peer Review</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Mahasiswa Aktif</div>
        <div class="stat-value"><?= $q_mhs['total'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Karya Terdaftar</div>
        <div class="stat-value"><?= $q_karya['total'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Review Menunggu</div>
        <div class="stat-value" style="color: #d97706;"><?= $q_review['total'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Review Selesai</div>
        <div class="stat-value" style="color: #059669;"><?= $q_selesai['total'] ?? 0 ?></div>
    </div>
</div>

<h2>Menu Utama</h2>
<div class="menu-grid">
    <a href="mahasiswa/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Data Mahasiswa</div>
            <div class="menu-card-desc">Kelola biodata mahasiswa, angkatan, kontak, dan status keanggotaan aktif.</div>
        </div>
        <div class="menu-card-link">Buka Modul Mahasiswa &rarr;</div>
    </a>

    <a href="karya/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Data Karya</div>
            <div class="menu-card-desc">Kelola judul karya ilmiah, esai, bisnis, desain, beserta tautan dokumen Drive.</div>
        </div>
        <div class="menu-card-link">Buka Modul Karya &rarr;</div>
    </a>

    <a href="peer_review/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Peer Review</div>
            <div class="menu-card-desc">Tugaskan mentor penilai, pantau progres peninjauan, dan catat hasil evaluasi karya.</div>
        </div>
        <div class="menu-card-link">Buka Modul Peer Review &rarr;</div>
    </a>
</div>

<?php include 'layout/footer.php'; ?>