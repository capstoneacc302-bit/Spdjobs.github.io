<?php
require_once 'includes/header.php';
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $db->prepare("SELECT j.*, c.name as cat_name FROM jobs j LEFT JOIN job_categories c ON j.category_id=c.id WHERE j.id=? AND j.status='open'");
$stmt->bind_param('i', $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
if (!$job) { header('Location: jobs.php'); exit; }
$pageTitle = $job['title'];

// Check if user already applied
$alreadyApplied = false;
if (isLoggedIn()) {
    $chk = $db->prepare("SELECT id FROM applications WHERE user_id=? AND job_id=?");
    $chk->bind_param('ii', $_SESSION['user_id'], $id);
    $chk->execute();
    $alreadyApplied = $chk->get_result()->num_rows > 0;
}
?>

<div class="container page-wrap">
    <p class="text-sm text-muted mb-2"><a href="jobs.php">← Back to Jobs</a></p>

    <div style="background:var(--red);border-radius:var(--radius);padding:1.75rem 2rem;color:#fff;margin-bottom:1rem;">
        <p style="font-size:12px;opacity:0.75;margin-bottom:4px;"><?= htmlspecialchars($job['cat_name'] ?? '') ?></p>
        <h1 style="font-size:26px;font-weight:600;margin-bottom:6px;"><?= htmlspecialchars($job['title']) ?></h1>
        <p style="font-size:14px;opacity:0.88;"><?= htmlspecialchars($job['company']) ?></p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px;">
            <span style="background:rgba(255,255,255,0.15);border-radius:5px;padding:3px 10px;font-size:12px;"><?= $job['shift'] ?></span>
            <span style="background:rgba(255,255,255,0.15);border-radius:5px;padding:3px 10px;font-size:12px;"><?= $job['employment_type'] ?></span>
            <span style="background:rgba(255,255,255,0.15);border-radius:5px;padding:3px 10px;font-size:12px;"><?= $job['slots'] ?> slot<?= $job['slots']!=1?'s':'' ?> available</span>
            <?php if ($job['is_urgent']): ?><span style="background:#fff;color:var(--red);border-radius:5px;padding:3px 10px;font-size:12px;font-weight:700;">🔥 URGENT HIRING</span><?php endif; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1rem;align-items:start;">
        <div>
            <div class="card">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:0.75rem;">About this Job</h3>
                <p style="font-size:14px;color:var(--gray-700);line-height:1.8;"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
            </div>

            <div class="card">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:0.75rem;">Responsibilities</h3>
                <div style="font-size:14px;color:var(--gray-700);line-height:2;"><?= nl2br(htmlspecialchars($job['responsibilities'])) ?></div>
            </div>

            <div class="card">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:0.75rem;">Document Requirements</h3>
                <div class="req-grid">
                    <?php foreach (explode("\n", $job['requirements']) as $req):
                        $req = trim($req, "• \n\r");
                        if (!$req) continue; ?>
                    <div class="req-item"><div class="req-dot"></div><?= htmlspecialchars($req) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:0.75rem;">Benefits</h3>
                <div style="font-size:14px;color:var(--gray-700);line-height:2;"><?= nl2br(htmlspecialchars($job['benefits'])) ?></div>
            </div>
        </div>

        <div style="position:sticky;top:80px;">
            <div class="card">
                <h3 style="font-size:15px;font-weight:600;margin-bottom:1rem;">Job Summary</h3>
                <table style="width:100%;font-size:13px;border-collapse:collapse;">
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Salary</td><td style="padding:6px 0;font-weight:600;color:var(--red);"><?= htmlspecialchars($job['salary_display']) ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Shift</td><td style="padding:6px 0;"><?= $job['shift'] ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Type</td><td style="padding:6px 0;"><?= $job['employment_type'] ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Slots</td><td style="padding:6px 0;"><?= $job['slots'] ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Location</td><td style="padding:6px 0;"><?= htmlspecialchars($job['location']) ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Experience</td><td style="padding:6px 0;"><?= $job['experience_required'] ? 'Required' : 'Not required' ?></td></tr>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Fresh Grads</td><td style="padding:6px 0;"><?= $job['accepts_fresh_grad'] ? '✅ Welcome' : '❌ No' ?></td></tr>
                    <?php if ($job['deadline']): ?>
                    <tr><td style="padding:6px 0;color:var(--gray-500);">Deadline</td><td style="padding:6px 0;color:var(--red);"><?= date('M d, Y', strtotime($job['deadline'])) ?></td></tr>
                    <?php endif; ?>
                </table>
                <hr class="divider">
                <?php if ($alreadyApplied): ?>
                    <div class="alert alert-success">✅ You have already applied for this job. <a href="dashboard.php">View status →</a></div>
                <?php elseif (isLoggedIn()): ?>
                    <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary btn-block">Apply for this Job</a>
                <?php else: ?>
                    <a href="login.php?redirect=apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary btn-block">Sign In to Apply</a>
                    <a href="register.php" class="btn btn-secondary btn-block mt-1">Create Free Account</a>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top:1rem;">
                <h3 style="font-size:14px;font-weight:600;margin-bottom:8px;">Contact SPD Jobs Bataan</h3>
                <p style="font-size:13px;color:var(--gray-600);line-height:2;">
                    📍 Manalo Village, Palihan, Hermosa, Bataan<br>
                    📞 Globe: <?= COMPANY_GLOBE ?><br>
                    📞 Smart: <?= COMPANY_SMART ?><br>
                    📞 Sun: <?= COMPANY_SUN ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
