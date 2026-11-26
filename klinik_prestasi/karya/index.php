<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin', 'mahasiswa']);

if (hasRole('mahasiswa')) {
    $query  = "
        SELECT K.*, M.Nama AS Penulis, M.NIM
        FROM KARYA K
        JOIN USER_MEMBER U ON K.ID_User = U.ID_User
        JOIN MAHASISWA M ON U.NIM = M.NIM
        WHERE TRIM(K.ID_User) = $1
        ORDER BY K.ID_Karya ASC
    ";
    $result = pg_query_params($conn, $query, [trim((string)$_SESSION['user_id'])]);
} else {
    $query  = "
        SELECT K.*, M.Nama AS Penulis, M.NIM
        FROM KARYA K
        LEFT JOIN USER_MEMBER U ON K.ID_User = U.ID_User
        LEFT JOIN MAHASISWA M ON U.NIM = M.NIM
        ORDER BY K.ID_Karya ASC
    ";
    $result = pg_query($conn, $query);
}
?>

<h2>Data Karya</h2>

<div style="margin-bottom: 16px;">
    <a href="create.php" class="btn btn-green">+ Tambah Karya Baru</a>
</div>

<table>
    <thead>
        <tr>
            <th>ID Karya</th>
            <th>Judul Karya</th>
            <?php if (hasRole('admin')): ?>
                <th>Penulis</th>
            <?php endif; ?>
            <th>Bidang</th>
            <th>File Karya</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = pg_fetch_assoc($result)): ?>
        <?php $id_karya = htmlspecialchars(trim((string)$row['id_karya']), ENT_QUOTES, 'UTF-8'); ?>
        <tr>
            <td><strong><?= $id_karya ?></strong></td>
            <td><?= htmlspecialchars((string)$row['judul_karya'], ENT_QUOTES, 'UTF-8') ?></td>
            <?php if (hasRole('admin')): ?>
                <td>
                    <?= htmlspecialchars((string)($row['penulis'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                    (<strong><?= htmlspecialchars(trim((string)($row['id_user'] ?? '-')), ENT_QUOTES, 'UTF-8') ?></strong>)
                </td>
            <?php endif; ?>
            <td><?= htmlspecialchars((string)$row['bidang_karya'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if (!empty($row['file_karya'])): ?>
                    <a href="<?= htmlspecialchars((string)$row['file_karya'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="btn btn-blue" style="padding: 4px 10px; font-size: 12px;">Buka Link</a>
                <?php else: ?>
                    <span style="color: #94a3b8;">-</span>
                <?php endif; ?>
            </td>
            <td style="white-space: nowrap;">
                <a href="edit.php?id=<?= urlencode(trim((string)$row['id_karya'])) ?>" class="btn btn-gray">Edit</a>
                <a href="delete.php?id=<?= urlencode(trim((string)$row['id_karya'])) ?>" class="btn btn-red" onclick="return confirm('Hapus karya <?= $id_karya ?>?');">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../layout/footer.php'; ?>