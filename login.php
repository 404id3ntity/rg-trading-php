<?php
require_once __DIR__ . '/includes/config.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: /rg-trading-php/index.php');
    exit;
}

$error = '';
$step = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Email and password are required.';
        } else {
            $result = api_request('POST', '/auth/login', [
                'email'    => $email,
                'password' => $password,
            ]);

            if ($result['status'] === 200 && !empty($result['body']['data']['requires_otp'])) {
                $_SESSION['otp_email'] = $email;
                $step = 'otp';
                set_flash('success', $result['body']['message'] ?? 'OTP sent to your email.');
            } else {
                $error = $result['body']['message'] ?? 'Invalid email or password.';
            }
        }
    } elseif ($action === 'verify_otp') {
        $otp   = trim($_POST['otp'] ?? '');
        $email = $_SESSION['otp_email'] ?? '';

        if (empty($otp) || empty($email)) {
            $error = 'OTP is required. Please login again if session expired.';
            $step = empty($email) ? 'login' : 'otp';
        } else {
            $result = api_request('POST', '/auth/verify-otp', [
                'email' => $email,
                'otp'   => $otp,
            ]);

            if ($result['status'] === 200 && isset($result['body']['data']['access_token'])) {
                $_SESSION['access_token']  = $result['body']['data']['access_token'];
                $_SESSION['refresh_token'] = $result['body']['data']['refresh_token'];
                $_SESSION['user']          = $result['body']['data']['user'];
                unset($_SESSION['otp_email']);

                set_flash('success', 'Welcome back, ' . $_SESSION['user']['first_name'] . '!');

                // Redirect admins and riders to their dashboards
                if (is_admin()) {
                    header('Location: /rg-trading-php/pages/admin/dashboard.php');
                } elseif (is_rider()) {
                    header('Location: /rg-trading-php/pages/rider/orders.php');
                } else {
                    header('Location: /rg-trading-php/index.php');
                }
                exit;
            } else {
                $error = $result['body']['message'] ?? 'Invalid or expired OTP.';
                $step = 'otp';
            }
        }
    }
} else {
    unset($_SESSION['otp_email']);
}

$page_title = 'Login — ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($page_title) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2><?= $step === 'otp' ? 'Verify OTP' : 'Welcome back' ?></h2>
    <p><?= $step === 'otp' ? 'Enter the 6-digit code sent to your email' : 'Sign in to your R&amp;G Trading account' ?></p>

    <?php if ($error): ?>
      <div class="flash flash-error flash-inline">
        <?= h($error) ?>
      </div>
    <?php endif; ?>
    <?php $flash = get_flash(); if ($flash && $flash['type'] === 'success'): ?>
      <div class="flash flash-success flash-inline">
        <?= h($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php if ($step === 'otp'): ?>
      <form method="POST">
        <input type="hidden" name="action" value="verify_otp">
        <div class="form-group">
          <label for="otp">One-Time Password</label>
          <input type="text" id="otp" name="otp" placeholder="123456" maxlength="6" required autofocus>
        </div>
        <button type="submit" class="btn-primary">Verify & Sign In</button>
      </form>
    <?php else: ?>
      <form method="POST">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com"
                 value="<?= h($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-primary">Sign In</button>
      </form>
    <?php endif; ?>

    <div class="auth-footer">
      <?php if ($step === 'otp'): ?>
        <a href="<?= BASE_URL ?>/login.php">Back to Login</a>
      <?php else: ?>
        Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
