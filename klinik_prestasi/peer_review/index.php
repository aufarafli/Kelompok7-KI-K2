<?php
include '../config/db.php';
include '../layout/header.php';

$where  = "";
$params = [];

if (hasRole('mahasiswa')) {
    $where  = "WHERE K.ID_User = $1";
    $params = [$_SESSION['user_id']];
} elseif (hasRole('mentor')) {
    $where  = "WHERE PR.ID_Mentor = $1";
    $params = [$_SESSION['role_id']];
}

$query = "
    SELECT 
        PR.ID_Review, 
        PR.ID_Karya,
        K.Judul_Karya, 
        K.ID_User AS ID_Penulis,
        M_Penulis.Nama AS Penulis,
        L.Nama_Layanan, 
        M_Mentor.Nama AS Nama_Mentor,
        PR.Status_Review,
        PR.Tanggal_Pengajuan,
        PR.Tanggal_Review
    FROM PEER_REVIEW PR
    JOIN KARYA K ON PR.ID_Karya = K.ID_Karya
    JOIN USER_MEMBER U_Penulis ON K.ID_User = U_Penulis.ID_User
    JOIN MAHASISWA M_Penulis ON U_Penulis.NIM = M_Penulis.NIM
    JOIN LAYANAN L ON PR.ID_Layanan = L.ID_Layanan
    LEFT JOIN MENTOR MT ON PR.ID_Mentor = MT.ID_Mentor
    LEFT JOIN USER_MEMBER U_Mentor ON MT.ID_User = U_Mentor.ID_User
    LEFT JOIN MAHASISWA M_Mentor ON U_Mentor.NIM = M_Mentor.NIM
    $where
    ORDER BY PR.ID_Review DESC
";
$result = empty($params) ? pg_query($conn, $query) : pg_query_params($conn, $query, $params);

$reviews = [];
$counts  = [
    'All'      => 0,
    'Waiting'  => 0,
    'Revision' => 0,
    'Finished' => 0
];

while ($r = pg_fetch_assoc($result)) {
    $st = trim($r['status_review']);
    $r['status_clean'] = $st;
    $reviews[] = $r;
    $counts['All']++;
    if (isset($counts[$st])) {
        $counts[$st]++;
    }
}
?>

<h2>Jadwal & Hasil Peer Review</h2>

<!-- Baris Aksi Utama & Tombol Filter Status -->
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px;">
    <div id="filter-group" style="display: flex; flex-wrap: wrap; gap: 8px;">
        <button type="button" class="filter-btn filter-all active" data-filter="All" onclick="filterPeerReview('All', this)">
            Semua (<?= $counts['All'] ?>)
        </button>
        <button type="button" class="filter-btn filter-waiting" data-filter="Waiting" onclick="filterPeerReview('Waiting', this)">
            Waiting (<?= $counts['Waiting'] ?>)
        </button>
        <button type="button" class="filter-btn filter-revision" data-filter="Revision" onclick="filterPeerReview('Revision', this)">
            Revision (<?= $counts['Revision'] ?>)
        </button>
        <button type="button" class="filter-btn filter-finished" data-filter="Finished" onclick="filterPeerReview('Finished', this)">
            Finished (<?= $counts['Finished'] ?>)
        </button>
    </div>

    <?php if (hasRole(['admin', 'mahasiswa'])): ?>
        <a href="create.php" class="btn btn-green">
            <?= hasRole('admin') ? '+ Tugaskan Review Baru' : '+ Ajukan Review Karya' ?>
        </a>
    <?php endif; ?>
</div>

