<?php
session_start();
require_once '../includes/config.php';
requireLogin();

$uid     = (int)$_SESSION['user_id'];
$user    = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
$success = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($conn, $_POST['full_name'] ?? '');
    $new_pw    = $_POST['new_password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $current   = $_POST['current_password'] ?? '';

    if (empty($full_name)) {
        $errors[] = 'Name cannot be empty.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param('si', $full_name, $uid);
        $stmt->execute();
        $_SESSION['user_name'] = $full_name;
    }

    if (!empty($new_pw)) {
        if (!password_verify($current, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (!isStrongPassword($new_pw)) {
            $errors[] = 'New password must be at least 8 characters with uppercase, lowercase, and a number.';
        } elseif ($new_pw !== $confirm) {
            $errors[] = 'New passwords do not match.';
        } else {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed' WHERE id = $uid");
            $success = 'Profile and password updated successfully.';
        }
    }

    if (empty($errors) && empty($success)) $success = 'Profile updated successfully.';
    $user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Profile — SecureAuth</title>
    <link rel="stylesheet" href="../assets/css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dash-body">

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">S</div>
        <div><span class="brand-name">SecureAuth</span><span class="brand-tag">Dashboard</span></div>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="profile.php" class="nav-item active">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($user['full_name']) ?></span>
            <span class="user-role"><?= ucfirst($user['role']) ?></span>
        </div>
        <a href="logout.php" class="logout-btn" title="Logout">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</aside>

<div class="dash-main">
    <div class="dash-topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div>
            <h1 class="page-title">My Profile</h1>
            <p class="page-sub">Update your name and password</p>
        </div>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:20px">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <?= $success ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error" style="margin-bottom:20px">
        <ul style="margin:0;padding-left:16px">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width:580px">
        <div class="profile-avatar-row">
            <div class="profile-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
            <div>
                <p class="profile-name"><?= htmlspecialchars($user['full_name']) ?></p>
                <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
            </div>
        </div>

        <form method="POST" action="">
            <h3 class="form-section-title">Personal Information</h3>
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required/>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f9fafb;color:#9ca3af"/>
                </div>
                <p style="font-size:12px;color:#9ca3af;margin-top:4px">Email cannot be changed.</p>
            </div>

            <h3 class="form-section-title" style="margin-top:28px">Change Password <span style="font-weight:400;font-size:13px;color:#9ca3af">(leave blank to keep current)</span></h3>
            <div class="form-group">
                <label>Current Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="current_password" placeholder="Enter current password"/>
                    <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" id="new_password" name="new_password" placeholder="New password"/>
                    <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="confirm_password" placeholder="Repeat new password"/>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="margin-top:8px">Save Changes</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
</script>
<script src="../assets/js/auth.js"></script>
</body>
</html>
