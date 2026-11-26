<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mahasiswa']);

// Fungsi pembantu untuk membuat ID Karya otomatis: [ID_User]K[001..999] (Contoh: U001K001)
function generateIdKarya($conn,$id_user) {
    $id_user = trim($id_user);
    $res = pg_query_params($conn,
        "SELECT id_karya FROM karya WHERE id_user = $1 AND id_karya LIKE $2",
        [$id_user,$id_user . 'K%']
    );

    $max_num = 0;
    while ($row = pg_fetch_assoc($res)) {$suffix = substr(trim($row['id_karya']), strlen($id_user) + 1);
        if (ctype_digit($suffix) && (int)$suffix >$max_num) {
            $max_num = (int)$suffix;
        }
    }
    return $id_user . 'K' . str_pad($max_num + 1, 3, '0', STR_PAD_LEFT);
}

// Ambil HANYA pengguna Mahasiswa (bukan Admin maupun Mentor) yang berstatus Aktif
$users_res = pg_query($conn, "
    SELECT U.ID_User, M.Nama, M.NIM 
    FROM USER_MEMBER U 
    JOIN MAHASISWA M ON U.NIM = M.NIM 
    LEFT JOIN ADMIN A ON U.ID_User = A.ID_User
    LEFT JOIN MENTOR MT ON U.ID_User = MT.ID_User
    WHERE M.Status_Mhs = 'Aktif' 
      AND A.ID_Admin IS NULL 
      AND MT.ID_Mentor IS NULL
      AND TRIM(U.ID_User) LIKE 'U%'
    ORDER BY U.ID_User ASC
");

$mahasiswa_list = [];
while ($u = pg_fetch_assoc($users_res)) {$uid = trim($u['id_user']);$u['next_karya_id'] = generateIdKarya($conn,$uid);
    $mahasiswa_list[] =$u;
}

$preview_id = hasRole('mahasiswa') ? generateIdKarya($conn,$_SESSION['user_id']) : 'Pilih penulis mahasiswa terlebih dahulu...';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user  = hasRole('admin') ? trim($_POST['id_user']) : $_SESSION['user_id'];$id_karya = generateIdKarya($conn,$id_user);
    $judul    =$_POST['judul'];
    $bidang   =$_POST['bidang'];
    $file     =$_POST['file'];

    $query = "INSERT INTO KARYA (ID_Karya, ID_User, Judul_Karya, Bidang_Karya, File_Karya) VALUES ($1, $2, $3, $4, $5)";
    $res   = pg_query_params($conn, $query, array($id_karya, $id_user,$judul, $bidang,$file));

    if ($res) {
        echo "<script>alert('Karya berhasil ditambahkan dengan ID: $id_karya'); window.location='index.php';</script>";
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Gagal: " . htmlspecialchars(pg_last_error($conn)) . "</div>";
    }
}
?>

<h2>Tambah Karya Baru</h2>
<div class="form-box">
    <form action="" method="POST">
        <label>ID Karya (Otomatis)</label>
        <input type="text" id="preview_id_karya" value="<?= htmlspecialchars($preview_id) ?>" disabled>

        <label>Penulis (Mahasiswa)</label>
        <?php if (hasRole('admin')): ?>
            <select name="id_user" id="select_penulis" onchange="updatePreviewIdKarya()" required>
                <option value="" data-next="Pilih penulis mahasiswa terlebih dahulu...">-- Pilih Penulis --</option>
                <?php foreach ($mahasiswa_list as$u): ?>
                    <option value="<?= trim($u['id_user']) ?>" data-next="<?= htmlspecialchars($u['next_karya_id']) ?>">
                        <?= trim($u['id_user']) ?> — <?= htmlspecialchars($u['nama']) ?> (<?= trim($u['nim']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input type="text" value="<?= htmlspecialchars($_SESSION['nama']) ?> (<?= htmlspecialchars($_SESSION['user_id']) ?>)" disabled>
        <?php endif; ?>

        <label>Judul Karya</label>
        <input type="text" name="judul" required placeholder="Judul lengkap karya...">

        <label>Bidang Karya</label>
        <select name="bidang" required>
            <option value="Esai">Esai</option>
            <option value="KTI">KTI</option>
            <option value="Bisnis">Bisnis</option>
            <option value="Design">Design</option>
        </select>

        <label>Link File Karya (Google Drive / GitHub)</label>
        <input type="url" name="file" required placeholder="https://drive.google.com/...">

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-green">Simpan Karya</button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<script>
function updatePreviewIdKarya() {
    const select = document.getElementById('select_penulis');
    const preview = document.getElementById('preview_id_karya');
    if (select && preview) {
        const selected = select.options[select.selectedIndex];
        preview.value = selected.getAttribute('data-next') || 'Pilih penulis mahasiswa terlebih dahulu...';
    }
}