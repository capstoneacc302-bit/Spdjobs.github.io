<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = (int)$_POST['app_id'];
    $status = sanitize($_POST['status']);
    $notes  = sanitize($_POST['notes'] ?? '');
    $validStatuses = ['pending','for_exam','for_initial_interview','for_medical','for_final_interview','for_orientation','approved','declined'];
    if (in_array($status, $validStatuses)) {
        $upd = $db->prepare("UPDATE applications SET status=?, admin_notes=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?");
        $upd->bind_param('ssii', $status, $notes, $_SESSION['admin_id'], $app_id);
        $upd->execute();

        // Notify applicant
        $appInfo = $db->query("SELECT a.user_id, a.id, j.title FROM applications a JOIN jobs j ON a.job_id=j.id WHERE a.id=$app_id")->fetch_assoc();
        $statusLabels = ['pending'=>'Pending','for_exam'=>'For Exam','for_initial_interview'=>'For Initial Interview','for_medical'=>'For Medical','for_final_interview'=>'For Final Interview','for_orientation'=>'For Orientation','approved'=>'Approved / Hired','declined'=>'Declined'];
        $notifTitle = 'Application Status Updated';
        $notifMsg   = "Your application for '{$appInfo['title']}' has been updated to: " . $statusLabels[$status];
        if ($notes) $notifMsg .= ". Note from HR: $notes";
        $notif = $db->prepare("INSERT INTO notifications (user_id, application_id, title, message) VALUES (?,?,?,?)");
        $notif->bind_param('iiss', $appInfo['user_id'], $appInfo['id'], $notifTitle, $notifMsg);
        $notif->execute();

        setFlash('success', 'Application status updated successfully.');
    }
    redirect(SITE_URL . '/admin/applications.php');
}

$status_filter = sanitize($_GET['status'] ?? '');
$search        = sanitize($_GET['search'] ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15; $offset = ($page - 1) * $perPage;

$where = ['1=1'];
if ($status_filter) $where[] = "a.status='$status_filter'";
if ($search) $where[] = "(u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR j.title LIKE '%$search%')";
$whereSQL = implode(' AND ', $where);

$total = $db->query("SELECT COUNT(*) c FROM applications a JOIN users u ON a.user_id=u.id JOIN jobs j ON a.job_id=j.id WHERE $whereSQL")->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

$apps = $db->query("SELECT a.*, u.first_name, u.last_name, u.email AS user_email, u.contact_number, j.title as job_title, j.company, adm.full_name as reviewed_by_name
    FROM applications a
    JOIN users u ON a.user_id=u.id
    JOIN jobs j ON a.job_id=j.id
    LEFT JOIN admins adm ON a.reviewed_by=adm.id
    WHERE $whereSQL
    ORDER BY a.created_at DESC
    LIMIT $perPage OFFSET $offset");

$statusList = ['pending','for_exam','for_initial_interview','for_medical','for_final_interview','for_orientation','approved','declined'];
$statusLabels = ['pending'=>'Pending','for_exam'=>'For Exam','for_initial_interview'=>'Initial Interview','for_medical'=>'For Medical','for_final_interview'=>'Final Interview','for_orientation'=>'For Orientation','approved'=>'Approved / Hired','declined'=>'Declined'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications — Admin | SPD Jobs</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:12px; padding:1.5rem; width:480px; max-width:95vw; }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="nav-logo">
        <div class="nav-logo-box" style="background:var(--gray-900);"><span>SPD</span></div>
        <div><div class="nav-brand-name">Admin Panel</div><div class="nav-brand-sub">SPD Jobs Inc. Bataan</div></div>
    </div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="applications.php" style="color:var(--red);">Applications</a>
        <a href="jobs.php">Jobs</a>
        <a href="users.php">Applicants</a>
    </div>
    <a href="logout.php" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:12px;">Log Out</a>
</nav>

<div class="container page-wrap">
    <?= showFlash() ?>
    <div class="page-header">
        <h1>Applications Management</h1>
        <p>Review, approve, decline, and track all applicant statuses</p>
    </div>

    <form method="GET" class="search-filter-bar">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" class="form-control" placeholder="Search applicant name or job..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="status" class="form-control" style="width:auto;">
            <option value="">All Status</option>
            <?php foreach ($statusList as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter==$s?'selected':'' ?>><?= $statusLabels[$s] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($search || $status_filter): ?><a href="applications.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>

    <p class="text-sm text-muted mb-2">Showing <?= $total ?> application<?= $total!=1?'s':'' ?></p>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Position</th>
                    <th>Source</th>
                    <th>Date Applied</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($app = $apps->fetch_assoc()):
                $sl = statusLabel($app['status']); ?>
            <tr>
                <td class="text-sm text-muted"><?= $app['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($app['first_name'].' '.$app['last_name']) ?></strong><br>
                    <span class="text-xs text-muted"><?= htmlspecialchars($app['user_email']) ?></span>
                </td>
                <td class="text-sm"><?= htmlspecialchars($app['job_title']) ?><br><span class="text-xs text-muted"><?= htmlspecialchars($app['company']) ?></span></td>
                <td><span class="tag"><?= ucfirst($app['application_source']) ?></span></td>
                <td class="text-sm"><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                <td><span class="status-pill <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                <td>
                    <div class="action-btns">
                        <button onclick="openModal(<?= $app['id'] ?>, '<?= htmlspecialchars(addslashes($app['first_name'].' '.$app['last_name'])) ?>', '<?= $app['status'] ?>', '<?= htmlspecialchars(addslashes($app['admin_notes'] ?? '')) ?>')" class="btn btn-sm btn-secondary">Update Status</button>
                        <?php if ($app['resume_path']): ?>
                            <a href="<?= SITE_URL ?>/uploads/<?= $app['resume_path'] ?>" target="_blank" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:11px;">Resume</a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page>1): ?><a href="?page=<?=$page-1?>&status=<?=$status_filter?>&search=<?=urlencode($search)?>">‹</a><?php endif; ?>
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
            <?php if ($i==$page): ?><span class="current"><?=$i?></span>
            <?php else: ?><a href="?page=<?=$i?>&status=<?=$status_filter?>&search=<?=urlencode($search)?>"><?=$i?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page<$totalPages): ?><a href="?page=<?=$page+1?>&status=<?=$status_filter?>&search=<?=urlencode($search)?>">›</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal-box">
        <h3 style="font-size:16px;font-weight:600;margin-bottom:4px;">Update Application Status</h3>
        <p class="text-sm text-muted mb-3" id="modalApplicantName"></p>
        <form method="POST">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="app_id" id="modalAppId">
            <div class="form-group mb-2">
                <label class="form-label">New Status</label>
                <select name="status" id="modalStatus" class="form-control">
                    <?php foreach ($statusList as $s): ?>
                        <option value="<?= $s ?>"><?= $statusLabels[$s] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Note for Applicant (optional)</label>
                <textarea name="notes" id="modalNotes" class="form-control" placeholder="e.g. Please report to our office on Monday 8AM for your exam."></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id, name, status, notes) {
    document.getElementById('modalAppId').value = id;
    document.getElementById('modalApplicantName').textContent = 'Applicant: ' + name;
    document.getElementById('modalStatus').value = status;
    document.getElementById('modalNotes').value = notes;
    document.getElementById('statusModal').classList.add('open');
}
function closeModal() {
    document.getElementById('statusModal').classList.remove('open');
}
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
</body>
</html>
