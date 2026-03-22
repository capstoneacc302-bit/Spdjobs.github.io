<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_job') {
        $id          = (int)($_POST['job_id'] ?? 0);
        $title       = sanitize($_POST['title']       ?? '');
        $company     = sanitize($_POST['company']     ?? '');
        $category_id = (int)($_POST['category_id']   ?? 0);
        $shift       = sanitize($_POST['shift']       ?? 'Shifting');
        $emp_type    = sanitize($_POST['employment_type'] ?? 'Contract');
        $salary_min  = (float)($_POST['salary_min']  ?? 0);
        $salary_max  = (float)($_POST['salary_max']  ?? 0);
        $sal_display = sanitize($_POST['salary_display'] ?? '');
        $slots       = (int)($_POST['slots']         ?? 1);
        $desc        = sanitize($_POST['description'] ?? '');
        $resp        = sanitize($_POST['responsibilities'] ?? '');
        $reqs        = sanitize($_POST['requirements'] ?? '');
        $bens        = sanitize($_POST['benefits']    ?? '');
        $exp_req     = isset($_POST['experience_required']) ? 1 : 0;
        $fresh       = isset($_POST['accepts_fresh_grad'])  ? 1 : 0;
        $urgent      = isset($_POST['is_urgent'])           ? 1 : 0;
        $featured    = isset($_POST['is_featured'])         ? 1 : 0;
        $status      = sanitize($_POST['status']     ?? 'open');
        $deadline    = sanitize($_POST['deadline']   ?? '') ?: null;

        if ($id) {
            $upd = $db->prepare("UPDATE jobs SET category_id=?,title=?,company=?,shift=?,employment_type=?,salary_min=?,salary_max=?,salary_display=?,slots=?,description=?,responsibilities=?,requirements=?,benefits=?,experience_required=?,accepts_fresh_grad=?,is_urgent=?,is_featured=?,status=?,deadline=?,updated_at=NOW() WHERE id=?");
            $upd->bind_param('issssddssissssiiiissi', $category_id,$title,$company,$shift,$emp_type,$salary_min,$salary_max,$sal_display,$slots,$desc,$resp,$reqs,$bens,$exp_req,$fresh,$urgent,$featured,$status,$deadline,$id);
            $upd->execute();
            setFlash('success', 'Job post updated successfully.');
        } else {
            $ins = $db->prepare("INSERT INTO jobs (category_id,title,company,shift,employment_type,salary_min,salary_max,salary_display,slots,description,responsibilities,requirements,benefits,experience_required,accepts_fresh_grad,is_urgent,is_featured,status,deadline,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $ins->bind_param('issssddssissssiiiissi', $category_id,$title,$company,$shift,$emp_type,$salary_min,$salary_max,$sal_display,$slots,$desc,$resp,$reqs,$bens,$exp_req,$fresh,$urgent,$featured,$status,$deadline,$_SESSION['admin_id']);
            $ins->execute();
            setFlash('success', 'New job post created successfully.');
        }
        redirect(SITE_URL . '/admin/jobs.php');
    }

    if ($action === 'delete_job') {
        $id = (int)$_POST['job_id'];
        $db->prepare("DELETE FROM jobs WHERE id=?")->bind_param('i',$id)->execute();
        setFlash('success', 'Job post deleted.');
        redirect(SITE_URL . '/admin/jobs.php');
    }
}

$editJob = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM jobs WHERE id=?");
    $stmt->bind_param('i', (int)$_GET['edit']);
    $stmt->execute();
    $editJob = $stmt->get_result()->fetch_assoc();
}
$showForm = isset($_GET['action']) && $_GET['action']==='new' || $editJob;

