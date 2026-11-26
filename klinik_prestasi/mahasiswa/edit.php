<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole('admin');

$nim = $_GET['nim'] ?? '';
$query_get = "
    SELECT 
        U.ID_User, 
        M.*, 
        A.ID_Admin, 
        A.Jabatan, 
        MT.ID_Mentor, 
        MT.Bidang_Keahlian,
        MT.Status_Mentor
    FROM MAHASISWA M 
    LEFT JOIN USER_MEMBER U ON M.NIM = U.NIM 
    LEFT JOIN ADMIN A ON U.ID_User = A.ID_User
    LEFT JOIN MENTOR MT ON U.ID_User = MT.ID_User
    WHERE M.NIM = $1
";
$res  = pg_query_params($conn, $query_get, array($nim));
$data = pg_fetch_assoc($res);

if (!$data) {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>Data pengguna tidak ditemukan.</div>";
    include '../layout/footer.php';
    exit;
}

// Deteksi peran pengguna secara otomatis
$role_target  = 'mahasiswa';
$judul_halaman = 'Edit Data Mahasiswa';
$label_status  = 'Status Mahasiswa';

if (!empty($data['id_admin'])) {
    $role_target   = 'admin';
    $judul_halaman = 'Edit Data Admin';
    $label_status  = 'Status Admin';
} elseif (!empty($data['id_mentor'])) {
    $role_target   = 'mentor';
    $judul_halaman = 'Edit Data Mentor';
    $label_status  = 'Status Mentor';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = trim($_POST['nama']);
    $kontak = trim($_POST['kontak']);
    $status = $_POST['status'];

    pg_query($conn, "BEGIN");

    $query_mhs  = "UPDATE MAHASISWA SET Nama=$1, Kontak=$2, Status_Mhs=$3 WHERE NIM=$4";
    $update_mhs = pg_query_params($conn, $query_mhs, array($nama, $kontak, $status, $nim));

    $update_role = true;
    if ($role_target === 'admin') {
        $jabatan     = trim($_POST['jabatan']) ?: 'Admin Klinik Prestasi';
        $update_role = pg_query_params($conn, "UPDATE ADMIN SET Jabatan=$1 WHERE ID_User=$2", array($jabatan, trim($data['id_user'])));
    } elseif ($role_target === 'mentor') {
        $bidang      = $_POST['bidang_keahlian'] ?: 'Esai';
        $update_role = pg_query_params($conn, "UPDATE MENTOR SET Bidang_Keahlian=$1, Status_Mentor=$2 WHERE ID_User=$3", array($bidang, $status, trim($data['id_user'])));
    }

    if ($update_mhs && $update_role) {
        pg_query($conn, "COMMIT");
        echo "<script>alert('$judul_halaman berhasil diperbarui!'); window.location='index.php';</script>";
    } else {
        pg_query($conn, "ROLLBACK");
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Error: " . htmlspecialchars(pg_last_error($conn)) . "</div>";
    }
}
?>

<h2><?= $judul_halaman ?></h2>
<div class="form-box">
    <form action="" method="POST">
        <label>ID User (Tidak dapat diubah)</label>
        <input type="text" value="<?= htmlspecialchars(trim($data['id_user'] ?? '-')) ?>" disabled>

        <?php if ($role_target === 'admin'): ?>
            <label>Jabatan Admin</label>
            <input type="text" name="jabatan" value="<?= htmlspecialchars($data['jabatan'] ?? '') ?>" required placeholder="Contoh: Koordinator Klinik Prestasi">
        <?php elseif ($role_target === 'mentor'): ?>
            <label>Bidang Keahlian Mentor</label>
            <select name="bidang_keahlian" required>
                <option value="Esai" <?= ($data['bidang_keahlian'] ?? '') == 'Esai' ? 'selected' : '' ?>>Esai</option>
                <option value="KTI" <?= ($data['bidang_keahlian'] ?? '') == 'KTI' ? 'selected' : '' ?>>KTI</option>
                <option value="Bisnis" <?= ($data['bidang_keahlian'] ?? '') == 'Bisnis' ? 'selected' : '' ?>>Bisnis</option>
                <option value="Design" <?= ($data['bidang_keahlian'] ?? '') == 'Design' ? 'selected' : '' ?>>Design</option>
            </select>
        <?php endif; ?>

        <label>Nama Lengkap</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

        <label>NIM (Tidak dapat diubah)</label>
        <input type="text" value="<?= htmlspecialchars($data['nim']) ?>" disabled>

        <label>Kontak</label>
        <input type="text" name="kontak" value="<?= htmlspecialchars($data['kontak']) ?>" required>

        <label><?= $label_status ?></label>
        <select name="status">
            <option value="Aktif" <?= $data['status_mhs'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="Nonaktif" <?= $data['status_mhs'] == 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-blue">Update</button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<?php include '../layout/footer.php'; ?>