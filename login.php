<?php
session_start();
require_once 'includes/config.php';
redirectIfLoggedIn();

$error = '';
$email_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'];
    $email_val = $email;

    if (empty($email) || empty($password)) {
        $error = 'Please fill in both fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Success
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email']= $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Update last login
                $id = $user['id'];
                $conn->query("UPDATE users SET last_login = NOW() WHERE id = $id");
                logAttempt($conn, $user['id'], $email, 'success', $ip);

                header('Location: dashboard/index.php');
                exit;
            } else {
                logAttempt($conn, $user['id'], $email, 'failed', $ip);
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            logAttempt($conn, null, $email, 'failed', $ip);
            $error = 'No account found with that email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login — SecureAuth</title>
    <link rel="stylesheet" href="assets/css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-container">

    <!-- Left panel -->
    <div class="auth-panel auth-panel--left">
        <div class="brand">
            <div class="brand-icon">S</div>
            <div>
                <span class="brand-name">SecureAuth</span>
                <span class="brand-tag">Login & Auth System</span>
            </div>
        </div>
        <div class="panel-content">
            <h2>Secure Access,<br>Simple Design.</h2>
            <p>A complete PHP & MySQL authentication system with registration, login, session management, and role-based access control.</p>
            <ul class="feature-list">
                <li><span class="feat-dot"></span>Password hashing with bcrypt</li>
                <li><span class="feat-dot"></span>Session-based authentication</li>
                <li><span class="feat-dot"></span>Role-based access control</li>
                <li><span class="feat-dot"></span>Login activity logging</li>
                <li><span class="feat-dot"></span>Input validation & sanitization</li>
            </ul>
        </div>
        <p class="panel-credit">Built by <strong>Khaleed Olawale</strong></p>
    </div>

    <!-- Right panel — form -->
    <div class="auth-panel auth-panel--right">
        <div class="auth-card">
            <div class="auth-card-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account to continue</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Account created! You can now log in.
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-info">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                You have been logged out successfully.
            </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><polyline points="4,4 12,12 20,4"/></svg>
                        <input type="email" id="email" name="email" placeholder="your@email.com"
                            value="<?= htmlspecialchars($email_val) ?>" required autocomplete="email" autofocus/>
                    </div>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                        <a href="forgot.php" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password"/>
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)" aria-label="Show password">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-check">
                    <label class="check-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Keep me signed in
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span>Sign In</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <p class="auth-switch">
                Don't have an account? <a href="register.php">Create one</a>
            </p>

            <div class="demo-hint">
                <p><strong>Demo credentials:</strong></p>
                <p>Admin: <code>admin@secureauth.com</code> / <code>Admin@123</code></p>
                <p>User: <code>john@example.com</code> / <code>User@123</code></p>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>
