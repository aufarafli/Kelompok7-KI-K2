<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mentor']);

$id = $_GET['id'] ?? '';
$query = "
    SELECT PR.*, K.Judul_Karya, K.Bidang_Karya 
    FROM PEER_REVIEW PR 
    JOIN KARYA K ON PR.ID_Karya = K.ID_Karya 
    WHERE PR.ID_Review = $1
";
$res  = pg_query_params($conn, $query, array($id));
$data = pg_fetch_assoc($res);

if (!$data) {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>Data review tidak ditemukan.</div>";
    include '../layout/footer.php';
    exit;
}

if (hasRole('mentor')) {
    header("Location: detail.php?id=" . urlencode($id));
    exit;
}

$bidang_karya = trim($data['bidang_karya']);
$mentor_list = pg_query_params($conn, "
    SELECT MT.ID_Mentor, M.Nama, MT.Bidang_Keahlian 
    FROM MENTOR MT
    JOIN USER_MEMBER U ON MT.ID_User = U.ID_User
    JOIN MAHASISWA M ON U.NIM = M.NIM
    WHERE M.Status_Mhs = 'Aktif' AND LOWER(MT.Bidang_Keahlian) = LOWER($1)
    ORDER BY MT.ID_Mentor ASC
", [$bidang_karya]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status       = $_POST['status'];
    $hasil        = $_POST['hasil'];
    $id_mentor    = !empty($_POST['id_mentor']) ? $_POST['id_mentor'] : null;
    $tanggal_auto = ($status === 'Waiting') ? null : ($data['tanggal_review'] ?: date('Y-m-d'));

    $update = pg_query_params(
        $conn,
        "UPDATE PEER_REVIEW SET ID_Mentor=$1, Status_Review=$2, Hasil_Review=$3, Tanggal_Review=$4 WHERE ID_Review=$5",
        array($id_mentor, $status, $hasil, $tanggal_auto, $id)
    );

    if ($update) {
        echo "<script>alert('Status dan penugasan review berhasil diperbarui!'); window.location='index.php';</script>";
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Error: " . htmlspecialchars(pg_last_error($conn)) . "</div>";
    }
}
?>

<h2>Kelola Penugasan & Status Review</h2>
<div class="form-box">
    <form action="" method="POST">
        <label>ID Karya</label>
        <input type="text" value="<?= htmlspecialchars(trim($data['id_karya'])) ?>" disabled>

        <label>Judul Karya & Bidang</label>
        <input type="text" value="<?= htmlspecialchars($data['judul_karya']) ?> (<?= htmlspecialchars($bidang_karya) ?>)" disabled>

        <label>Mentor Penilai (Sesuai Bidang <?= htmlspecialchars($bidang_karya) ?>)</label>
        <select name="id_mentor" required>
            <option value="">-- Pilih Mentor --</option>
            <?php while ($m = pg_fetch_assoc($mentor_list)): ?>
                <?php $sel = (trim($data['id_mentor'] ?? '') === trim($m['id_mentor'])) ? 'selected' : ''; ?>
                <option value="<?= trim($m['id_mentor']) ?>" <?= $sel ?>>
                    <?= trim($m['id_mentor']) ?> — <?= htmlspecialchars($m['nama']) ?> (Spesialis: <?= htmlspecialchars($m['bidang_keahlian']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Status Review</label>
        <select name="status" required>
            <option value="Waiting" <?= $data['status_review'] == 'Waiting' ? 'selected' : '' ?>>Waiting</option>
            <option value="Revision" <?= $data['status_review'] == 'Revision' ? 'selected' : '' ?>>Revision</option>
            <option value="Finished" <?= $data['status_review'] == 'Finished' ? 'selected' : '' ?>>Finished</option>
        </select>

        <label>Catatan / Hasil Review</label>
        <textarea name="hasil" rows="5" placeholder="Tuliskan masukan, saran perbaikan, atau hasil review di sini..."><?= htmlspecialchars($data['hasil_review'] ?? '') ?></textarea>

        <label>Tanggal Review (Otomatis)</label>
        <input type="text" value="<?= htmlspecialchars($data['tanggal_review'] ?: date('Y-m-d')) ?>" disabled>

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-blue">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<?php include '../layout/footer.php'; ?>