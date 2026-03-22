<?php
require_once '../includes/config.php';
$db = getDB();
$error = '';

if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $db->prepare("SELECT * FROM admins WHERE email=? AND is_active=1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        redirect(SITE_URL . '/admin/index.php');
    } else {
        $error = 'Invalid admin credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — SPD Jobs</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--gray-100);">
<div class="card" style="max-width:400px;width:100%;margin:2rem;">
    <div style="text-align:center;margin-bottom:1.5rem;">
        <div style="width:52px;height:52px;background:var(--gray-900);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <span style="color:#fff;font-weight:700;font-size:16px;">HR</span>
        </div>
        <h2 style="font-size:20px;font-weight:600;">Admin / HR Login</h2>
        <p class="text-muted text-sm">SPD Jobs Inc. — Bataan Branch</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group mb-2">
            <label class="form-label">Admin Email</label>
            <input type="email" name="email" class="form-control" placeholder="admin@spdjobs.com" required autofocus>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark btn-block">Sign In as Admin</button>
    </form>
    <div style="text-align:center;margin-top:1rem;font-size:13px;color:var(--gray-400);">
        Default: admin@spdjobs.com / password<br>
        <a href="<?= SITE_URL ?>/login.php">← Back to Applicant Login</a>
    </div>
</div>
</body>
</html>
