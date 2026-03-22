<?php
$pageTitle = 'Create Account';
require_once 'includes/header.php';
$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name  = sanitize($_POST['last_name']  ?? '');
    $email      = sanitize($_POST['email']      ?? '');
    $contact    = sanitize($_POST['contact']    ?? '');
    $password   = $_POST['password']   ?? '';
    $confirm    = $_POST['confirm']    ?? '';

    if (!$first_name) $errors[] = 'First name is required.';
    if (!$last_name)  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $chk = $db->prepare("SELECT id FROM users WHERE email=?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'Email already registered. <a href="login.php">Sign in instead</a>.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare("INSERT INTO users (first_name, last_name, email, contact_number, password) VALUES (?,?,?,?,?)");
            $ins->bind_param('sssss', $first_name, $last_name, $email, $contact, $hash);
            $ins->execute();
            $uid = $db->insert_id;
            $_SESSION['user_id']        = $uid;
            $_SESSION['user_firstname'] = $first_name;
            $_SESSION['user_lastname']  = $last_name;
            $_SESSION['user_email']     = $email;
            setFlash('success', 'Welcome to SPD Jobs, ' . $first_name . '! You can now browse and apply for jobs.');
            redirect(SITE_URL . '/dashboard.php');
        }
    }
}
?>

<div class="container-sm page-wrap">
    <div class="card" style="max-width:480px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:52px;height:52px;background:var(--red);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <span style="color:#fff;font-weight:700;font-size:18px;">SPD</span>
            </div>
            <h2 style="font-size:20px;font-weight:600;">Create Account</h2>
            <p class="text-muted text-sm">Sign up to apply for jobs at SPD Jobs Bataan</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid mb-2">
                <div class="form-group">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="Juan" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name <span class="required">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Dela Cruz" required>
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="juan@email.com" required>
            </div>
            <div class="form-group mb-2">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>" placeholder="09XX-XXX-XXXX">
            </div>
            <div class="form-group mb-2">
                <label class="form-label">Password <span class="required">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm" class="form-control" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <div style="text-align:center;margin-top:1rem;font-size:14px;color:var(--gray-500);">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
