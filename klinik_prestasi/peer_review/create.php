<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mahasiswa']);

function generateIdReview($conn) {
    $res = pg_query($conn, "SELECT id_review FROM peer_review WHERE id_review LIKE 'R%'");
    $max_num = 0;
    while ($row = pg_fetch_assoc($res)) {
        $suffix = substr(trim($row['id_review']), 1);
        if (ctype_digit($suffix) && (int)$suffix > $max_num) {
            $max_num = (int)$suffix;
        }
    }
    return 'R' . str_pad($max_num + 1, 3, '0', STR_PAD_LEFT);
}

if (hasRole('admin')) {
    $karya_list = pg_query($conn, "SELECT ID_Karya, Judul_Karya, Bidang_Karya FROM KARYA ORDER BY ID_Karya ASC");
} else {
    $karya_list = pg_query_params(
        $conn,
        "SELECT ID_Karya, Judul_Karya, Bidang_Karya FROM KARYA WHERE ID_User = $1 ORDER BY ID_Karya ASC",
        [$_SESSION['user_id']]
    );
}

$mentor_res = pg_query($conn, "
    SELECT MT.ID_Mentor, M.Nama, MT.Bidang_Keahlian 
    FROM MENTOR MT
    JOIN USER_MEMBER U ON MT.ID_User = U.ID_User
    JOIN MAHASISWA M ON U.NIM = M.NIM
    WHERE M.Status_Mhs = 'Aktif'
    ORDER BY MT.ID_Mentor ASC
");

$mentors_data = [];
while ($m = pg_fetch_assoc($mentor_res)) {
    $mentors_data[] = [
        'id'       => trim($m['id_mentor']),
        'nama'     => $m['nama'],
        'keahlian' => trim($m['bidang_keahlian'])
    ];
}

$tanggal_pengajuan_hari_ini = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_review         = generateIdReview($conn);
    $id_karya          = trim($_POST['id_karya']);
    $id_mentor         = trim($_POST['id_mentor']);
    $tanggal_pengajuan = date('Y-m-d');

    $q_karya    = pg_query_params($conn, "SELECT ID_User, Bidang_Karya FROM KARYA WHERE ID_Karya = $1", [$id_karya]);
    $data_karya = pg_fetch_assoc($q_karya);

    if (!$data_karya || (hasRole('mahasiswa') && trim($data_karya['id_user']) !== $_SESSION['user_id'])) {
        echo "<script>alert('Karya tidak valid atau bukan milik Anda!'); window.location='index.php';</script>";
        exit;
    }

    $bidang   = trim($data_karya['bidang_karya']);
    $q_mentor = pg_query_params($conn, "SELECT Bidang_Keahlian FROM MENTOR WHERE ID_Mentor = $1", [$id_mentor]);
    $d_mentor = pg_fetch_assoc($q_mentor);

    if (!$d_mentor || strcasecmp(trim($d_mentor['bidang_keahlian']), $bidang) !== 0) {
        echo "<script>alert('Mentor yang dipilih tidak sesuai dengan bidang karya ($bidang)!'); window.location='create.php';</script>";
        exit;
    }

    $q_layanan = pg_query_params(
        $conn,
        "SELECT ID_Layanan FROM LAYANAN WHERE Nama_Layanan ILIKE $1 LIMIT 1",
        ['%' . $bidang . '%']
    );
    $data_layanan = pg_fetch_assoc($q_layanan);

    if (!$data_layanan) {
        echo "<script>alert('Jenis layanan untuk bidang $bidang belum tersedia di database.'); window.location='index.php';</script>";
        exit;
    }

    $id_layanan = trim($data_layanan['id_layanan']);
    $status     = 'Waiting';

    $query = "
        INSERT INTO PEER_REVIEW 
            (ID_Review, ID_Karya, ID_Layanan, ID_Mentor, Status_Review, Tanggal_Pengajuan, Tanggal_Review) 
        VALUES ($1, $2, $3, $4, $5, $6, NULL)
    ";
    $res = pg_query_params($conn, $query, array($id_review, $id_karya, $id_layanan, $id_mentor, $status, $tanggal_pengajuan));

    if ($res) {
        echo "<script>alert('Pengajuan review untuk karya $id_karya berhasil dikirim pada tanggal $tanggal_pengajuan!'); window.location='index.php';</script>";
    } else {
        echo "<div style='background:#fef2f2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:16px;'>Gagal: " . htmlspecialchars(pg_last_error($conn)) . "</div>";
    }
}
?>

