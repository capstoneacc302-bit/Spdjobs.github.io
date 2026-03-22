<?php
$pageTitle = 'Apply for Job';
require_once 'includes/header.php';
requireLogin();
$db = getDB();

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$stmt = $db->prepare("SELECT * FROM jobs WHERE id=? AND status='open'");
$stmt->bind_param('i', $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
if (!$job) { header('Location: jobs.php'); exit; }

// Check already applied
$chk = $db->prepare("SELECT id FROM applications WHERE user_id=? AND job_id=?");
$chk->bind_param('ii', $_SESSION['user_id'], $job_id);
$chk->execute();
if ($chk->get_result()->num_rows > 0) {
    setFlash('warning', 'You have already applied for this job.');
    redirect(SITE_URL . '/dashboard.php');
}

// Prefill with user profile
$userStmt = $db->prepare("SELECT * FROM users WHERE id=?");
$userStmt->bind_param('i', $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name'       => sanitize($_POST['first_name']    ?? ''),
        'last_name'        => sanitize($_POST['last_name']     ?? ''),
        'email'            => sanitize($_POST['email']         ?? ''),
        'contact'          => sanitize($_POST['contact']       ?? ''),
        'dob'              => sanitize($_POST['dob']           ?? ''),
        'gender'           => sanitize($_POST['gender']        ?? ''),
        'address'          => sanitize($_POST['address']       ?? ''),
        'education'        => sanitize($_POST['education']     ?? ''),
        'school'           => sanitize($_POST['school']        ?? ''),
        'course'           => sanitize($_POST['course']        ?? ''),
        'year_grad'        => sanitize($_POST['year_grad']     ?? ''),
        'shift'            => sanitize($_POST['shift']         ?? 'Any Shift'),
        'source'           => sanitize($_POST['source']        ?? 'online'),
    ];

    if (!$data['first_name']) $errors[] = 'First name is required.';
    if (!$data['last_name'])  $errors[] = 'Last name is required.';
    if (!$data['address'])    $errors[] = 'Address is required.';
    if (!$data['education'])  $errors[] = 'Educational background is required.';

    // Handle file uploads
    $uploadDir = __DIR__ . '/uploads/';
    $uploads = [];
    $allowedTypes = ['image/jpeg','image/png','image/jpg','application/pdf'];

    foreach (['resume','valid_id','diploma','photo'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            if (!in_array($_FILES[$field]['type'], $allowedTypes)) {
                $errors[] = ucfirst($field) . ': Only JPG, PNG, PDF files are allowed.';
            } elseif ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
                $errors[] = ucfirst($field) . ': File must be under 5MB.';
            } else {
                $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $fname = $field . '_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $fname)) {
                    $uploads[$field] = $fname;
                }
            }
        }
    }

    if (empty($errors)) {
        $resume_path = $uploads['resume'] ?? null;
        $id_path = $uploads['valid_id'] ?? null;
        $diploma_path = $uploads['diploma'] ?? null;
        $photo_path = $uploads['photo'] ?? null;

        $ins = $db->prepare("INSERT INTO applications 
            (user_id, job_id, first_name, last_name, email, contact_number, date_of_birth, gender, address, highest_education, school_name, course_strand, year_graduated, preferred_shift, application_source, resume_path, id_path, diploma_path, photo_path)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $ins->bind_param('iissssssssssissssss',
            $_SESSION['user_id'], $job_id,
            $data['first_name'], $data['last_name'], $data['email'], $data['contact'],
            $data['dob'], $data['gender'], $data['address'],
            $data['education'], $data['school'], $data['course'], $data['year_grad'],
            $data['shift'], $data['source'],
            $resume_path, $id_path, $diploma_path, $photo_path
        );
        $ins->execute();
        $app_id = $db->insert_id;

        // Create notification
        $notifMsg = "Your application for '{$job['title']}' has been received. We will review it and update you on your status.";
        $notif = $db->prepare("INSERT INTO notifications (user_id, application_id, title, message) VALUES (?,?,?,?)");
        $notifTitle = 'Application Received';
        $notif->bind_param('iiss', $_SESSION['user_id'], $app_id, $notifTitle, $notifMsg);
        $notif->execute();

        setFlash('success', 'Application submitted successfully! You can track your status in My Dashboard.');
        redirect(SITE_URL . '/dashboard.php');
    }
}
?>

