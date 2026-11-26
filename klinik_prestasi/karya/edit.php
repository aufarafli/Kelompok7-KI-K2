<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mahasiswa']);

$id = trim((string)($_GET['id'] ?? ''));
if ($id === '') {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>ID karya tidak valid.</div>";
    include '../layout/footer.php';
    exit;
}

$res  = pg_query_params($conn, "SELECT * FROM KARYA WHERE TRIM(ID_Karya) = $1", [$id]);
$data = pg_fetch_assoc($res);

if (!$data) {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>Data karya tidak ditemukan.</div>";
    include '../layout/footer.php';
    exit;
}

// Anti-IDOR: Mahasiswa hanya boleh mengedit karyanya sendiri
if (hasRole('mahasiswa') && trim((string)($data['id_user'] ?? '')) !== trim((string)$_SESSION['user_id'])) {
    echo "<script>alert('Akses Ditolak! Karya ini bukan milik Anda.'); window.location='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = trim((string)($_POST['judul'] ?? ''));
    $bidang = trim((string)($_POST['bidang'] ?? ''));
    $file   = trim((string)($_POST['file'] ?? ''));

    $update = pg_query_params(
        $conn,
        "UPDATE KARYA SET Judul_Karya = $1, Bidang_Karya = $2, File_Karya = $3 WHERE TRIM(ID_Karya) = $4",
        [$judul, $bidang, $file, $id]
    );

    if ($update) {
        echo "<script>alert('Data karya berhasil diperbarui!'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Gagal memperbarui karya: " . htmlspecialchars(pg_last_error($conn), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

$bidang_aktif = trim((string)($data['bidang_karya'] ?? 'Esai'));
?>

<h2>Edit Data Karya</h2>
<div class="form-box">
    <form action="" method="POST">
        <label>ID Karya</label>
        <input type="text" value="<?= htmlspecialchars(trim((string)$data['id_karya']), ENT_QUOTES, 'UTF-8') ?>" disabled>

        <label>Judul Karya</label>
        <input type="text" name="judul" required value="<?= htmlspecialchars((string)($data['judul_karya'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label>Bidang Karya</label>
        <select name="bidang" required>
            <option value="Esai" <?= strcasecmp($bidang_aktif, 'Esai') === 0 ? 'selected' : '' ?>>Esai</option>
            <option value="KTI" <?= strcasecmp($bidang_aktif, 'KTI') === 0 ? 'selected' : '' ?>>KTI</option>
            <option value="Bisnis" <?= strcasecmp($bidang_aktif, 'Bisnis') === 0 ? 'selected' : '' ?>>Bisnis</option>
            <option value="Design" <?= strcasecmp($bidang_aktif, 'Design') === 0 ? 'selected' : '' ?>>Design</option>
        </select>

        <label>Link File Karya (Google Drive / GitHub)</label>
        <input type="url" name="file" required value="<?= htmlspecialchars((string)($data['file_karya'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-blue">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<?php include '../layout/footer.php'; ?>