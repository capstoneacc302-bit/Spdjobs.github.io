<?php
$pageTitle = 'Edit Profile';
require_once 'includes/header.php';
requireLogin();
$db  = getDB();
$uid = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name  = sanitize($_POST['last_name']  ?? '');
    $contact    = sanitize($_POST['contact']    ?? '');
    $dob        = sanitize($_POST['dob']        ?? '');
    $gender     = sanitize($_POST['gender']     ?? '');
    $address    = sanitize($_POST['address']    ?? '');
    $sss        = sanitize($_POST['sss']        ?? '');
    $philhealth = sanitize($_POST['philhealth'] ?? '');
    $pagibig    = sanitize($_POST['pagibig']    ?? '');
    $tin        = sanitize($_POST['tin']        ?? '');

    if (!$first_name) $errors[] = 'First name is required.';
    if (!$last_name)  $errors[] = 'Last name is required.';

    // Password change
    $new_password = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';
    if ($new_password) {
        if (strlen($new_password) < 6) $errors[] = 'New password must be at least 6 characters.';
        elseif ($new_password !== $confirm_pass) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if ($new_password) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET first_name=?,last_name=?,contact_number=?,date_of_birth=?,gender=?,address=?,sss_number=?,philhealth_number=?,pagibig_number=?,tin_number=?,password=? WHERE id=?");
            $upd->bind_param('sssssssssssi', $first_name,$last_name,$contact,$dob,$gender,$address,$sss,$philhealth,$pagibig,$tin,$hash,$uid);
        } else {
            $upd = $db->prepare("UPDATE users SET first_name=?,last_name=?,contact_number=?,date_of_birth=?,gender=?,address=?,sss_number=?,philhealth_number=?,pagibig_number=?,tin_number=? WHERE id=?");
            $upd->bind_param('ssssssssssi', $first_name,$last_name,$contact,$dob,$gender,$address,$sss,$philhealth,$pagibig,$tin,$uid);
        }
        $upd->execute();
        $_SESSION['user_firstname'] = $first_name;
        $_SESSION['user_lastname']  = $last_name;

        // Reload user data
        $stmt2 = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt2->bind_param('i', $uid);
        $stmt2->execute();
        $user = $stmt2->get_result()->fetch_assoc();

        setFlash('success', 'Profile updated successfully.');
        redirect(SITE_URL . '/profile.php');
    }
}
?>

<div class="container page-wrap" style="max-width:700px;">
    <p class="text-sm text-muted mb-2"><a href="dashboard.php">← Back to Dashboard</a></p>

    <?= showFlash() ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?php foreach ($errors as $e): ?><div>• <?= $e ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
        <div style="width:64px;height:64px;border-radius:50%;background:var(--red);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:600;">
            <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
        </div>
        <div>
            <h2 style="font-size:18px;font-weight:600;"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h2>
            <p class="text-sm text-muted"><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <form method="POST">
        <div class="card">
            <div class="form-section-title">Personal Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="Male" <?= $user['gender']=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $user['gender']=='Female'?'selected':'' ?>>Female</option>
                        <option value="Other" <?= $user['gender']=='Other'?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" placeholder="09XX-XXX-XXXX">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Complete Address</label>
                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Barangay, Municipality, Province">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Government Numbers <span style="font-weight:400;color:var(--gray-400);font-size:12px;">(optional — fill in after hiring)</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">SSS Number</label>
                    <input type="text" name="sss" class="form-control" value="<?= htmlspecialchars($user['sss_number'] ?? '') ?>" placeholder="XX-XXXXXXX-X">
                </div>
                <div class="form-group">
                    <label class="form-label">PhilHealth Number</label>
                    <input type="text" name="philhealth" class="form-control" value="<?= htmlspecialchars($user['philhealth_number'] ?? '') ?>" placeholder="XX-XXXXXXXXX-X">
                </div>
                <div class="form-group">
                    <label class="form-label">Pag-IBIG Number</label>
                    <input type="text" name="pagibig" class="form-control" value="<?= htmlspecialchars($user['pagibig_number'] ?? '') ?>" placeholder="XXXX-XXXX-XXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">TIN Number</label>
                    <input type="text" name="tin" class="form-control" value="<?= htmlspecialchars($user['tin_number'] ?? '') ?>" placeholder="XXX-XXX-XXX">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="form-section-title">Change Password <span style="font-weight:400;color:var(--gray-400);font-size:12px;">(leave blank to keep current)</span></div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_pass" class="form-control" placeholder="Repeat new password">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
