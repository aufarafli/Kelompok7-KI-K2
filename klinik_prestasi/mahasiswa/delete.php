<?php
include '../config/db.php';
require_once __DIR__ . '/../config/auth.php';
wajibRole('admin');

$nim = trim($_GET['nim'] ?? '');

if (empty($nim)) {
    echo "<script>alert('NIM tidak valid!'); window.location='index.php';</script>";
    exit;
}

// Cek data pengguna dan pastikan BUKAN akun Admin
$q_cek = pg_query_params($conn, "
    SELECT U.ID_User, M.Nama, A.ID_Admin, MT.ID_Mentor
    FROM MAHASISWA M
    LEFT JOIN USER_MEMBER U ON M.NIM = U.NIM
    LEFT JOIN ADMIN A ON U.ID_User = A.ID_User
    LEFT JOIN MENTOR MT ON U.ID_User = MT.ID_User
    WHERE M.NIM = $1
", [$nim]);

$target = pg_fetch_assoc($q_cek);

if (!$target) {
    echo "<script>alert('Data pengguna tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Proteksi: akun Admin tidak boleh dihapus lewat fitur ini
if (!empty($target['id_admin'])) {
    echo "<script>alert('Akses Ditolak! Akun Admin tidak dapat dihapus.'); window.location='index.php';</script>";
    exit;
}

$id_user   = trim($target['id_user'] ?? '');
$id_mentor = trim($target['id_mentor'] ?? '');
$nama      = $target['nama'];

pg_query($conn, "BEGIN");

$ok = true;

// 1. Jika yang dihapus adalah Mentor, lepaskan penugasannya di PEER_REVIEW dan hapus dari tabel MENTOR
if (!empty($id_mentor)) {
    $r1 = pg_query_params($conn, "UPDATE PEER_REVIEW SET ID_Mentor = NULL, Status_Review = 'Menunggu Admin' WHERE ID_Mentor = $1", [$id_mentor]);
    $r2 = pg_query_params($conn, "DELETE FROM MENTOR WHERE ID_Mentor = $1", [$id_mentor]);
    if (!$r1 || !$r2) $ok = false;
}

// 2. Jika memiliki akun USER_MEMBER, hapus data PEER_REVIEW terkait karyanya, KARYA, dan USER_MEMBER
if ($ok && !empty($id_user)) {
    $r3 = pg_query_params($conn, "DELETE FROM PEER_REVIEW WHERE ID_Karya IN (SELECT ID_Karya FROM KARYA WHERE ID_User = $1)", [$id_user]);
    $r4 = pg_query_params($conn, "DELETE FROM KARYA WHERE ID_User = $1", [$id_user]);
    $r5 = pg_query_params($conn, "DELETE FROM USER_MEMBER WHERE ID_User = $1", [$id_user]);
    if (!$r3 || !$r4 || !$r5) $ok = false;
}

// 3. Hapus biodata dari tabel MAHASISWA
if ($ok) {
    $r6 = pg_query_params($conn, "DELETE FROM MAHASISWA WHERE NIM = $1", [$nim]);
    if (!$r6) $ok = false;
}

if ($ok) {
    pg_query($conn, "COMMIT");
    echo "<script>alert('Data pengguna ($nama) berhasil dihapus!'); window.location='index.php';</script>";
} else {
    pg_query($conn, "ROLLBACK");
    $err = addslashes(pg_last_error($conn));
    echo "<script>alert('Gagal menghapus data: $err'); window.location='index.php';</script>";
}
?>