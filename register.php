<?php
session_start();
require_once 'includes/config.php';
redirectIfLoggedIn();

$errors  = [];
$success = '';
$vals    = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($conn, $_POST['full_name'] ?? '');
    $email     = clean($conn, $_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $vals      = ['full_name' => $full_name, 'email' => $email];

    // Validate
    if (empty($full_name)) $errors[] = 'Full name is required.';
    elseif (strlen($full_name) < 3) $errors[] = 'Name must be at least 3 characters.';

    if (empty($email)) $errors[] = 'Email address is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (empty($password)) $errors[] = 'Password is required.';
    elseif (!isStrongPassword($password)) $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';

    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Check email exists
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $full_name, $email, $hashed);
            if ($ins->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register — SecureAuth</title>
    <link rel="stylesheet" href="assets/css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-container">

    <div class="auth-panel auth-panel--left">
        <div class="brand">
            <div class="brand-icon">S</div>
            <div>
                <span class="brand-name">SecureAuth</span>
                <span class="brand-tag">Login & Auth System</span>
            </div>
        </div>
        <div class="panel-content">
            <h2>Join SecureAuth<br>in Seconds.</h2>
            <p>Create your account with a secure password. Your data is protected with industry-standard bcrypt hashing.</p>
            <ul class="feature-list">
                <li><span class="feat-dot"></span>Instant account creation</li>
                <li><span class="feat-dot"></span>Password strength enforcement</li>
                <li><span class="feat-dot"></span>Secure bcrypt hashing</li>
                <li><span class="feat-dot"></span>Duplicate email detection</li>
                <li><span class="feat-dot"></span>Input sanitization built in</li>
            </ul>
        </div>
        <p class="panel-credit">Built by <strong>Khaleed Olawale</strong></p>
    </div>

    <div class="auth-panel auth-panel--right">
        <div class="auth-card">
            <div class="auth-card-header">
                <h1>Create Account</h1>
                <p>Fill in your details to get started</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <ul style="margin:0;padding-left:16px">
                    <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate id="registerForm">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <input type="text" id="full_name" name="full_name" placeholder="Your full name"
                            value="<?= htmlspecialchars($vals['full_name']) ?>" required autofocus/>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><polyline points="4,4 12,12 20,4"/></svg>
                        <input type="email" id="email" name="email" placeholder="your@email.com"
                            value="<?= htmlspecialchars($vals['email']) ?>" required/>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="password" name="password" placeholder="Min 8 chars, upper, lower, number" required/>
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)" aria-label="Show password">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <div class="pw-strength" id="pwStrength">
                        <div class="pw-bar" id="pwBar"></div>
                    </div>
                    <p class="pw-hint" id="pwHint"></p>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required/>
                        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)" aria-label="Show password">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <p class="match-hint" id="matchHint"></p>
                </div>

                <div class="form-check">
                    <label class="check-label">
                        <input type="checkbox" name="agree" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#">Terms of Service</a>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Create Account</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <p class="auth-switch">
                Already have an account? <a href="login.php">Sign in</a>
            </p>
        </div>
    </div>

</div>

<script src="assets/js/auth.js"></script>
</body>
</html>
