<?php
$pageTitle = 'Browse Jobs';
require_once 'includes/header.php';
$db = getDB();

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$shift     = isset($_GET['shift'])    ? sanitize($_GET['shift'])    : '';
$search    = isset($_GET['search'])   ? sanitize($_GET['search'])   : '';
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 9;
$offset    = ($page - 1) * $perPage;

$where = ["j.status='open'"];
$params = [];
$types  = '';

if ($category) { $where[] = "j.category_id=?"; $params[] = $category; $types .= 'i'; }
if ($shift)    { $where[] = "j.shift=?";        $params[] = $shift;    $types .= 's'; }
if ($search)   { $where[] = "(j.title LIKE ? OR j.company LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$totalRow = $db->query("SELECT COUNT(*) c FROM jobs j $whereSQL" . ($types ? ' -- parameterized separately' : ''))->fetch_assoc();

// Use prepared statements
$stmt = $db->prepare("SELECT j.*, c.name as cat_name FROM jobs j LEFT JOIN job_categories c ON j.category_id=c.id $whereSQL ORDER BY j.is_urgent DESC, j.is_featured DESC, j.created_at DESC LIMIT ? OFFSET ?");
if ($types) {
    $params[] = $perPage; $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$jobs = $stmt->get_result();

$countStmt = $db->prepare("SELECT COUNT(*) c FROM jobs j $whereSQL");
if ($types && strlen($types) > 2) {
    $countParams = array_slice($params, 0, -2);
    $countTypes  = substr($types, 0, -2);
    if ($countTypes) $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

$categories = $db->query("SELECT * FROM job_categories ORDER BY name");

$icons = ['Production Worker'=>'🏭','Production Helper'=>'🏭','Assembler'=>'🔩','Machine Operator'=>'⚙️','Packaging Staff'=>'📦','Quality Control Inspector'=>'🔍','Quality Assurance Staff'=>'📋','Warehouse Staff'=>'🏪','Inventory Clerk'=>'📊','Maintenance Technician'=>'🔧','Engineering Assistant'=>'📐','Stock Controller'=>'🗂️'];
?>

<div class="container page-wrap">
    <div class="page-header">
        <h1>Available Jobs</h1>
        <p>Showing <?= $total ?> open position<?= $total != 1 ? 's' : '' ?> at SPD Jobs Bataan Branch</p>
    </div>

    <form method="GET" class="search-filter-bar">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="search" class="form-control" placeholder="Search job title or company..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="category" class="form-control" style="width:auto;">
            <option value="">All Categories</option>
            <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select name="shift" class="form-control" style="width:auto;">
            <option value="">All Shifts</option>
            <option value="Day Shift" <?= $shift=='Day Shift' ? 'selected':'' ?>>Day Shift</option>
            <option value="Night Shift" <?= $shift=='Night Shift' ? 'selected':'' ?>>Night Shift</option>
            <option value="Shifting" <?= $shift=='Shifting' ? 'selected':'' ?>>Shifting</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search || $category || $shift): ?>
            <a href="jobs.php" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($jobs->num_rows === 0): ?>
        <div class="alert alert-info">No jobs found matching your search. <a href="jobs.php">View all jobs</a></div>
    <?php else: ?>
    <div class="jobs-grid">
        <?php while ($job = $jobs->fetch_assoc()):
            $icon = $icons[$job['title']] ?? '💼';
        ?>
        <div class="job-card" onclick="location.href='job-detail.php?id=<?= $job['id'] ?>'">
            <div class="job-card-top">
                <div class="job-icon"><?= $icon ?></div>
                <?php if ($job['is_urgent']): ?>
                    <span class="badge badge-urgent">Urgent</span>
                <?php elseif (strtotime($job['created_at']) > strtotime('-7 days')): ?>
                    <span class="badge badge-new">New</span>
                <?php else: ?>
                    <span class="badge badge-open">Open</span>
                <?php endif; ?>
            </div>
            <div class="job-title"><?= htmlspecialchars($job['title']) ?></div>
            <div class="job-company"><?= htmlspecialchars($job['company']) ?></div>
            <div class="job-tags">
                <span class="tag"><?= $job['shift'] ?></span>
                <span class="tag"><?= $job['employment_type'] ?></span>
                <?php if ($job['accepts_fresh_grad']): ?><span class="tag">Fresh grads ok</span><?php endif; ?>
                <?php if ($job['slots']): ?><span class="tag"><?= $job['slots'] ?> slots</span><?php endif; ?>
            </div>
            <?php if ($job['cat_name']): ?><div class="text-xs text-muted mb-1"><?= htmlspecialchars($job['cat_name']) ?></div><?php endif; ?>
            <div class="job-salary"><?= htmlspecialchars($job['salary_display']) ?></div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&shift=<?= urlencode($shift) ?>">‹</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&shift=<?= urlencode($shift) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&shift=<?= urlencode($shift) ?>">›</a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
