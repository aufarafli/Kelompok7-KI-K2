<?php
include '../config/db.php';
require_once __DIR__ . '/../config/auth.php';
wajibRole(['admin', 'mahasiswa']);

$id = trim($_GET['id'] ?? '');

if (empty($id)) {
    echo "<script>alert('ID Review tidak valid!'); window.location='index.php';</script>";
    exit;
}

// Ambil data pengajuan review beserta pemilik karyanya
$q_cek = pg_query_params($conn, "
    SELECT PR.ID_Review, PR.Status_Review, K.ID_User AS ID_Penulis, K.Judul_Karya
    FROM PEER_REVIEW PR
    JOIN KARYA K ON PR.ID_Karya = K.ID_Karya
    WHERE PR.ID_Review = $1
", [$id]);

$data = pg_fetch_assoc($q_cek);

if (!$data) {
    echo "<script>alert('Data pengajuan review tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Validasi otorisasi khusus Mahasiswa
if (hasRole('mahasiswa')) {
    if (trim($data['id_penulis']) !== $_SESSION['user_id']) {
        echo "<script>alert('Akses Ditolak! Anda hanya dapat membatalkan pengajuan review milik sendiri.'); window.location='index.php';</script>";
        exit;
    }
    if (trim($data['status_review']) !== 'Waiting') {
        echo "<script>alert('Pengajuan tidak dapat dihapus karena sudah diproses oleh mentor (Status: " . trim($data['status_review']) . ").'); window.location='index.php';</script>";
        exit;
    }
}

$del = pg_query_params($conn, "DELETE FROM PEER_REVIEW WHERE ID_Review = $1", [$id]);

if ($del) {
    $pesan = hasRole('mahasiswa') ? 'Pengajuan review karya berhasil dibatalkan/dihapus!' : 'Jadwal review berhasil dihapus!';
    echo "<script>alert('$pesan'); window.location='index.php';</script>";
} else {
    $err = addslashes(pg_last_error($conn));
    echo "<script>alert('Gagal menghapus data: $err'); window.location='index.php';</script>";
}
?>