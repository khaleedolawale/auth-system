<?php
// ── SecureAuth Configuration ──
// Update DB credentials to match your hosting (XAMPP or InfinityFree)

define('DB_HOST', 'localhost');       // InfinityFree: sql206.infinityfree.net (check your panel)
define('DB_USER', 'root');            // InfinityFree: your username e.g. if0_xxxxxxx
define('DB_PASS', '');                // InfinityFree: your DB password
define('DB_NAME', 'secureauth');      // InfinityFree: e.g. if0_xxxxxxx_secureauth
define('APP_NAME', 'SecureAuth');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;background:#fff0f0;border:1px solid #fca5a5;margin:40px auto;max-width:480px;border-radius:8px;">
        <h2 style="color:#dc2626">Database Connection Failed</h2>
        <p style="color:#666;margin-top:10px">' . $conn->connect_error . '</p>
        <p style="color:#999;font-size:13px;margin-top:8px">Check credentials in <code>includes/config.php</code></p>
    </div>');
}
$conn->set_charset('utf8mb4');

function clean($conn, $val) {
    return $conn->real_escape_string(htmlspecialchars(trim($val)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard/index.php');
        exit;
    }
}

function isStrongPassword($p) {
    return strlen($p) >= 8
        && preg_match('/[A-Z]/', $p)
        && preg_match('/[a-z]/', $p)
        && preg_match('/[0-9]/', $p);
}

function logAttempt($conn, $userId, $email, $status, $ip) {
    $uid   = $userId ? (int)$userId : 'NULL';
    $email = $conn->real_escape_string($email);
    $ip    = $conn->real_escape_string($ip);
    $conn->query("INSERT INTO login_log (user_id, email, status, ip_address)
                  VALUES ($uid, '$email', '$status', '$ip')");
}
?>