$jobs = $db->query("SELECT j.*, c.name as cat_name, COUNT(a.id) as app_count FROM jobs j LEFT JOIN job_categories c ON j.category_id=c.id LEFT JOIN applications a ON j.id=a.job_id GROUP BY j.id ORDER BY j.created_at DESC");
$categories = $db->query("SELECT * FROM job_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs — Admin | SPD Jobs</title>
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
        <a href="jobs.php" style="color:var(--red);">Jobs</a>
        <a href="users.php">Applicants</a>
    </div>
    <a href="logout.php" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:12px;">Log Out</a>
</nav>

<div class="container page-wrap">
    <?= showFlash() ?>

    <?php if ($showForm): ?>
    <div class="page-header">
        <h1><?= $editJob ? 'Edit Job Post' : 'Create New Job Post' ?></h1>
        <a href="jobs.php" class="btn btn-secondary">← Back to Job List</a>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="save_job">
        <input type="hidden" name="job_id" value="<?= $editJob['id'] ?? 0 ?>">

        <div class="card">
            <div class="form-section-title">Basic Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Job Title <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($editJob['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company / Deployment Site</label>
                    <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($editJob['company'] ?? '') ?>" placeholder="e.g. Hermosa Ecozone — Electronics Mfg.">
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">Select category</option>
                        <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($editJob['category_id'] ?? 0)==$cat['id']?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Number of Slots</label>
                    <input type="number" name="slots" class="form-control" value="<?= $editJob['slots'] ?? 1 ?>" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Shift</label>
                    <select name="shift" class="form-control">
                        <?php foreach(['Day Shift','Night Shift','Shifting','Any'] as $s): ?>
                            <option value="<?=$s?>" <?= ($editJob['shift']??'')==$s?'selected':'' ?>><?=$s?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Employment Type</label>
                    <select name="employment_type" class="form-control">
                        <?php foreach(['Contract','Project-based','Regular'] as $t): ?>
                            <option value="<?=$t?>" <?= ($editJob['employment_type']??'')==$t?'selected':'' ?>><?=$t?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Min Salary (₱/day)</label>
                    <input type="number" name="salary_min" class="form-control" value="<?= $editJob['salary_min'] ?? '' ?>" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Salary (₱/day)</label>
                    <input type="number" name="salary_max" class="form-control" value="<?= $editJob['salary_max'] ?? '' ?>" step="0.01">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Salary Display Text</label>
                    <input type="text" name="salary_display" class="form-control" value="<?= htmlspecialchars($editJob['salary_display'] ?? '') ?>" placeholder="e.g. ₱570/day (minimum wage)">
                </div>
                <div class="form-group">
                    <label class="form-label">Application Deadline</label>
                    <input type="date" name="deadline" class="form-control" value="<?= $editJob['deadline'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="open" <?= ($editJob['status']??'open')=='open'?'selected':'' ?>>Open</option>
                        <option value="closed" <?= ($editJob['status']??'')=='closed'?'selected':'' ?>>Closed</option>
                        <option value="paused" <?= ($editJob['status']??'')=='paused'?'selected':'' ?>>Paused</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:0.75rem;">
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="checkbox" name="experience_required" <?= !empty($editJob['experience_required'])?'checked':'' ?>> Experience Required
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="checkbox" name="accepts_fresh_grad" <?= ($editJob['accepts_fresh_grad']??1)?'checked':'' ?>> Accepts Fresh Graduates
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="checkbox" name="is_urgent" <?= !empty($editJob['is_urgent'])?'checked':'' ?>> Urgent Hiring
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-size:14px;cursor:pointer;">
                    <input type="checkbox" name="is_featured" <?= !empty($editJob['is_featured'])?'checked':'' ?>> Featured on Homepage
                </label>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Job Description</div>
            <div class="form-group mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" placeholder="Brief description of the job..."><?= htmlspecialchars($editJob['description'] ?? '') ?></textarea></div>
            <div class="form-group mb-2"><label class="form-label">Responsibilities (one per line, use • bullet)</label><textarea name="responsibilities" class="form-control" rows="5"><?= htmlspecialchars($editJob['responsibilities'] ?? '') ?></textarea></div>
            <div class="form-group mb-2"><label class="form-label">Requirements (one per line, use • bullet)</label><textarea name="requirements" class="form-control" rows="5"><?= htmlspecialchars($editJob['requirements'] ?? '') ?></textarea></div>
            <div class="form-group"><label class="form-label">Benefits (one per line)</label><textarea name="benefits" class="form-control" rows="4"><?= htmlspecialchars($editJob['benefits'] ?? '') ?></textarea></div>
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <a href="jobs.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><?= $editJob ? 'Update Job Post' : 'Create Job Post' ?></button>
        </div>
    </form>

    <?php else: ?>
    <div class="page-header">
        <h1>Manage Job Posts</h1>
        <a href="jobs.php?action=new" class="btn btn-primary">+ Post New Job</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Category</th><th>Shift</th><th>Salary</th><th>Slots</th><th>Applications</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while ($job = $jobs->fetch_assoc()): ?>
            <tr>
                <td><strong><?= htmlspecialchars($job['title']) ?></strong><br><span class="text-xs text-muted"><?= htmlspecialchars($job['company']) ?></span></td>
                <td class="text-sm"><?= htmlspecialchars($job['cat_name'] ?? '—') ?></td>
                <td class="text-sm"><?= $job['shift'] ?></td>
                <td class="text-sm" style="color:var(--red);font-weight:600;"><?= htmlspecialchars($job['salary_display']) ?></td>
                <td class="text-sm"><?= $job['slots'] ?></td>
                <td class="text-sm"><?= $job['app_count'] ?> applicant<?= $job['app_count']!=1?'s':'' ?></td>
                <td>
                    <?php if ($job['status']=='open'): ?><span class="badge badge-open">Open</span>
                    <?php elseif($job['status']=='closed'): ?><span class="badge" style="background:#FEF2F2;color:#991B1B;border:1px solid #FCA5A5;">Closed</span>
                    <?php else: ?><span class="badge" style="background:var(--amber-light);color:#92620A;border:1px solid #FDE68A;">Paused</span><?php endif; ?>
                    <?php if ($job['is_urgent']): ?><span class="badge badge-urgent" style="margin-left:4px;">Urgent</span><?php endif; ?>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="jobs.php?edit=<?= $job['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                        <a href="../job-detail.php?id=<?= $job['id'] ?>" target="_blank" class="btn btn-sm" style="border:1px solid var(--gray-200);color:var(--gray-500);font-size:11px;">View</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this job post? This will also delete all applications for it.');">
                            <input type="hidden" name="action" value="delete_job">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
