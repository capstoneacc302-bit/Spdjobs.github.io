<?php
$pageTitle = 'My Dashboard';
require_once 'includes/header.php';
requireLogin();
$db  = getDB();
$uid = $_SESSION['user_id'];

$userStmt = $db->prepare("SELECT * FROM users WHERE id=?");
$userStmt->bind_param('i', $uid);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$appsStmt = $db->prepare("SELECT a.*, j.title as job_title, j.company FROM applications a JOIN jobs j ON a.job_id=j.id WHERE a.user_id=? ORDER BY a.created_at DESC");
$appsStmt->bind_param('i', $uid);
$appsStmt->execute();
$apps = $appsStmt->get_result();

$notifStmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$notifStmt->bind_param('i', $uid);
$notifStmt->execute();
$notifs = $notifStmt->get_result();

$unread = $db->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0");
$unread->bind_param('i', $uid);
$unread->execute();
$unreadCount = $unread->get_result()->fetch_assoc()['c'];

// Mark notifications as read
$db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->bind_param('i', $uid) && null;

$statusOrder = ['pending','for_exam','for_initial_interview','for_medical','for_final_interview','for_orientation','approved','declined'];
$statusLabels = ['pending'=>'Applied','for_exam'=>'For Exam','for_initial_interview'=>'Initial Interview','for_medical'=>'For Medical','for_final_interview'=>'Final Interview','for_orientation'=>'Orientation','approved'=>'Hired','declined'=>'Declined'];

// Latest active application
$latestStmt = $db->prepare("SELECT a.*, j.title as job_title FROM applications a JOIN jobs j ON a.job_id=j.id WHERE a.user_id=? AND a.status NOT IN ('declined') ORDER BY a.created_at DESC LIMIT 1");
$latestStmt->bind_param('i', $uid);
$latestStmt->execute();
$latest = $latestStmt->get_result()->fetch_assoc();
?>

<div class="container page-wrap">
    <?= showFlash() ?>

    <div style="display:grid;grid-template-columns:220px 1fr;gap:1.5rem;align-items:start;">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active">📊 My Dashboard</a>
                <a href="dashboard.php?section=applications">📄 My Applications</a>
                <a href="dashboard.php?section=profile">👤 Edit Profile</a>
                <a href="jobs.php">🔍 Browse Jobs</a>
                <a href="logout.php">🚪 Log Out</a>
            </nav>
        </div>

        <!-- MAIN CONTENT -->
        <div>
            <!-- Profile Card -->
            <div class="card" style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
                <div style="width:60px;height:60px;border-radius:50%;background:var(--red);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:600;flex-shrink:0;">
                    <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
                </div>
                <div>
                    <h2 style="font-size:18px;font-weight:600;"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                    <p class="text-muted text-sm"><?= htmlspecialchars($user['email']) ?> | <?= htmlspecialchars($user['contact_number'] ?: 'No contact on file') ?></p>
                    <p class="text-xs text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
                </div>
                <a href="dashboard.php?section=profile" class="btn btn-secondary btn-sm" style="margin-left:auto;">Edit Profile</a>
            </div>

            <?php if ($unreadCount): ?>
            <div class="alert alert-info">🔔 You have <?= $unreadCount ?> new notification<?= $unreadCount > 1 ? 's' : '' ?>.</div>
            <?php endif; ?>

            <?php if ($latest): ?>
            <!-- Application Progress Timeline -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header">
                    <h3>Application Progress</h3>
                    <span class="text-sm text-muted"><?= htmlspecialchars($latest['job_title']) ?></span>
                </div>
                <div class="timeline">
                    <?php
                    $steps = ['pending','for_exam','for_initial_interview','for_medical','for_final_interview','for_orientation','approved'];
                    $currentIdx = array_search($latest['status'], $steps);
                    if ($latest['status'] === 'declined') $currentIdx = -1;
                    foreach ($steps as $i => $step):
                        $isDone    = $currentIdx !== false && $i < $currentIdx;
                        $isCurrent = $currentIdx !== false && $i === $currentIdx;
                        $isFirst   = $i === 0;
                    ?>
                    <?php if (!$isFirst): ?>
                        <div class="t-line <?= $isDone ? 'done' : '' ?>"></div>
                    <?php endif; ?>
                    <div class="t-step">
                        <div class="t-dot <?= $isDone ? 'done' : ($isCurrent ? 'current' : '') ?>">
                            <?= $isDone ? '✓' : ($i + 1) ?>
                        </div>
                        <div class="t-label <?= ($isDone || $isCurrent) ? 'active' : '' ?>"><?= $statusLabels[$step] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($latest['status'] === 'declined'): ?>
                    <div class="alert alert-danger mt-2">Your application for this position has been declined.</div>
                <?php elseif ($latest['admin_notes']): ?>
                    <div class="alert alert-info mt-2"><strong>Message from HR:</strong> <?= htmlspecialchars($latest['admin_notes']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Applications Table -->
            <div class="card-header" style="background:none;border:none;padding:0;margin-bottom:0.75rem;">
                <h3 class="section-heading" style="margin:0;">My Applications</h3>
                <a href="jobs.php" class="btn btn-primary btn-sm">+ Apply for New Job</a>
            </div>
            <?php if ($apps->num_rows === 0): ?>
                <div class="alert alert-info">You haven't applied for any jobs yet. <a href="jobs.php">Browse available jobs →</a></div>
            <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Job Position</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Shift</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($app = $apps->fetch_assoc()):
                            $sl = statusLabel($app['status']);
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($app['job_title']) ?></strong></td>
                            <td class="text-sm text-muted"><?= htmlspecialchars($app['company']) ?></td>
                            <td class="text-sm"><?= date('M d, Y', strtotime($app['created_at'])) ?></td>
                            <td class="text-sm"><?= $app['preferred_shift'] ?></td>
                            <td><span class="status-pill <?= $sl['class'] ?>"><?= $sl['label'] ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Notifications -->
            <?php if ($notifs->num_rows > 0): ?>
            <div class="card" style="margin-top:1rem;">
                <div class="card-header"><h3>Recent Notifications</h3></div>
                <?php while ($notif = $notifs->fetch_assoc()): ?>
                <div style="padding:10px 0;border-bottom:1px solid var(--gray-100);">
                    <p style="font-weight:600;font-size:14px;"><?= htmlspecialchars($notif['title']) ?></p>
                    <p class="text-sm text-muted"><?= htmlspecialchars($notif['message']) ?></p>
                    <p class="text-xs text-muted"><?= timeAgo($notif['created_at']) ?></p>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
