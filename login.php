<?php
$pageTitle = 'Sign In';
require_once 'includes/header.php';
$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $db->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_firstname'] = $user['first_name'];
        $_SESSION['user_lastname']  = $user['last_name'];
        $_SESSION['user_email']     = $user['email'];
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : SITE_URL . '/dashboard.php';
        setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
        redirect($redirect);
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<div class="container-sm page-wrap">
    <div class="card" style="max-width:420px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:52px;height:52px;background:var(--red);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                <span style="color:#fff;font-weight:700;font-size:18px;">SPD</span>
            </div>
            <h2 style="font-size:20px;font-weight:600;">Welcome Back</h2>
            <p class="text-muted text-sm">Sign in to your SPD Jobs account</p>
        </div>

        <?= showFlash() ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group mb-2">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="juan@email.com" required autofocus>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In as Applicant</button>
        </form>

        <div style="text-align:center;margin:1rem 0;font-size:13px;color:var(--gray-400);">— or —</div>
        <a href="admin/login.php" class="btn btn-dark btn-block">Sign In as Admin / HR</a>

        <div style="text-align:center;margin-top:1rem;font-size:14px;color:var(--gray-500);">
            No account yet? <a href="register.php">Sign up for free</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
