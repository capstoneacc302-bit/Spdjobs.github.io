<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$search = sanitize($_GET['search'] ?? '');
$where  = $search ? "WHERE u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%'" : '';

$users = $db->query("SELECT u.*, COUNT(a.id) as app_count FROM users u LEFT JOIN applications a ON u.id=a.user_id $where GROUP BY u.id ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants — Admin | SPD Jobs</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-logo">
        <div class="nav-logo-box" style="background:var(--gray-900);"><span>SPD</span></div>
        <div><div class="nav-brand-name">Admin Panel</div><div class="nav-brand-sub">SPD Jobs Bataan</div></div>
    </div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="applications.php">Applications</a>
        <a href="jobs.php">Jobs</a>
        <a href="users.php" style="color:var(--red);">Applicants</a>
    </div>
    <a href="logout.php" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:12px;">Log Out</a>
</nav>

<div class="container page-wrap">
    <div class="page-header">
        <h1>Registered Applicants</h1>
        <p>All accounts registered in the system</p>
    </div>

    <form method="GET" class="search-filter-bar">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?><a href="users.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Contact</th><th>Applications</th><th>Registered</th></tr></thead>
            <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td class="text-muted text-sm"><?= $u['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:30px;height:30px;border-radius:50%;background:var(--red);color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                            <?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?>
                        </div>
                        <strong><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></strong>
                    </div>
                </td>
                <td class="text-sm"><?= htmlspecialchars($u['email']) ?></td>
                <td class="text-sm"><?= htmlspecialchars($u['contact_number'] ?: '—') ?></td>
                <td><a href="applications.php?search=<?= urlencode($u['first_name'].' '.$u['last_name']) ?>" class="text-sm"><?= $u['app_count'] ?> application<?= $u['app_count']!=1?'s':'' ?></a></td>
                <td class="text-sm text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
