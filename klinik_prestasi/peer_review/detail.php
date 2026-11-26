<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mentor', 'mahasiswa']);

$id = trim((string)($_GET['id'] ?? ''));
if ($id === '') {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>ID review tidak valid.</div>";
    include '../layout/footer.php';
    exit;
}

$query = "
    SELECT 
        PR.*, 
        K.Judul_Karya, 
        K.Bidang_Karya, 
        K.File_Karya,
        K.ID_User AS ID_Penulis,
        M_Penulis.Nama AS Penulis,
        M_Penulis.NIM AS NIM_Penulis,
        L.Nama_Layanan, 
        M_Mentor.Nama AS Nama_Mentor,
        MT.Bidang_Keahlian
    FROM PEER_REVIEW PR
    JOIN KARYA K ON PR.ID_Karya = K.ID_Karya
    JOIN USER_MEMBER U_Penulis ON K.ID_User = U_Penulis.ID_User
    JOIN MAHASISWA M_Penulis ON U_Penulis.NIM = M_Penulis.NIM
    JOIN LAYANAN L ON PR.ID_Layanan = L.ID_Layanan
    LEFT JOIN MENTOR MT ON PR.ID_Mentor = MT.ID_Mentor
    LEFT JOIN USER_MEMBER U_Mentor ON MT.ID_User = U_Mentor.ID_User
    LEFT JOIN MAHASISWA M_Mentor ON U_Mentor.NIM = M_Mentor.NIM
    WHERE TRIM(PR.ID_Review) = $1
";
$res  = pg_query_params($conn, $query, [$id]);
$data = pg_fetch_assoc($res);

if (!$data) {
    echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px;'>Data review tidak ditemukan.</div>";
    include '../layout/footer.php';
    exit;
}

if (hasRole('mahasiswa') && trim((string)($data['id_penulis'] ?? '')) !== trim((string)$_SESSION['user_id'])) {
    echo "<script>alert('Akses Ditolak! Anda hanya dapat melihat detail review karya milik sendiri.'); window.location='index.php';</script>";
    exit;
}
if (hasRole('mentor') && trim((string)($data['id_mentor'] ?? '')) !== trim((string)($_SESSION['role_id'] ?? ''))) {
    echo "<script>alert('Akses Ditolak! Review ini ditugaskan ke mentor lain.'); window.location='index.php';</script>";
    exit;
}

