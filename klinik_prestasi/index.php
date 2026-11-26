<?php
include 'config/db.php';
include 'layout/header.php';

if (hasRole('admin')) {
    $q_pengguna = pg_fetch_assoc(pg_query($conn, "
        SELECT COUNT(*) AS total 
        FROM USER_MEMBER U 
        JOIN MAHASISWA M ON U.NIM = M.NIM 
        WHERE M.Status_Mhs = 'Aktif'
    "));
    $q_karya    = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM KARYA"));
    $q_waiting  = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE Status_Review = 'Waiting'"));
    $q_revision = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE Status_Review = 'Revision'"));
    $q_finished = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE Status_Review = 'Finished'"));
} elseif (hasRole('mentor')) {
    $id_mentor  = $_SESSION['role_id'];
    $q_waiting  = pg_fetch_assoc(pg_query_params($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE ID_Mentor = $1 AND Status_Review = 'Waiting'", [$id_mentor]));
    $q_revision = pg_fetch_assoc(pg_query_params($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE ID_Mentor = $1 AND Status_Review = 'Revision'", [$id_mentor]));
    $q_finished = pg_fetch_assoc(pg_query_params($conn, "SELECT COUNT(*) AS total FROM PEER_REVIEW WHERE ID_Mentor = $1 AND Status_Review = 'Finished'", [$id_mentor]));
} else {
    $id_user    = $_SESSION['user_id'];
    $q_karya    = pg_fetch_assoc(pg_query_params($conn, "SELECT COUNT(*) AS total FROM KARYA WHERE ID_User = $1", [$id_user]));
    $q_waiting  = pg_fetch_assoc(pg_query_params($conn, "
        SELECT COUNT(*) AS total 
        FROM PEER_REVIEW PR 
        JOIN KARYA K ON PR.ID_Karya = K.ID_Karya 
        WHERE K.ID_User = $1 AND PR.Status_Review = 'Waiting'
    ", [$id_user]));
    $q_revision = pg_fetch_assoc(pg_query_params($conn, "
        SELECT COUNT(*) AS total 
        FROM PEER_REVIEW PR 
        JOIN KARYA K ON PR.ID_Karya = K.ID_Karya 
        WHERE K.ID_User = $1 AND PR.Status_Review = 'Revision'
    ", [$id_user]));
    $q_finished = pg_fetch_assoc(pg_query_params($conn, "
        SELECT COUNT(*) AS total 
        FROM PEER_REVIEW PR 
        JOIN KARYA K ON PR.ID_Karya = K.ID_Karya 
        WHERE K.ID_User = $1 AND PR.Status_Review = 'Finished'
    ", [$id_user]));
}
?>

<div class="hero-header">
    <h1>Klinik Prestasi UKM Forces IPB</h1>
    <p>Sistem Informasi Manajemen Data Mahasiswa, Pengajuan Karya, dan Pemantauan Peer Review</p>
</div>

<div class="stats-grid">
    <?php if (hasRole('mentor')): ?>
        <div class="stat-card" style="border-left: 4px solid #d97706;">
            <div class="stat-label" style="color: #b45309;">Waiting</div>
            <div class="stat-value" style="color: #d97706;"><?= $q_waiting['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #64748b;">
            <div class="stat-label" style="color: #475569;">Revision</div>
            <div class="stat-value" style="color: #475569;"><?= $q_revision['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-label" style="color: #047857;">Finished</div>
            <div class="stat-value" style="color: #059669;"><?= $q_finished['total'] ?? 0 ?></div>
        </div>
    <?php elseif (hasRole('mahasiswa')): ?>
        <div class="stat-card" style="border-left: 4px solid #2563eb;">
            <div class="stat-label" style="color: #1d4ed8;">Total Karya</div>
            <div class="stat-value" style="color: #0f172a;"><?= $q_karya['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #d97706;">
            <div class="stat-label" style="color: #b45309;">Waiting</div>
            <div class="stat-value" style="color: #d97706;"><?= $q_waiting['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #64748b;">
            <div class="stat-label" style="color: #475569;">Revision</div>
            <div class="stat-value" style="color: #475569;"><?= $q_revision['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-label" style="color: #047857;">Finished</div>
            <div class="stat-value" style="color: #059669;"><?= $q_finished['total'] ?? 0 ?></div>
        </div>
    <?php else: ?>
        <div class="stat-card" style="border-left: 4px solid #2563eb;">
            <div class="stat-label">Pengguna Aktif</div>
            <div class="stat-value"><?= $q_pengguna['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #1e3a8a;">
            <div class="stat-label">Total Karya Terdaftar</div>
            <div class="stat-value"><?= $q_karya['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #d97706;">
            <div class="stat-label" style="color: #b45309;">Waiting</div>
            <div class="stat-value" style="color: #d97706;"><?= $q_waiting['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #64748b;">
            <div class="stat-label" style="color: #475569;">Revision</div>
            <div class="stat-value" style="color: #475569;"><?= $q_revision['total'] ?? 0 ?></div>
        </div>

        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-label" style="color: #047857;">Finished</div>
            <div class="stat-value" style="color: #059669;"><?= $q_finished['total'] ?? 0 ?></div>
        </div>
    <?php endif; ?>
</div>

<h2>Menu Utama</h2>
<div class="menu-grid">
    <?php if (hasRole('admin')): ?>
    <a href="mahasiswa/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Daftar Pengguna</div>
            <div class="menu-card-desc">Kelola akun admin, mentor penilai, dan biodata anggota mahasiswa aktif.</div>
        </div>
        <div class="menu-card-link">Buka Modul Pengguna &rarr;</div>
    </a>
    <?php endif; ?>

    <?php if (hasRole(['admin', 'mahasiswa'])): ?>
    <a href="karya/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Data Karya</div>
            <div class="menu-card-desc">Kelola judul karya ilmiah, esai, bisnis, desain, beserta tautan dokumen Drive.</div>
        </div>
        <div class="menu-card-link">Buka Modul Karya &rarr;</div>
    </a>
    <?php endif; ?>

    <a href="peer_review/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Peer Review</div>
            <div class="menu-card-desc">Tugaskan mentor penilai, pantau progres peninjauan, dan catat hasil evaluasi karya.</div>
        </div>
        <div class="menu-card-link">Buka Modul Peer Review &rarr;</div>
    </a>

    <?php if (hasRole('admin')): ?>
    <a href="audit_log/index.php" class="menu-card">
        <div>
            <div class="menu-card-title">Audit Log</div>
            <div class="menu-card-desc">Pantau jejak autentikasi, pengajuan karya, perubahan status review, dan aktivitas sistem.</div>
        </div>
        <div class="menu-card-link">Buka Modul Audit Log &rarr;</div>
    </a>
    <?php endif; ?>
</div>

<?php include 'layout/footer.php'; ?>