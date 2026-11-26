<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole('admin');

// Tangkap peran dari tombol yang diklik (?role=admin / mentor / mahasiswa)
$selected_role = $_GET['role'] ?? 'mahasiswa';
if (!in_array($selected_role, ['admin', 'mentor', 'mahasiswa'])) {
    $selected_role = 'mahasiswa';
}

function generateIdByRole($conn, $role) {
    $prefix = 'U';
    if ($role === 'admin') {
        $prefix = 'A';
    } elseif ($role === 'mentor') {
        $prefix = 'M';
    }

    $res = pg_query_params($conn, "SELECT id_user FROM user_member WHERE id_user LIKE $1", [$prefix . '%']);
    $max_num = 0;
    while ($row = pg_fetch_assoc($res)) {
        $suffix = substr(trim($row['id_user']), 1);
        if (ctype_digit($suffix) && (int)$suffix > $max_num) {
            $max_num = (int)$suffix;
        }
    }
    return $prefix . str_pad($max_num + 1, 3, '0', STR_PAD_LEFT);
}

$initial_id = generateIdByRole($conn, $selected_role);
$judul_form = 'Tambah Mahasiswa Baru';
$label_peran = 'Mahasiswa (Awalan U)';

if ($selected_role === 'admin') {
    $judul_form  = 'Tambah Admin Baru';
    $label_peran = 'Admin Sistem (Awalan A)';
} elseif ($selected_role === 'mentor') {
    $judul_form  = 'Tambah Mentor Baru';
    $label_peran = 'Mentor Penilai (Awalan M)';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role_akun     = $_POST['role_akun'];
    if (!in_array($role_akun, ['admin', 'mentor', 'mahasiswa'])) {
        $role_akun = 'mahasiswa';
    }

    $id_user       = generateIdByRole($conn, $role_akun);
    $nama          = trim($_POST['nama']);
    $nim           = strtoupper(trim($_POST['nim']));
    $angkatan      = strtoupper(substr($nim, 0, 3));
    $kontak        = trim($_POST['kontak']);
    $password_raw  = $_POST['password'];
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    pg_query($conn, "BEGIN");

    $query_mhs = "INSERT INTO MAHASISWA (NIM, Nama, Angkatan, Kontak, Status_Mhs) VALUES ($1, $2, $3, $4, 'Aktif')";
    $res_mhs   = pg_query_params($conn, $query_mhs, array($nim, $nama, $angkatan, $kontak));

    $query_user = "INSERT INTO USER_MEMBER (ID_User, NIM, Password) VALUES ($1, $2, $3)";
    $res_user   = pg_query_params($conn, $query_user, array($id_user, $nim, $password_hash));

    $res_role = true;
    if ($role_akun === 'admin') {
        $jabatan  = trim($_POST['jabatan']) ?: 'Admin Klinik Prestasi';
        $res_role = pg_query_params($conn, "INSERT INTO ADMIN (ID_Admin, ID_User, Jabatan) VALUES ($1, $2, $3)", array($id_user, $id_user, $jabatan));
    } elseif ($role_akun === 'mentor') {
        $bidang   = $_POST['bidang_keahlian'] ?: 'Esai';
        $res_role = pg_query_params($conn, "INSERT INTO MENTOR (ID_Mentor, ID_User, Bidang_Keahlian, Status_Mentor) VALUES ($1, $2, $3, 'Aktif')", array($id_user, $id_user, $bidang));
    }

    if ($res_mhs && $res_user && $res_role) {
        pg_query($conn, "COMMIT");
        echo "<script>alert('Akun baru berhasil ditambahkan dengan ID: $id_user'); window.location='index.php';</script>";
    } else {
        pg_query($conn, "ROLLBACK");
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Gagal menyimpan data: " . htmlspecialchars(pg_last_error($conn)) . "</div>";
    }
}
?>

<h2><?= $judul_form ?></h2>
<div class="form-box">
    <form action="" method="POST" autocomplete="off">
        <h3>Data Akun & Identitas</h3>

        <label>Peran Akun (Otomatis)</label>
        <input type="text" value="<?= htmlspecialchars($label_peran) ?>" disabled>
        <input type="hidden" name="role_akun" value="<?= htmlspecialchars($selected_role) ?>">

        <label>ID User (Otomatis Sesuai Peran)</label>
        <input type="text" value="<?= htmlspecialchars($initial_id) ?>" disabled>

        <?php if ($selected_role === 'mentor'): ?>
            <label>Bidang Keahlian Mentor</label>
            <select name="bidang_keahlian" required>
                <option value="Esai">Esai</option>
                <option value="KTI">KTI</option>
                <option value="Bisnis">Bisnis</option>
                <option value="Design">Design</option>
            </select>
        <?php elseif ($selected_role === 'admin'): ?>
            <label>Jabatan Admin</label>
            <input type="text" name="jabatan" placeholder="Contoh: Koordinator Klinik Prestasi" autocomplete="off">
        <?php endif; ?>

        <label>Nama Lengkap</label>
        <input type="text" name="nama" required placeholder="Nama Lengkap" autocomplete="off">

        <label>NIM (11 Karakter)</label>
        <input type="text" name="nim" maxlength="11" required placeholder="Contoh: F2301234567" autocomplete="off">

        <label>Kontak (No. HP / WhatsApp)</label>
        <input type="text" name="kontak" required placeholder="0812xxxxxxxx" autocomplete="off">

        <label>Password Akun</label>
        <input type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter" autocomplete="new-password">

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-green">Simpan</button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<?php include '../layout/footer.php'; ?>