<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hitung base_path relatif secara akurat dari lokasi folder config/auth.php ke folder skrip aktif
$app_root_real = realpath(__DIR__ . '/..');
$app_root_norm = $app_root_real ? str_replace('\\', '/', $app_root_real) : '';

$script_file_real = isset($_SERVER['SCRIPT_FILENAME']) ? realpath((string)$_SERVER['SCRIPT_FILENAME']) : false;
$script_dir_norm  = $script_file_real ? str_replace('\\', '/', dirname($script_file_real)) : '';

$base_path   = '';
$current_dir = '';

if ($app_root_norm !== '' && $script_dir_norm !== '') {
    if ($script_dir_norm === $app_root_norm) {
        $base_path   = '';
        $current_dir = '';
    } elseif (strpos($script_dir_norm, $app_root_norm . '/') === 0) {
        $rel_sub     = trim(substr($script_dir_norm, strlen($app_root_norm) + 1), '/');
        $segments    = $rel_sub !== '' ? explode('/', $rel_sub) : [];
        $current_dir = $segments[0] ?? '';
        $base_path   = str_repeat('../', count($segments));
    }
}

if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header("Location: " . $base_path . "login.php");
    exit;
}

function hasRole($roles): bool {
    $current = strtolower(trim((string)($_SESSION['role'] ?? '')));
    if ($current === '') {
        return false;
    }

    if (is_array($roles)) {
        foreach ($roles as $r) {
            if ($current === strtolower(trim((string)$r))) {
                return true;
            }
        }
        return false;
    }

    return $current === strtolower(trim((string)$roles));
}

function wajibRole($roles): void {
    global $base_path;
    if (!hasRole($roles)) {
        echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk halaman ini.'); window.location='" . $base_path . "index.php';</script>";
        exit;
    }
}