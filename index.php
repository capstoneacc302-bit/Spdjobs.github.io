<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$db = getDB();

// Featured jobs
$jobs = $db->query("SELECT * FROM jobs WHERE status='open' ORDER BY is_urgent DESC, is_featured DESC, created_at DESC LIMIT 6");

// Stats
$total_jobs   = $db->query("SELECT COUNT(*) c FROM jobs WHERE status='open'")->fetch_assoc()['c'];
$total_apps   = $db->query("SELECT COUNT(*) c FROM applications WHERE MONTH(created_at)=MONTH(NOW())")->fetch_assoc()['c'];
$total_hired  = $db->query("SELECT COUNT(*) c FROM applications WHERE status='approved' AND MONTH(created_at)=MONTH(NOW())")->fetch_assoc()['c'];

// Ads
$ads = $db->query("SELECT * FROM advertisements WHERE is_active=1 AND (end_date IS NULL OR end_date >= CURDATE()) LIMIT 2");
?>

<section class="hero">
    <div class="hero-content">
        <h1>Find Your Job in Bataan's Industrial Zone</h1>
        <p>Connecting workers to Hermosa Ecozone factories and manufacturing companies. Fresh graduates welcome — no experience needed.</p>
        <div class="hero-buttons">
            <a href="jobs.php" class="btn btn-white">Browse Jobs</a>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-ghost">Apply Now — It's Free</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-ghost">My Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="container">
    <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Open Positions</div><div class="stat-value red"><?= $total_jobs ?></div></div>
        <div class="stat-card"><div class="stat-label">Applicants This Month</div><div class="stat-value"><?= $total_apps ?></div></div>
        <div class="stat-card"><div class="stat-label">Hired This Month</div><div class="stat-value red"><?= $total_hired ?></div></div>
        <div class="stat-card"><div class="stat-label">Deployment Sites</div><div class="stat-value">10+</div></div>
    </div>

    <?php while ($ad = $ads->fetch_assoc()): ?>
    <div class="ad-banner">
        <div>
            <div class="ad-tag">ANNOUNCEMENT</div>
            <h3><?= htmlspecialchars($ad['title']) ?></h3>
            <p><?= htmlspecialchars($ad['body']) ?></p>
        </div>
        <a href="<?= $ad['cta_link'] ?: 'jobs.php' ?>" class="btn btn-primary" style="white-space:nowrap;"><?= htmlspecialchars($ad['cta_text']) ?></a>
    </div>
    <?php endwhile; ?>

    <div class="section-heading mt-3">In-Demand Jobs</div>
    <p class="section-sub">Actively hiring — limited slots available</p>

    <div class="jobs-grid">
        <?php
        $icons = ['Production Worker'=>'🏭','Production Helper'=>'🏭','Assembler'=>'🔩','Machine Operator'=>'⚙️','Packaging Staff'=>'📦','Quality Control Inspector'=>'🔍','Quality Assurance Staff'=>'📋','Warehouse Staff'=>'🏪','Inventory Clerk'=>'📊','Maintenance Technician'=>'🔧','Engineering Assistant'=>'📐','Stock Controller'=>'🗂️'];
        while ($job = $jobs->fetch_assoc()):
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
            </div>
            <div class="job-salary"><?= htmlspecialchars($job['salary_display']) ?></div>
        </div>
        <?php endwhile; ?>
    </div>

    <div style="text-align:center;margin:1.5rem 0;">
        <a href="jobs.php" class="btn btn-secondary">View All Jobs →</a>
    </div>

    <!-- HOW TO APPLY -->
    <div class="card mt-3" id="about">
        <div class="card-header"><h2>How to Apply at SPD Jobs Bataan</h2></div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:32px;margin-bottom:10px;">1️⃣</div>
                <p style="font-weight:600;margin-bottom:5px;">Create Account</p>
                <p style="font-size:13px;color:var(--gray-500);">Sign up with your email to get started. Free and easy.</p>
            </div>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:32px;margin-bottom:10px;">2️⃣</div>
                <p style="font-weight:600;margin-bottom:5px;">Browse Jobs</p>
                <p style="font-size:13px;color:var(--gray-500);">Find the position that matches your skills and availability.</p>
            </div>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:32px;margin-bottom:10px;">3️⃣</div>
                <p style="font-weight:600;margin-bottom:5px;">Submit Application</p>
                <p style="font-size:13px;color:var(--gray-500);">Fill out the online form and upload your documents.</p>
            </div>
            <div style="text-align:center;padding:1rem;">
                <div style="font-size:32px;margin-bottom:10px;">4️⃣</div>
                <p style="font-weight:600;margin-bottom:5px;">Track Your Status</p>
                <p style="font-size:13px;color:var(--gray-500);">Monitor your application progress from your dashboard.</p>
            </div>
        </div>
    </div>

    <!-- REQUIREMENTS -->
    <div class="card mt-3">
        <div class="card-header"><h2>Basic Requirements to Apply</h2><span style="font-size:13px;color:var(--gray-400);">Walk-in or Online</span></div>
        <div class="req-grid">
            <div class="req-item"><div class="req-dot"></div>Resume / Biodata with recent 2x2 photo</div>
            <div class="req-item"><div class="req-dot"></div>1–2 Valid Government IDs (National ID, UMID, etc.)</div>
            <div class="req-item"><div class="req-dot"></div>High School / SHS or College Diploma</div>
            <div class="req-item"><div class="req-dot"></div>Barangay Clearance (sometimes required)</div>
            <div class="req-item"><div class="req-dot"></div>2x2 ID Photos – white background</div>
            <div class="req-item"><div class="req-dot"></div>PSA Birth Certificate (preferred)</div>
        </div>
        <p class="text-sm text-muted mt-2">💡 No SSS/PhilHealth/Pag-IBIG yet? SPD can assist you in processing your government numbers after hiring.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
