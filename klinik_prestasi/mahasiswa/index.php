<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole('admin');

// 1. Ambil Data Admin
$q_admin = pg_query($conn, "
    SELECT U.ID_User, M.NIM, M.Nama, M.Kontak, A.Jabatan
    FROM ADMIN A
    JOIN USER_MEMBER U ON A.ID_User = U.ID_User
    JOIN MAHASISWA M ON U.NIM = M.NIM
    ORDER BY U.ID_User ASC
");

// 2. Ambil Data Mentor
$q_mentor = pg_query($conn, "
    SELECT U.ID_User, M.NIM, M.Nama, M.Kontak, MT.Bidang_Keahlian
    FROM MENTOR MT
    JOIN USER_MEMBER U ON MT.ID_User = U.ID_User
    JOIN MAHASISWA M ON U.NIM = M.NIM
    ORDER BY U.ID_User ASC
");

// 3. Ambil Data Mahasiswa (User biasa yang bukan Admin maupun Mentor)
$q_mhs = pg_query($conn, "
    SELECT U.ID_User, M.NIM, M.Nama, M.Kontak
    FROM MAHASISWA M
    LEFT JOIN USER_MEMBER U ON M.NIM = U.NIM
    LEFT JOIN ADMIN A ON U.ID_User = A.ID_User
    LEFT JOIN MENTOR MT ON U.ID_User = MT.ID_User
    WHERE A.ID_Admin IS NULL AND MT.ID_Mentor IS NULL
    ORDER BY U.ID_User ASC, M.NIM ASC
");
?>

<h2>Manajemen Pengguna Klinik Prestasi</h2>

<!-- BAGIAN 1: ADMIN -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 12px;">
    <h3 style="margin: 0; font-size: 17px; color: #1e3a8a;">1. Daftar Admin</h3>
    <a href="create.php?role=admin" class="btn btn-green">+ Tambah Admin</a>
</div>
<table style="margin-bottom: 32px;">
    <thead>
        <tr>
            <th>ID User</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Jabatan</th>
            <th>Kontak</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = pg_fetch_assoc($q_admin)): ?>
        <tr>
            <td><strong><?= htmlspecialchars(trim($row['id_user'])) ?></strong></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['nim']) ?></td>
            <td><?= htmlspecialchars($row['jabatan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['kontak']) ?></td>
            <td>
                <a href="edit.php?nim=<?= trim($row['nim']) ?>" class="btn btn-blue">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- BAGIAN 2: MENTOR -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 12px;">
    <h3 style="margin: 0; font-size: 17px; color: #1e3a8a;">2. Daftar Mentor Penilai</h3>
    <a href="create.php?role=mentor" class="btn btn-green">+ Tambah Mentor</a>
</div>
<table style="margin-bottom: 32px;">
    <thead>
        <tr>
            <th>ID User</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Bidang Keahlian</th>
            <th>Kontak</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = pg_fetch_assoc($q_mentor)): ?>
        <tr>
            <td><strong><?= htmlspecialchars(trim($row['id_user'])) ?></strong></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['nim']) ?></td>
            <td><span style="background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 5px; font-size: 12px; font-weight: 600;"><?= htmlspecialchars($row['bidang_keahlian'] ?? '-') ?></span></td>
            <td><?= htmlspecialchars($row['kontak']) ?></td>
            <td style="white-space: nowrap;">
                <a href="edit.php?nim=<?= trim($row['nim']) ?>" class="btn btn-blue">Edit</a>
                <a href="delete.php?nim=<?= trim($row['nim']) ?>" class="btn btn-red" onclick="return confirm('Yakin ingin menghapus Mentor <?= htmlspecialchars(addslashes($row['nama'])) ?>? Penugasan review mentor ini akan dikembalikan ke status Menunggu Admin.');">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- BAGIAN 3: MAHASISWA -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; margin-bottom: 12px;">
    <h3 style="margin: 0; font-size: 17px; color: #1e3a8a;">3. Daftar Mahasiswa</h3>
    <a href="create.php?role=mahasiswa" class="btn btn-green">+ Tambah Mahasiswa</a>
</div>
<table>
    <thead>
        <tr>
            <th>ID User</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Kontak</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = pg_fetch_assoc($q_mhs)): ?>
        <tr>
            <td><strong><?= $row['id_user'] ? htmlspecialchars(trim($row['id_user'])) : '-' ?></strong></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['nim']) ?></td>
            <td><?= htmlspecialchars($row['kontak']) ?></td>
            <td style="white-space: nowrap;">
                <a href="edit.php?nim=<?= trim($row['nim']) ?>" class="btn btn-blue">Edit</a>
                <a href="delete.php?nim=<?= trim($row['nim']) ?>" class="btn btn-red" onclick="return confirm('Yakin ingin menghapus Mahasiswa <?= htmlspecialchars(addslashes($row['nama'])) ?> beserta seluruh karya dan riwayat review-nya?');">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../layout/footer.php'; ?>