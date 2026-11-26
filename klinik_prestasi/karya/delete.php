<?php
include '../config/db.php';
require_once __DIR__ . '/../config/auth.php';
wajibRole(['admin', 'mahasiswa']);

$id = trim((string)($_GET['id'] ?? ''));
if ($id === '') {
    echo "<script>alert('ID karya tidak valid!'); window.location='index.php';</script>";
    exit;
}

$res  = pg_query_params($conn, "SELECT ID_Karya, ID_User FROM KARYA WHERE TRIM(ID_Karya) = $1", [$id]);
$data = pg_fetch_assoc($res);

if (!$data) {
    echo "<script>alert('Data karya tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Anti-IDOR: Mahasiswa hanya boleh menghapus karyanya sendiri
if (hasRole('mahasiswa') && trim((string)($data['id_user'] ?? '')) !== trim((string)$_SESSION['user_id'])) {
    echo "<script>alert('Akses Ditolak! Karya ini bukan milik Anda.'); window.location='index.php';</script>";
    exit;
}

pg_query($conn, "BEGIN");
pg_query_params($conn, "DELETE FROM PEER_REVIEW WHERE TRIM(ID_Karya) = $1", [$id]);
$del = pg_query_params($conn, "DELETE FROM KARYA WHERE TRIM(ID_Karya) = $1", [$id]);

if ($del) {
    pg_query($conn, "COMMIT");
    echo "<script>alert('Karya berhasil dihapus!'); window.location='index.php';</script>";
} else {
    pg_query($conn, "ROLLBACK");
    $err = addslashes((string)pg_last_error($conn));
    echo "<script>alert('Gagal menghapus karya: $err'); window.location='index.php';</script>";
}