<h2><?= hasRole('admin') ? 'Tugaskan Peer Review Baru' : 'Ajukan Peer Review Karya' ?></h2>
<div class="form-box">
    <form action="" method="POST">
        <label>Tanggal Ajukan Review (Otomatis)</label>
        <input type="text" value="<?= htmlspecialchars($tanggal_pengajuan_hari_ini) ?>" disabled>

        <label><?= hasRole('admin') ? 'Pilih Karya Mahasiswa' : 'Pilih Karya Saya' ?></label>
        <select name="id_karya" id="select_karya" required onchange="updateFormByKarya()">
            <option value="" data-bidang="">-- Pilih Karya --</option>
            <?php while ($k = pg_fetch_assoc($karya_list)): ?>
                <option value="<?= trim($k['id_karya']) ?>" data-bidang="<?= htmlspecialchars(trim($k['bidang_karya'])) ?>">
                    <?= htmlspecialchars($k['judul_karya']) ?> (<?= trim($k['id_karya']) ?> — <?= htmlspecialchars($k['bidang_karya']) ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Jenis Layanan Review (Otomatis Sesuai Bidang Karya)</label>
        <input type="text" id="preview_layanan" value="Pilih karya terlebih dahulu..." disabled>

        <label>Tugaskan Mentor</label>
        <select name="id_mentor" id="select_mentor" required>
            <option value="">-- Pilih Mentor --</option>
            <?php foreach ($mentors_data as $m): ?>
                <option value="<?= htmlspecialchars($m['id']) ?>">
                    <?= htmlspecialchars($m['id']) ?> — <?= htmlspecialchars($m['nama']) ?> (Spesialis: <?= htmlspecialchars($m['keahlian']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <div style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 10px 12px; border-radius: 7px; font-size: 13px; margin-top: 12px;">
            Status awal pengajuan adalah <strong>Waiting</strong>. Tanggal review akan terisi otomatis setelah mentor memeriksa karya (<strong>Revision</strong> atau <strong>Finished</strong>).
        </div>

        <div style="margin-top: 20px; display: flex; gap: 8px;">
            <button type="submit" class="btn btn-green"><?= hasRole('admin') ? 'Simpan Penugasan' : 'Kirim Pengajuan Review' ?></button>
            <a href="index.php" class="btn btn-gray">Batal</a>
        </div>
    </form>
</div>

<script>
const allMentors = <?= json_encode($mentors_data) ?>;

function updateFormByKarya() {
    const selectKarya = document.getElementById('select_karya');
    const previewLayanan = document.getElementById('preview_layanan');
    const selectMentor = document.getElementById('select_mentor');

    const selectedOption = selectKarya.options[selectKarya.selectedIndex];
    const bidang = selectedOption.getAttribute('data-bidang');

    previewLayanan.value = bidang ? ('Review ' + bidang) : 'Pilih karya terlebih dahulu...';

    const previousValue = selectMentor.value;
    selectMentor.innerHTML = '<option value="">-- Pilih Mentor --</option>';

    allMentors.forEach(m => {
        if (!bidang || m.keahlian.toLowerCase() === bidang.toLowerCase()) {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = `${m.id} — ${m.nama} (Spesialis: ${m.keahlian})`;
            if (m.id === previousValue) {
                opt.selected = true;
            }
            selectMentor.appendChild(opt);
        }
    });
}
</script>

<?php include '../layout/footer.php'; ?>