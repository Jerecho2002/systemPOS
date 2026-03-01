<?php
include "database/database.php";
$database->login();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS & Inventory System - Login</title>
  <link href="assets/fonts.css" rel="stylesheet">
  <style>
    :root {
      --bg: #0f1117;
      --surface: #1a1d27;
      --surface2: #22263a;
      --border: #2e3347;
      --accent: #f5a623;
      --text: #e8eaf0;
      --text-muted: #7b82a0;
      --danger: #ff5c5c;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      background: var(--bg);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    .login-wrap {
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
    }

    /* Header */
    .login-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .logo-mark {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 56px;
      height: 56px;
      background: var(--accent);
      border-radius: 16px;
      margin-bottom: 20px;
      box-shadow: 0 8px 24px rgba(245, 166, 35, 0.25);
    }

    .login-header h1 {
      font-family: 'Syne', sans-serif;
      font-size: 22px;
      font-weight: 800;
      color: var(--text);
      letter-spacing: -0.5px;
      margin-bottom: 6px;
    }

    .login-header p {
      font-size: 13px;
      color: var(--text-muted);
    }

    /* Card */
    .login-card {
      background: var(--surface);
      border: 1.5px solid var(--border);
      border-radius: 20px;
      padding: 32px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .card-label {
      font-family: 'Syne', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 4px;
    }

    .card-sublabel {
      font-size: 13px;
      color: var(--text-muted);
      margin-bottom: 24px;
    }

    /* Error */
    .error-box {
      background: rgba(255, 92, 92, .1);
      border: 1px solid rgba(255, 92, 92, .3);
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 13px;
      color: var(--danger);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Form */
    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 7px;
    }

    .form-input {
      width: 100%;
      background: var(--bg);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 11px 14px;
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .form-input::placeholder {
      color: var(--text-muted);
    }

    .form-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(245, 166, 35, .1);
    }

    /* Password wrapper */
    .password-wrap {
      position: relative;
    }

    .password-wrap .form-input {
      padding-right: 42px;
    }

    .toggle-pw {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;
      transition: color .2s;
    }

    .toggle-pw:hover {
      color: var(--text);
    }

    /* Submit */
    .btn-login {
      width: 100%;
      background: var(--accent);
      color: #111;
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-family: 'Syne', sans-serif;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 8px;
      transition: opacity .2s, transform .1s, box-shadow .2s;
      box-shadow: 0 4px 16px rgba(245, 166, 35, 0.2);
    }

    .btn-login:hover {
      opacity: .9;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(245, 166, 35, 0.3);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    /* Divider */
    .divider {
      height: 1px;
      background: var(--border);
      margin: 24px 0;
    }

    /* Footer */
    .login-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 12px;
      color: var(--text-muted);
    }

    .login-footer strong {
      color: var(--accent);
    }
  </style>
</head>

<body>

  <div class="login-wrap">

    <!-- Header -->
    <div class="login-header">
      <div class="logo-mark">
        <svg width="26" height="26" fill="none" stroke="#111" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9h14m-9-9v9m4-9v9" />
        </svg>
      </div>
      <h1>POS & Inventory System</h1>
      <p>Hanging Parrot Digital Solutions</p>
    </div>

    <!-- Card -->
    <div class="login-card">

      <div class="card-label">Welcome back</div>
      <div class="card-sublabel">Sign in to access your dashboard</div>

      <?php if (isset($_SESSION['login-error'])): ?>
        <div class="error-box">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
          </svg>
          <?= htmlspecialchars($_SESSION['login-error']) ?>
        </div>
        <?php unset($_SESSION['login-error']); ?>
      <?php endif; ?>

      <form method="POST">

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-input"
            placeholder="Enter your username"
            autocomplete="username"
            required>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="password-wrap">
            <input
              type="password"
              id="password"
              name="password"
              class="form-input"
              placeholder="Enter your password"
              autocomplete="current-password"
              required>
            <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
              <svg id="eyeIcon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" name="login" class="btn-login">
          Sign In
        </button>

      </form>

    </div>

  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById('password');
      const icon = document.getElementById('eyeIcon');

      if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            `;
      } else {
        input.type = 'password';
        icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            `;
      }
    }

    // Auto dismiss error after 4 seconds
    const errorBox = document.querySelector('.error-box');
    if (errorBox) {
      setTimeout(() => {
        errorBox.style.opacity = '0';
        errorBox.style.transition = 'opacity .5s';
      }, 3500);
      setTimeout(() => errorBox.remove(), 4000);
    }
  </script>

</body>

</html>