// Proses simpan evaluasi oleh Mentor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole('mentor')) {
    $status_baru  = trim((string)($_POST['status_action'] ?? 'Finished'));
    if (!in_array($status_baru, ['Revision', 'Finished'], true)) {
        $status_baru = 'Finished';
    }
    $hasil_baru   = trim((string)($_POST['hasil_review'] ?? ''));
    $tanggal_auto = date('Y-m-d');

    $update = pg_query_params(
        $conn,
        "UPDATE PEER_REVIEW SET Status_Review = $1, Hasil_Review = $2, Tanggal_Review = $3 WHERE TRIM(ID_Review) = $4",
        [$status_baru, $hasil_baru, $tanggal_auto, $id]
    );

    if ($update) {
        echo "<script>alert('Evaluasi berhasil disimpan dengan status: $status_baru pada tanggal $tanggal_auto!'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Error: " . htmlspecialchars(pg_last_error($conn), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

// Proses simpan pengelolaan langsung oleh Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hasRole('admin')) {
    $mentor_baru  = !empty($_POST['id_mentor']) ? trim((string)$_POST['id_mentor']) : null;
    $status_baru  = trim((string)($_POST['status_review'] ?? $data['status_review']));
    if (!in_array($status_baru, ['Waiting', 'Revision', 'Finished'], true)) {
        $status_baru = 'Waiting';
    }
    $hasil_baru   = trim((string)($_POST['hasil_review'] ?? ''));
    $tanggal_auto = ($status_baru === 'Waiting') ? null : (!empty($data['tanggal_review']) ? $data['tanggal_review'] : date('Y-m-d'));

    $update = pg_query_params(
        $conn,
        "UPDATE PEER_REVIEW SET ID_Mentor = $1, Status_Review = $2, Hasil_Review = $3, Tanggal_Review = $4 WHERE TRIM(ID_Review) = $5",
        [$mentor_baru, $status_baru, $hasil_baru, $tanggal_auto, $id]
    );

    if ($update) {
        echo "<script>alert('Perubahan data review berhasil disimpan!'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Error: " . htmlspecialchars(pg_last_error($conn), ENT_QUOTES, 'UTF-8') . "</div>";
    }
}

$bidang_karya = trim((string)($data['bidang_karya'] ?? ''));
$mentor_list  = null;
if (hasRole('admin')) {
    $mentor_list = pg_query_params($conn, "
        SELECT MT.ID_Mentor, M.Nama, MT.Bidang_Keahlian 
        FROM MENTOR MT
        JOIN USER_MEMBER U ON MT.ID_User = U.ID_User
        JOIN MAHASISWA M ON U.NIM = M.NIM
        WHERE M.Status_Mhs = 'Aktif' AND LOWER(TRIM(MT.Bidang_Keahlian)) = LOWER($1)
        ORDER BY MT.ID_Mentor ASC
    ", [$bidang_karya]);
}

$status      = trim((string)($data['status_review'] ?? 'Waiting'));
$badge_bg    = '#fef3c7';
$badge_color = '#b45309';
if ($status === 'Revision') {
    $badge_bg    = '#e2e8f0';
    $badge_color = '#334155';
} elseif ($status === 'Finished') {
    $badge_bg    = '#d1fae5';
    $badge_color = '#047857';
}

$tgl_ajukan_tampil = !empty($data['tanggal_pengajuan']) ? htmlspecialchars((string)$data['tanggal_pengajuan'], ENT_QUOTES, 'UTF-8') : '-';
$tgl_review_tampil = (in_array($status, ['Revision', 'Finished'], true) && !empty($data['tanggal_review']))
    ? htmlspecialchars((string)$data['tanggal_review'], ENT_QUOTES, 'UTF-8')
    : '-';
?>

<h2><?= hasRole('admin') ? 'Kelola & Detail Peer Review' : 'Detail Hasil Peer Review' ?></h2>
<div class="form-box" style="max-width: 680px;">
    <table style="box-shadow: none; border: none; margin-bottom: 16px;">
        <tr>
            <th style="width: 190px; background: transparent;">ID Karya</th>
            <td><strong><?= htmlspecialchars(trim((string)$data['id_karya']), ENT_QUOTES, 'UTF-8') ?></strong></td>
        </tr>
        <tr>
            <th style="background: transparent;">Judul Karya</th>
            <td><?= htmlspecialchars((string)$data['judul_karya'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($bidang_karya, ENT_QUOTES, 'UTF-8') ?>)</td>
        </tr>
        <tr>
            <th style="background: transparent;">Penulis</th>
            <td><?= htmlspecialchars((string)$data['penulis'], ENT_QUOTES, 'UTF-8') ?> (<strong><?= htmlspecialchars(trim((string)$data['id_penulis']), ENT_QUOTES, 'UTF-8') ?></strong> — <?= htmlspecialchars((string)$data['nim_penulis'], ENT_QUOTES, 'UTF-8') ?>)</td>
        </tr>
        <tr>
            <th style="background: transparent;">Tautan Dokumen</th>
            <td>
                <?php if (!empty($data['file_karya'])): ?>
                    <a href="<?= htmlspecialchars((string)$data['file_karya'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-blue" style="padding: 4px 10px; font-size: 12px;">Buka File Karya</a>
                <?php else: ?>
                    <span style="color: #94a3b8;">-</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="background: transparent;">Layanan Review</th>
            <td><?= htmlspecialchars((string)$data['nama_layanan'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <tr>
            <th style="background: transparent;">Mentor Penilai</th>
            <td>
                <?php if (!empty($data['nama_mentor'])): ?>
                    <?= htmlspecialchars((string)$data['nama_mentor'], ENT_QUOTES, 'UTF-8') ?> (Spesialis <?= htmlspecialchars((string)$data['bidang_keahlian'], ENT_QUOTES, 'UTF-8') ?>)
                <?php else: ?>
                    <span style="color: #94a3b8; font-style: italic;">Belum ditentukan</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th style="background: transparent;">Status Review</th>
            <td>
                <span style="padding: 3px 9px; border-radius: 5px; font-size: 12px; font-weight: 600; background: <?= $badge_bg ?>; color: <?= $badge_color ?>;">
                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </td>
        </tr>
        <tr>
            <th style="background: transparent;">Tanggal Ajukan</th>
            <td><?= $tgl_ajukan_tampil ?></td>
        </tr>
        <tr>
            <th style="background: transparent;">Tanggal Review</th>
            <td><?= $tgl_review_tampil ?></td>
        </tr>
    </table>

    <?php if (hasRole('mentor')): ?>
        <form action="" method="POST">
            <label for="hasil_review">Catatan / Hasil Evaluasi Mentor:</label>
            <textarea id="hasil_review" name="hasil_review" rows="5" placeholder="Tuliskan masukan, poin revisi, atau hasil review di sini..."><?= htmlspecialchars((string)($data['hasil_review'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

            <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <button type="submit" name="status_action" value="Finished" class="btn btn-green">Finished</button>
                <button type="submit" name="status_action" value="Revision" class="btn btn-gray">Revision</button>
                <a href="index.php" class="btn" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">Kembali</a>
            </div>
        </form>
    <?php elseif (hasRole('admin')): ?>
        <form action="" method="POST" style="border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 8px;">
            <label for="id_mentor">Mentor Penilai (Sesuai Bidang <?= htmlspecialchars($bidang_karya, ENT_QUOTES, 'UTF-8') ?>)</label>
            <select id="id_mentor" name="id_mentor" required>
                <option value="">-- Pilih Mentor --</option>
                <?php while ($m = pg_fetch_assoc($mentor_list)): ?>
                    <?php $sel = (trim((string)($data['id_mentor'] ?? '')) === trim((string)$m['id_mentor'])) ? 'selected' : ''; ?>
                    <option value="<?= htmlspecialchars(trim((string)$m['id_mentor']), ENT_QUOTES, 'UTF-8') ?>" <?= $sel ?>>
                        <?= htmlspecialchars(trim((string)$m['id_mentor']), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string)$m['nama'], ENT_QUOTES, 'UTF-8') ?> (Spesialis: <?= htmlspecialchars((string)$m['bidang_keahlian'], ENT_QUOTES, 'UTF-8') ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="status_review">Status Review</label>
            <select id="status_review" name="status_review" required>
                <option value="Waiting" <?= $status === 'Waiting' ? 'selected' : '' ?>>Waiting</option>
                <option value="Revision" <?= $status === 'Revision' ? 'selected' : '' ?>>Revision</option>
                <option value="Finished" <?= $status === 'Finished' ? 'selected' : '' ?>>Finished</option>
            </select>

            <label for="hasil_review">Catatan / Hasil Evaluasi Mentor</label>
            <textarea id="hasil_review" name="hasil_review" rows="4" placeholder="Tuliskan masukan, poin revisi, atau hasil review di sini..."><?= htmlspecialchars((string)($data['hasil_review'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

            <div style="margin-top: 20px; display: flex; gap: 8px;">
                <button type="submit" class="btn btn-blue">Simpan Perubahan</button>
                <a href="index.php" class="btn btn-gray">Kembali</a>
            </div>
        </form>
    <?php else: ?>
        <label>Catatan / Hasil Evaluasi Mentor:</label>
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; min-height: 90px; white-space: pre-wrap; font-size: 14px;">
            <?= !empty($data['hasil_review']) ? htmlspecialchars((string)$data['hasil_review'], ENT_QUOTES, 'UTF-8') : '<span style="color:#94a3b8;">Belum ada catatan evaluasi dari mentor.</span>' ?>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <a href="index.php" class="btn btn-gray">Kembali</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../layout/footer.php'; ?>