<style>
    .filter-btn {
        padding: 7px 14px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        border-radius: 7px;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .filter-all {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }
    .filter-all:hover { background-color: #dbeafe; }
    .filter-all.active {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
    }

    .filter-waiting {
        background-color: #fef3c7;
        border: 1px solid #fde68a;
        color: #b45309;
    }
    .filter-waiting:hover { background-color: #fde68a; }
    .filter-waiting.active {
        background-color: #d97706;
        border-color: #d97706;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(217, 119, 6, 0.25);
    }

    .filter-revision {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
    }
    .filter-revision:hover { background-color: #e2e8f0; }
    .filter-revision.active {
        background-color: #64748b;
        border-color: #64748b;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(100, 116, 139, 0.25);
    }

    .filter-finished {
        background-color: #d1fae5;
        border: 1px solid #a7f3d0;
        color: #047857;
    }
    .filter-finished:hover { background-color: #a7f3d0; }
    .filter-finished.active {
        background-color: #10b981;
        border-color: #10b981;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25);
    }
</style>

<table id="table-peer-review">
    <thead>
        <tr>
            <th>ID Karya</th>
            <th>Judul Karya</th>
            <?php if (!hasRole('mahasiswa')): ?>
                <th>Penulis</th>
            <?php endif; ?>
            <th>Layanan</th>
            <th>Mentor Penilai</th>
            <th>Status</th>
            <th>Tanggal Ajukan</th>
            <th>Tanggal Review</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reviews as $row): ?>
        <?php
            $status      = $row['status_clean'];
            $badge_bg    = '#fef3c7';
            $badge_color = '#b45309'; // Waiting (Kuning)
            if ($status === 'Revision') {
                $badge_bg    = '#e2e8f0';
                $badge_color = '#334155'; // Revision (Abu-abu)
            } elseif ($status === 'Finished') {
                $badge_bg    = '#d1fae5';
                $badge_color = '#047857'; // Finished (Hijau)
            }

            $tgl_ajukan = !empty($row['tanggal_pengajuan']) ? htmlspecialchars($row['tanggal_pengajuan']) : '-';
            $tgl_review = (in_array($status, ['Revision', 'Finished']) && !empty($row['tanggal_review']))
                ? htmlspecialchars($row['tanggal_review'])
                : '-';
        ?>
        <tr class="review-row" data-status="<?= htmlspecialchars($status) ?>">
            <td><strong><?= htmlspecialchars(trim($row['id_karya'])) ?></strong></td>
            <td><?= htmlspecialchars($row['judul_karya']) ?></td>
            <?php if (!hasRole('mahasiswa')): ?>
                <td><?= htmlspecialchars($row['penulis']) ?> (<strong><?= htmlspecialchars(trim($row['id_penulis'])) ?></strong>)</td>
            <?php endif; ?>
            <td><?= htmlspecialchars($row['nama_layanan']) ?></td>
            <td>
                <?php if ($row['nama_mentor']): ?>
                    <?= htmlspecialchars($row['nama_mentor']) ?>
                <?php else: ?>
                    <span style="color: #94a3b8; font-style: italic;">Belum ditentukan</span>
                <?php endif; ?>
            </td>
            <td>
                <span style="padding: 3px 9px; border-radius: 5px; font-size: 12px; font-weight: 600; background: <?= $badge_bg ?>; color: <?= $badge_color ?>;">
                    <?= htmlspecialchars($status) ?>
                </span>
            </td>
            <td><?= $tgl_ajukan ?></td>
            <td><?= $tgl_review ?></td>
            <td style="white-space: nowrap;">
                <?php if (hasRole('mentor')): ?>
                    <a href="detail.php?id=<?= trim($row['id_review']) ?>" class="btn btn-blue">Review</a>
                <?php elseif (hasRole('admin')): ?>
                    <a href="detail.php?id=<?= trim($row['id_review']) ?>" class="btn btn-blue">Kelola</a>
                    <a href="delete.php?id=<?= trim($row['id_review']) ?>" class="btn btn-red" onclick="return confirm('Hapus jadwal review ini?');">Hapus</a>
                <?php else: ?>
                    <a href="detail.php?id=<?= trim($row['id_review']) ?>" class="btn btn-gray">Detail</a>
                    <?php if ($status === 'Waiting'): ?>
                        <a href="delete.php?id=<?= trim($row['id_review']) ?>" class="btn btn-red" onclick="return confirm('Yakin ingin membatalkan dan menghapus pengajuan review untuk karya <?= htmlspecialchars(addslashes($row['judul_karya'])) ?>?');">Hapus</a>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr id="empty-filter-row" style="<?= count($reviews) > 0 ? 'display: none;' : '' ?>">
            <td colspan="<?= hasRole('mahasiswa') ? '8' : '9' ?>" style="text-align: center; color: #94a3b8; padding: 24px;">
                Tidak ada data peer review untuk kategori status ini.
            </td>
        </tr>
    </tbody>
</table>

<script>
function filterPeerReview(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const rows = document.querySelectorAll('.review-row');
    let visibleCount = 0;

    rows.forEach(row => {
        if (status === 'All' || row.getAttribute('data-status') === status) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const emptyRow = document.getElementById('empty-filter-row');
    if (emptyRow) {
        emptyRow.style.display = (visibleCount === 0) ? '' : 'none';
    }
}
</script>

<?php include '../layout/footer.php'; ?>