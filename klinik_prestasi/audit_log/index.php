<?php
include '../config/db.php';
include '../layout/header.php';
wajibRole(['admin']);

$filter_role = trim((string)($_GET['role'] ?? ''));
$search_q    = trim((string)($_GET['q'] ?? ''));

$conditions = [];
$params     = [];
$idx        = 1;

if ($filter_role !== '' && in_array(strtolower($filter_role), ['admin', 'mentor', 'mahasiswa', 'guest'], true)) {
    $conditions[] = "LOWER(TRIM(role_user)) = $" . $idx;
    $params[]     = strtolower($filter_role);
    $idx++;
}

if ($search_q !== '') {
    $conditions[] = "(id_user ILIKE $" . $idx . " OR nama_user ILIKE $" . $idx . " OR aksi ILIKE $" . $idx . " OR target_id ILIKE $" . $idx . " OR keterangan ILIKE $" . $idx . ")";
    $params[]     = '%' . $search_q . '%';
    $idx++;
}

$where_sql = !empty($conditions) ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$query = "
    SELECT 
        id_log,
        id_user,
        nama_user,
        role_user,
        aksi,
        modul,
        target_id,
        keterangan,
        ip_address,
        TO_CHAR(waktu_kejadian, 'YYYY-MM-DD HH24:MI:SS') AS waktu_formatted
    FROM public.audit_log
    $where_sql
    ORDER BY id_log DESC
    LIMIT 200
";

$result = empty($params) ? pg_query($conn, $query) : pg_query_params($conn, $query, $params);
?>

<h2>Audit Log Aktivitas Pengguna (Accounting)</h2>

<form method="GET" style="background: #ffffff; padding: 16px; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 18px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
    <select name="role" style="width: auto; min-width: 160px; margin: 0;">
        <option value="">-- Semua Role --</option>
        <option value="admin" <?= strtolower($filter_role) === 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="mentor" <?= strtolower($filter_role) === 'mentor' ? 'selected' : '' ?>>Mentor</option>
        <option value="mahasiswa" <?= strtolower($filter_role) === 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
        <option value="guest" <?= strtolower($filter_role) === 'guest' ? 'selected' : '' ?>>Guest (Gagal Login)</option>
    </select>

    <input type="text" name="q" value="<?= htmlspecialchars($search_q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari ID User, nama, aksi, atau target ID..." style="flex: 1; min-width: 220px; margin: 0;">

    <button type="submit" class="btn btn-blue">Filter Log</button>
    <a href="index.php" class="btn btn-gray">Reset</a>
</form>

<table>
    <thead>
        <tr>
            <th>Waktu (WIB)</th>
            <th>Pengguna</th>
            <th>Role</th>
            <th>Aksi</th>
            <th>Modul</th>
            <th>Target ID</th>
            <th>Keterangan</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        <?php $has_rows = false; ?>
        <?php while ($row = pg_fetch_assoc($result)): ?>
        <?php
            $has_rows = true;
            $role_clean = strtolower(trim((string)($row['role_user'] ?? 'guest')));
            $r_bg = '#f1f5f9';
            $r_tx = '#475569';
            if ($role_clean === 'admin') {
                $r_bg = '#eff6ff';
                $r_tx = '#1d4ed8';
            } elseif ($role_clean === 'mentor') {
                $r_bg = '#fef3c7';
                $r_tx = '#b45309';
            } elseif ($role_clean === 'mahasiswa') {
                $r_bg = '#d1fae5';
                $r_tx = '#047857';
            }
        ?>
        <tr>
            <td style="white-space: nowrap; font-size: 13px;"><?= htmlspecialchars((string)($row['waktu_formatted'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <strong><?= htmlspecialchars((string)($row['nama_user'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($row['id_user'])): ?>
                    <br><span style="font-size: 12px; color: #64748b;"><?= htmlspecialchars(trim((string)$row['id_user']), ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </td>
            <td>
                <span style="padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: <?= $r_bg ?>; color: <?= $r_tx ?>;">
                    <?= htmlspecialchars($role_clean, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </td>
            <td><strong><?= htmlspecialchars((string)($row['aksi'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars((string)($row['modul'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><strong><?= !empty($row['target_id']) ? htmlspecialchars(trim((string)$row['target_id']), ENT_QUOTES, 'UTF-8') : '-' ?></strong></td>
            <td style="font-size: 13px; color: #334155;"><?= htmlspecialchars((string)($row['keterangan'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td style="font-size: 12px; color: #64748b;"><?= htmlspecialchars((string)($row['ip_address'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endwhile; ?>

        <?php if (!$has_rows): ?>
        <tr>
            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">
                Belum ada catatan audit log yang sesuai dengan filter.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../layout/footer.php'; ?>