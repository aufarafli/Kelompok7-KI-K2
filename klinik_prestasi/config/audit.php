<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function catatAuditLog(
    $conn,
    string $aksi,
    string $modul,
    ?string $target_id = null,
    ?string $keterangan = null,
    ?string $override_user_id = null,
    ?string $override_nama = null,
    ?string $override_role = null
): void {
    if (!$conn) {
        return;
    }

    $id_user   = $override_user_id ?? (isset($_SESSION['user_id']) ? trim((string)$_SESSION['user_id']) : null);
    $nama_user = $override_nama ?? (isset($_SESSION['nama']) ? trim((string)$_SESSION['nama']) : 'Guest');
    $role_user = $override_role ?? (isset($_SESSION['role']) ? strtolower(trim((string)$_SESSION['role'])) : 'guest');

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    $query = "
        INSERT INTO public.audit_log 
            (id_user, nama_user, role_user, aksi, modul, target_id, keterangan, ip_address, waktu_kejadian)
        VALUES 
            ($1, $2, $3, $4, $5, $6, $7, $8, (NOW() AT TIME ZONE 'Asia/Jakarta'))
    ";

    @pg_query_params($conn, $query, [
        $id_user !== '' ? $id_user : null,
        $nama_user !== '' ? $nama_user : 'Guest',
        $role_user !== '' ? $role_user : 'guest',
        trim($aksi),
        trim($modul),
        ($target_id !== null && trim($target_id) !== '') ? trim($target_id) : null,
        $keterangan,
        substr((string)$ip, 0, 45)
    ]);
}