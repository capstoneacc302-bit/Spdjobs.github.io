<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$total_apps     = $db->query("SELECT COUNT(*) c FROM applications")->fetch_assoc()['c'];
$pending        = $db->query("SELECT COUNT(*) c FROM applications WHERE status='pending'")->fetch_assoc()['c'];
$approved       = $db->query("SELECT COUNT(*) c FROM applications WHERE status='approved'")->fetch_assoc()['c'];
$active_jobs    = $db->query("SELECT COUNT(*) c FROM jobs WHERE status='open'")->fetch_assoc()['c'];
$this_month     = $db->query("SELECT COUNT(*) c FROM applications WHERE MONTH(created_at)=MONTH(NOW())")->fetch_assoc()['c'];

// Pipeline counts
$pipeline = [];
foreach(['pending','for_exam','for_initial_interview','for_medical','for_final_interview','for_orientation'] as $s) {
    $r = $db->query("SELECT COUNT(*) c FROM applications WHERE status='$s'");
    $pipeline[$s] = $r->fetch_assoc()['c'];
}

// Recent applications
$recent = $db->query("SELECT a.*, u.first_name, u.last_name, j.title as job_title FROM applications a JOIN users u ON a.user_id=u.id JOIN jobs j ON a.job_id=j.id ORDER BY a.created_at DESC LIMIT 10");

$statusLabels = ['pending'=>'Pending','for_exam'=>'For Exam','for_initial_interview'=>'Initial Interview','for_medical'=>'For Medical','for_final_interview'=>'Final Interview','for_orientation'=>'For Orientation','approved'=>'Approved','declined'=>'Declined'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — SPD Jobs</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-logo">
        <div class="nav-logo-box" style="background:var(--gray-900);"><span>SPD</span></div>
        <div><div class="nav-brand-name">Admin Panel</div><div class="nav-brand-sub">SPD Jobs Inc. Bataan</div></div>
    </div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="applications.php">Applications</a>
        <a href="jobs.php">Manage Jobs</a>
        <a href="users.php">Applicants</a>
    </div>
    <div class="nav-user">
        <span style="font-size:13px;color:var(--gray-600);"><?= htmlspecialchars($_SESSION['admin_name']) ?> (<?= ucfirst($_SESSION['admin_role']) ?>)</span>
        <a href="logout.php" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:12px;">Log Out</a>
    </div>
</nav>

<div class="container page-wrap">
    <?= showFlash() ?>
    <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p>Overview of recruitment activity — SPD Jobs Bataan Branch</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:1.5rem;">
        <div class="stat-card"><div class="stat-label">Total Applicants</div><div class="stat-value blue"><?= $total_apps ?></div></div>
        <div class="stat-card"><div class="stat-label">Pending Review</div><div class="stat-value amber"><?= $pending ?></div></div>
        <div class="stat-card"><div class="stat-label">Hired / Approved</div><div class="stat-value green"><?= $approved ?></div></div>
        <div class="stat-card"><div class="stat-label">Active Job Posts</div><div class="stat-value red"><?= $active_jobs ?></div></div>
        <div class="stat-card"><div class="stat-label">This Month</div><div class="stat-value"><?= $this_month ?></div></div>
    </div>

    <!-- Pipeline -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Application Pipeline</h3><a href="applications.php" class="text-sm text-red">View all →</a></div>
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;">
            <?php
            $pipeColors = ['pending'=>'#D97706','for_exam'=>'#2563EB','for_initial_interview'=>'#7C3AED','for_medical'=>'#059669','for_final_interview'=>'#EA580C','for_orientation'=>'#9A3412'];
            $pipeLabels2 = ['pending'=>'Pending','for_exam'=>'For Exam','for_initial_interview'=>'Initial Int.','for_medical'=>'For Medical','for_final_interview'=>'Final Int.','for_orientation'=>'Orientation'];
            foreach ($pipeline as $s => $count): ?>
            <div style="background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-sm);padding:1rem;text-align:center;">
                <div style="font-size:11px;color:var(--gray-500);margin-bottom:4px;"><?= $pipeLabels2[$s] ?></div>
                <div style="font-size:24px;font-weight:600;color:<?= $pipeColors[$s] ?>;"><?= $count ?></div>
                <a href="applications.php?status=<?= $s ?>" style="font-size:11px;color:var(--gray-400);">View →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Applications -->
    <div style="display:grid;grid-template-columns:1fr 340px;gap:1rem;">
        <div class="table-wrap">
            <div style="padding:0.85rem 1rem;border-bottom:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;">
                <h3 style="font-size:15px;font-weight:600;">Recent Applications</h3>
                <a href="applications.php" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Applicant</th><th>Position</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php while ($app = $recent->fetch_assoc()):
                    $sl = statusLabel($app['status']); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($app['first_name'].' '.$app['last_name']) ?></strong></td>
                    <td class="text-sm"><?= htmlspecialchars($app['job_title']) ?></td>
                    <td class="text-sm text-muted"><?= date('M d', strtotime($app['created_at'])) ?></td>
                    <td><span class="status-pill <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                    <td><a href="applications.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-secondary">Review</a></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3>Quick Actions</h3></div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="jobs.php?action=new" class="btn btn-primary">+ Post New Job</a>
                    <a href="applications.php?status=pending" class="btn btn-secondary">Review Pending Applications</a>
                    <a href="applications.php" class="btn btn-secondary">All Applications</a>
                    <a href="jobs.php" class="btn btn-secondary">Manage Job Posts</a>
                    <a href="users.php" class="btn btn-secondary">View All Applicants</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
