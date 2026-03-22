<?php
// SPD Jobs Inc. - Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change to your DB username
define('DB_PASS', '');             // Change to your DB password
define('DB_NAME', 'spd_jobs_db');

define('SITE_NAME', 'SPD Jobs Inc.');
define('SITE_BRANCH', 'Bataan Branch');
define('SITE_URL', 'http://localhost/spd_jobs');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Company Info
define('COMPANY_ADDRESS', 'Manalo Village, Palihan, Hermosa, Bataan, Philippines');
define('COMPANY_GLOBE', '0917-621-1262');
define('COMPANY_SMART', '0998-570-8638');
define('COMPANY_SUN', '0925-338-8905');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('<div style="font-family:sans-serif;padding:2rem;color:#991b1b;background:#fef2f2;border-radius:8px;margin:2rem;">
                <strong>Database Connection Error:</strong> ' . $conn->connect_error . '<br><br>
                Please check your database settings in <code>includes/config.php</code> and make sure MySQL is running.
            </div>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
function redirect($url) {
    header("Location: $url");
    exit;
}
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
function showFlash() {
    $f = getFlash();
    if (!$f) return '';
    $color = $f['type'] === 'success' ? '#065F46' : '#991B1B';
    $bg    = $f['type'] === 'success' ? '#ECFDF5' : '#FEF2F2';
    $border= $f['type'] === 'success' ? '#10B981' : '#EF4444';
    return "<div style='background:{$bg};color:{$color};border-left:3px solid {$border};padding:10px 14px;border-radius:0 8px 8px 0;margin-bottom:1rem;font-size:14px;'>" . htmlspecialchars($f['msg']) . "</div>";
}
function statusLabel($status) {
    $map = [
        'pending'               => ['label' => 'Pending Review',    'class' => 'status-pending'],
        'for_exam'              => ['label' => 'For Exam',          'class' => 'status-exam'],
        'for_initial_interview' => ['label' => 'For Initial Interview','class'=>'status-interview'],
        'for_medical'           => ['label' => 'For Medical',       'class' => 'status-medical'],
        'for_final_interview'   => ['label' => 'For Final Interview','class'=>'status-final'],
        'for_orientation'       => ['label' => 'For Orientation',   'class' => 'status-orientation'],
        'approved'              => ['label' => 'Approved / Hired',  'class' => 'status-approved'],
        'declined'              => ['label' => 'Declined',          'class' => 'status-declined'],
    ];
    return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'status-pending'];
}
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hr ago';
    return date('M d, Y', $time);
}
