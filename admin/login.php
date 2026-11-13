<?php
// File: laundryq/admin/login.php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db_connect.php';

$message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(
        trim($_POST['email'] ?? '')
    );

    $password = (string) (
        $_POST['password'] ?? ''
    );

    if (
        $email === '' ||
        $password === ''
    ) {
        $message = "
            <div class='alert alert-danger'>
                Please enter your email and password.
            </div>
        ";
    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $message = "
            <div class='alert alert-danger'>
                Please enter a valid email address.
            </div>
        ";
    } else {
        try {
            $admin = $db->users->findOne([
                'email' => $email,
                'role' => 'admin'
            ]);

            if (!$admin) {
                $message = "
                    <div class='alert alert-danger'>
                        No admin account found with that email.
                    </div>
                ";
            } else {
                $storedPassword = (string) (
                    $admin['password'] ??
                    $admin['password_hash'] ??
                    ''
                );

                if (
                    $storedPassword === '' ||
                    !password_verify(
                        $password,
                        $storedPassword
                    )
                ) {
                    $message = "
                        <div class='alert alert-danger'>
                            Incorrect email or password.
                        </div>
                    ";
                } else {
                    session_regenerate_id(true);

                    $_SESSION['admin_id'] =
                        (string) $admin['_id'];

                    $_SESSION['admin_name'] =
                        (string) (
                            $admin['full_name'] ?? ''
                        );

                    $_SESSION['admin_role'] =
                        (string) (
                            $admin['role'] ?? 'admin'
                        );

                    $_SESSION['admin_login_time'] =
                        time();

                    header(
                        'Location: dashboard.php'
                    );

                    exit;
                }
            }
        } catch (Throwable $e) {
            error_log(
                'Admin login error: ' .
                $e->getMessage()
            );

            $message = "
                <div class='alert alert-danger'>
                    A database error occurred.
                    Please try again later.
                </div>
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Admin Login - LaundryQ</title>

    <link
        href="../assets/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <link
        href="../assets/css/admin-ui.css"
        rel="stylesheet"
    >
</head>

<body class="admin-login-page">
    <main class="admin-login-shell">
        <header class="admin-login-topbar">
            <a class="admin-login-brand" href="../index.php">
                <span class="admin-login-mark" aria-hidden="true">L</span>
                LaundryQ <span class="admin-login-brand-label">ADMIN</span>
            </a>

            <nav class="admin-login-navigation" aria-label="Page navigation">
                <button
                    type="button"
                    class="admin-login-back"
                    onclick="history.back();"
                >
                    ← Back
                </button>
                <a href="../index.php" class="admin-login-home">
                    Home <span aria-hidden="true">↗</span>
                </a>
            </nav>
        </header>

        <section class="admin-login-card" aria-labelledby="admin-login-title">
            <div class="admin-login-aside">
                <span class="admin-login-eyebrow">STAFF PORTAL</span>
                <h1>Welcome back.</h1>
                <p>
                    Keep every order moving and every customer up to date.
                    Sign in to manage your LaundryQ operations.
                </p>
                <div class="admin-login-note">
                    <span aria-hidden="true"></span>
                    Secure administrator access
                </div>
            </div>

            <div class="admin-login-form">
                <div class="admin-login-heading">
                    <h2 id="admin-login-title">Admin sign in</h2>
                    <p>Enter your administrator account details to continue.</p>
                </div>

                <?php if ($message !== ''): ?>
                    <?= $message ?>
                <?php endif; ?>

                <form
                    method="POST"
                    action="login.php"
                >
                    <div class="mb-3">
                        <label
                            for="email"
                            class="form-label"
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            value="<?= htmlspecialchars(
                                $email,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label
                            for="password"
                            class="form-label"
                        >
                            Password
                        </label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword(this)"
                                aria-label="Show password"
                            >
                                Show
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn admin-login-submit w-100"
                    >
                        Sign in to dashboard <span aria-hidden="true">→</span>
                    </button>
                </form>
            </div>
        </section>

        <p class="admin-login-footer">
            LaundryQ <span aria-hidden="true">·</span> Admin workspace
        </p>
    </main>

    <script>
        function togglePassword(button) {
            const passwordInput =
                document.getElementById('password');

            const isPassword =
                passwordInput.type === 'password';

            passwordInput.type =
                isPassword ? 'text' : 'password';

            button.textContent =
                isPassword ? 'Hide' : 'Show';

            button.setAttribute(
                'aria-label',
                isPassword ? 'Hide password' : 'Show password'
            );
        }
    </script>
</body>
</html>