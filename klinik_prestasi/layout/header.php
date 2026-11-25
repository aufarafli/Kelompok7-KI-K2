<?php
// Deteksi folder saat ini untuk mengatur path navigasi & menu aktif otomatis
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$is_root = ($current_dir === 'klinik_prestasi');
$base_path = $is_root ? '' : '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Prestasi — Sistem Manajemen Data & Review</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-soft: #eff6ff;
            --success: #10b981;
            --success-hover: #059669;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --secondary: #64748b;
            --secondary-hover: #475569;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --radius: 10px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar Modern */
        .navbar {
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            font-weight: 700;
            font-size: 18px;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand span {
            background: var(--primary);
            color: white;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 13px;
        }

        .nav-links {
            display: flex;
            gap: 6px;
        }

        .nav-links a {
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-muted);
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-links a:hover {
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .nav-links a.active {
            background-color: var(--primary-soft);
            color: var(--primary);
            font-weight: 600;
        }

        /* Kontainer Utama Halaman */
        .main-container {
            max-width: 1140px;
            width: 100%;
            margin: 32px auto;
            padding: 0 24px;
            flex: 1;
        }

        h1, h2, h3 {
            color: var(--text-main);
            margin-top: 0;
            font-weight: 700;
        }

        h2 {
            font-size: 22px;
            margin-bottom: 18px;
            letter-spacing: -0.02em;
        }

        /* Tombol Global (Kompatibel dengan kelas lama) */
        .btn {
            padding: 8px 14px;
            text-decoration: none;
            color: white;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover { transform: translateY(-1px); }
        .btn-green { background-color: var(--success); }
        .btn-green:hover { background-color: var(--success-hover); }
        .btn-blue { background-color: var(--primary); }
        .btn-blue:hover { background-color: var(--primary-hover); }
        .btn-red { background-color: var(--danger); }
        .btn-red:hover { background-color: var(--danger-hover); }
        .btn-gray { background-color: var(--secondary); }
        .btn-gray:hover { background-color: var(--secondary-hover); }

        /* Tabel Modern */
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: var(--bg-card);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            font-size: 14px;
        }

        th, td {
            padding: 13px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            border-left: none;
            border-right: none;
        }

        th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) { background-color: #fcfcfd; }
        tbody tr:hover { background-color: var(--primary-soft); transition: background 0.15s; }

        /* Form Box Modern */
        .form-box {
            background: var(--bg-card);
            padding: 28px;
            border: 1px solid var(--border-color);
            max-width: 560px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
        }

        .form-box h3 {
            font-size: 15px;
            color: var(--primary);
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
        }

        label {
            font-weight: 600;
            font-size: 13px;
            color: var(--text-main);
            display: block;
            margin-bottom: 6px;
            margin-top: 14px;
        }

        input, select, textarea {
            font-family: inherit;
            font-size: 14px;
            padding: 10px 12px;
            margin-bottom: 6px;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background-color: #fff;
            color: var(--text-main);
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        input:disabled {
            background-color: #f1f5f9 !important;
            color: var(--text-muted) !important;
            cursor: not-allowed;
        }

        /* Dashboard Cards (Untuk index.php) */
        .hero-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 36px 32px;
            border-radius: 14px;
            margin-bottom: 28px;
            box-shadow: var(--shadow-md);
        }
        .hero-header h1 { color: white; font-size: 26px; margin-bottom: 6px; }
        .hero-header p { color: #dbeafe; margin: 0; font-size: 15px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }
        .stat-card .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 600; }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: var(--text-main); margin-top: 4px; }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .menu-card {
            background: var(--bg-card);
            padding: 24px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .menu-card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .menu-card-desc {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .menu-card-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= $base_path ?>index.php" class="brand">
                <span>KP</span> Klinik Prestasi
            </a>
            <div class="nav-links">
                <a href="<?= $base_path ?>index.php" class="<?= $is_root ? 'active' : '' ?>">Home</a>
                <a href="<?= $base_path ?>mahasiswa/index.php" class="<?= ($current_dir === 'mahasiswa') ? 'active' : '' ?>">Data Mahasiswa</a>
                <a href="<?= $base_path ?>karya/index.php" class="<?= ($current_dir === 'karya') ? 'active' : '' ?>">Data Karya</a>
                <a href="<?= $base_path ?>peer_review/index.php" class="<?= ($current_dir === 'peer_review') ? 'active' : '' ?>">Peer Review</a>
            </div>
        </div>
    </nav>
    <main class="main-container">