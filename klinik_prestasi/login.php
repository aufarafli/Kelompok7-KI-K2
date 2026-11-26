<?php
session_start();
include 'config/db.php';
require_once __DIR__ . '/config/audit.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($login_id !== '' && $password !== '') {
        $query = "
            SELECT 
                U.id_user, 
                U.nim, 
                U.password, 
                M.nama, 
                M.status_mhs,
                A.id_admin,
                A.jabatan,
                MT.id_mentor,
                MT.status_mentor
            FROM user_member U
            JOIN mahasiswa M ON U.nim = M.nim
            LEFT JOIN admin A ON U.id_user = A.id_user
            LEFT JOIN mentor MT ON U.id_user = MT.id_user
            WHERE UPPER(U.id_user) = UPPER($1) OR UPPER(U.nim) = UPPER($1)
            LIMIT 1
        ";

        $result = pg_query_params($conn, $query, array($login_id));
        $user = $result ? pg_fetch_assoc($result) : false;

        if ($user && password_verify($password, (string)$user['password'])) {
            if (trim((string)$user['status_mhs']) !== 'Aktif') {
                $error = "Akun Anda berstatus Nonaktif. Hubungi Admin Klinik Prestasi.";
                catatAuditLog(
                    $conn,
                    'LOGIN_FAILED',
                    'AUTH',
                    trim((string)$user['id_user']),
                    'Percobaan login pada akun Nonaktif (' . $login_id . ')',
                    trim((string)$user['id_user']),
                    (string)$user['nama'],
                    'guest'
                );
            } else {
                session_regenerate_id(true);

                $role = 'mahasiswa';
                $role_id = (string)$user['id_user'];

                if (!empty($user['id_admin'])) {
                    $role = 'admin';
                    $role_id = (string)$user['id_admin'];
                } elseif (!empty($user['id_mentor']) && trim((string)$user['status_mentor']) === 'Aktif') {
                    $role = 'mentor';
                    $role_id = (string)$user['id_mentor'];
                }

                $_SESSION['user_id'] = trim((string)$user['id_user']);
                $_SESSION['nim']     = trim((string)$user['nim']);
                $_SESSION['nama']    = (string)$user['nama'];
                $_SESSION['role']    = $role;
                $_SESSION['role_id'] = trim($role_id);

                catatAuditLog(
                    $conn,
                    'LOGIN_SUCCESS',
                    'AUTH',
                    $_SESSION['user_id'],
                    'Berhasil masuk ke sistem sebagai ' . $role
                );

                header("Location: index.php");
                exit;
            }
        } else {
            $error = "NIM / ID User atau Password salah!";
            catatAuditLog(
                $conn,
                'LOGIN_FAILED',
                'AUTH',
                $login_id,
                'Kredensial salah untuk input: ' . $login_id,
                null,
                $login_id,
                'guest'
            );
        }
    } else {
        $error = "Harap isi NIM/ID User dan Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Klinik Prestasi UKM Forces IPB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 36px 32px;
            border-radius: 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }
        .badge-kp {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
        }
        h1 { font-size: 22px; margin: 0 0 6px 0; color: #0f172a; }
        p.subtitle { font-size: 14px; color: #64748b; margin: 0 0 24px 0; }
        label { display: block; font-size: 13px; font-weight: 600; color: #0f172a; margin-bottom: 6px; }
        input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 16px;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        button:hover { background: #1d4ed8; }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .hint-box {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px dashed #e2e8f0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.7;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <span class="badge-kp">UKM FORCES IPB</span>
        <h1>Masuk ke Klinik Prestasi</h1>
        <p class="subtitle">Silakan masuk menggunakan NIM atau ID User Anda.</p>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <label for="login_id">NIM atau ID User</label>
            <input type="text" id="login_id" name="login_id" required placeholder="Contoh: A001, M001, atau U001" autocomplete="off" autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="Masukkan password" autocomplete="new-password">

            <button type="submit">Masuk ke Sistem</button>
        </form>

        <div class="hint-box">
            <strong>Format ID User Pengguna:</strong><br>
            &bull; Admin: <code>A001</code> (Awalan <strong>A</strong> + 3 digit angka ID Anda)<br>
            &bull; Mentor: <code>M001</code> (Awalan <strong>M</strong> + 3 digit angka ID Anda)<br>
            &bull; Mahasiswa: <code>U001</code> (Awalan <strong>U</strong> + 3 digit angka Anda)<br>
        </div>
    </div>
</body>
</html>