<div class="container page-wrap" style="max-width:780px;">
    <div style="background:var(--red);border-radius:var(--radius);padding:1.25rem 1.5rem;color:#fff;margin-bottom:1rem;">
        <p style="font-size:12px;opacity:0.75;">Applying for</p>
        <h2 style="font-size:18px;font-weight:600;"><?= htmlspecialchars($job['title']) ?></h2>
        <p style="font-size:13px;opacity:0.85;"><?= htmlspecialchars($job['company']) ?> — <?= htmlspecialchars($job['salary_display']) ?></p>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $e): ?><div>• <?= $e ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-red">📄 Please prepare: Resume, valid ID, diploma copy, and 2x2 photo before submitting your application.</div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?= $job_id ?>">

        <div class="card">
            <div class="form-section-title">Personal Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? $user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? $user['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($_POST['dob'] ?? $user['date_of_birth']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select gender</option>
                        <option value="Male" <?= ($_POST['gender'] ?? $user['gender']) == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($_POST['gender'] ?? $user['gender']) == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= ($_POST['gender'] ?? $user['gender']) == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($_POST['contact'] ?? $user['contact_number']) ?>" placeholder="09XX-XXX-XXXX">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Complete Address <span class="required">*</span></label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($_POST['address'] ?? $user['address']) ?>" placeholder="Barangay, Municipality, Province" required>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Educational Background</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Highest Attainment <span class="required">*</span></label>
                    <select name="education" class="form-control" required>
                        <option value="">Select education</option>
                        <option value="Elementary">Elementary</option>
                        <option value="High School">High School</option>
                        <option value="Senior High School">Senior High School</option>
                        <option value="Vocational">Vocational / Technical</option>
                        <option value="College">College</option>
                        <option value="Post Graduate">Post Graduate</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">School / Institution</label>
                    <input type="text" name="school" class="form-control" value="<?= htmlspecialchars($_POST['school'] ?? '') ?>" placeholder="School name">
                </div>
                <div class="form-group">
                    <label class="form-label">Course / Strand (if applicable)</label>
                    <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($_POST['course'] ?? '') ?>" placeholder="e.g. HUMSS, ABM, BS IT">
                </div>
                <div class="form-group">
                    <label class="form-label">Year Graduated</label>
                    <input type="number" name="year_grad" class="form-control" value="<?= htmlspecialchars($_POST['year_grad'] ?? '') ?>" placeholder="<?= date('Y') ?>" min="1990" max="<?= date('Y') ?>">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Job Preference</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Preferred Shift</label>
                    <select name="shift" class="form-control">
                        <option value="Any Shift">Any Shift</option>
                        <option value="Day Shift">Day Shift</option>
                        <option value="Night Shift">Night Shift</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Application Source</label>
                    <select name="source" class="form-control">
                        <option value="online">Online Application</option>
                        <option value="walk-in">Walk-in</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Document Uploads <span style="font-weight:400;color:var(--gray-400);font-size:12px;">(JPG, PNG, PDF — max 5MB each)</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Resume / Biodata</label>
                    <input type="file" name="resume" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <span class="form-hint">PDF or image of your resume with 2x2 photo</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Valid Government ID</label>
                    <input type="file" name="valid_id" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <span class="form-hint">National ID, UMID, Driver's License, etc.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Diploma / TOR</label>
                    <input type="file" name="diploma" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    <span class="form-hint">HS/SHS diploma or college diploma</span>
                </div>
                <div class="form-group">
                    <label class="form-label">2x2 ID Photo</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
                    <span class="form-hint">White background, recent photo</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <a href="job-detail.php?id=<?= $job_id ?>" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
