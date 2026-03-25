<?php
session_start();
require_once '../includes/config.php';
requireLogin();

// Fetch fresh user data
$uid  = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();

// Stats
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_logins = $conn->query("SELECT COUNT(*) AS c FROM login_log WHERE status='success'")->fetch_assoc()['c'];
$failed_logins = $conn->query("SELECT COUNT(*) AS c FROM login_log WHERE status='failed'")->fetch_assoc()['c'];

// Recent login log
$logs = $conn->query("SELECT l.*, u.full_name FROM login_log l
    LEFT JOIN users u ON l.user_id = u.id
    ORDER BY l.logged_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard — SecureAuth</title>
    <link rel="stylesheet" href="../assets/css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="dash-body">

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">S</div>
        <div>
            <span class="brand-name">SecureAuth</span>
            <span class="brand-tag">Dashboard</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item active">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="profile.php" class="nav-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
        </a>
        <?php if ($user['role'] === 'admin'): ?>
        <a href="users.php" class="nav-item">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            All Users
        </a>
        <?php endif; ?>
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

<!-- MAIN -->
<div class="dash-main">
    <div class="dash-topbar">
        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">Welcome back, <?= htmlspecialchars($user['full_name']) ?>!</p>
        </div>
        <div class="topbar-right">
            <span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="stat-num"><?= $total_users ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--green">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="stat-num"><?= $total_logins ?></div>
                <div class="stat-label">Successful Logins</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--red">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div class="stat-num"><?= $failed_logins ?></div>
                <div class="stat-label">Failed Attempts</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--purple">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <div class="stat-num"><?= ucfirst($user['role']) ?></div>
                <div class="stat-label">Your Access Level</div>
            </div>
        </div>
    </div>

    <!-- SESSION INFO -->
    <div class="card" style="margin-bottom:24px">
        <h2 class="card-title">Current Session</h2>
        <div class="session-grid">
            <div class="session-item">
                <span class="session-label">Logged in as</span>
                <span class="session-value"><?= htmlspecialchars($user['full_name']) ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Email</span>
                <span class="session-value"><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Role</span>
                <span class="session-value"><span class="role-badge role-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></span>
            </div>
            <div class="session-item">
                <span class="session-label">Last Login</span>
                <span class="session-value"><?= $user['last_login'] ? date('d M Y, H:i', strtotime($user['last_login'])) : 'First login' ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Member Since</span>
                <span class="session-value"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
            </div>
            <div class="session-item">
                <span class="session-label">Session ID</span>
                <span class="session-value" style="font-family:monospace;font-size:12px"><?= substr(session_id(), 0, 20) ?>...</span>
            </div>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="card">
        <div class="card-header-row">
            <h2 class="card-title">Recent Login Activity</h2>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($log['email']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $log['status'] ?>">
                                <?= ucfirst($log['status']) ?>
                            </span>
                        </td>
                        <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($log['ip_address']) ?></td>
                        <td><?= date('d M, H:i', strtotime($log['logged_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /dash-main -->

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>
</body>
</html>
