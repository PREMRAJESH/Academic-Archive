<?php
// ============================================================
// auth/login.php — Login Page
// ============================================================
session_start();

// Already logged in? Redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    $dest = ($_SESSION['role'] === 'admin') ? '../admin/dashboard.php' : '../user/dashboard.php';
    header('Location: ' . $dest);
    exit;
}

require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Fetch user by email (prepared statement)
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Password correct — start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Redirect by role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../user/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login | Academic Archive</title>
    <meta name="description" content="Login to Academic Archive — Research Publication Management System"/>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@400,0&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    "primary": "#00236f", "primary-container": "#1e3a8a",
                    "secondary": "#505f76", "surface": "#f7f9fb",
                    "surface-container-low": "#f2f4f6",
                },
                fontFamily: { "headline": ["Newsreader","serif"], "body": ["Inter","sans-serif"] }
            }}
        }
    </script>
    <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body class="bg-surface font-body min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-10">
        <a href="../index.php" class="inline-block">
            <h1 class="font-headline text-3xl font-bold text-primary italic">Academic Archive</h1>
            <p class="text-sm text-secondary mt-1 tracking-widest uppercase">Research Publication System</p>
        </a>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-lg p-10">
        <h2 class="font-headline text-2xl font-bold text-primary mb-2">Welcome back</h2>
        <p class="text-secondary text-sm mb-8">Sign in to your researcher account.</p>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert alert-error mb-6" id="login-error">
            <span class="material-symbols-outlined text-base">error</span>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="login.php" class="space-y-5" id="login-form">

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="email">
                    Email Address
                </label>
                <input
                    id="email" name="email" type="email" required
                    class="form-input"
                    placeholder="you@university.edu"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                />
            </div>

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="password">
                    Password
                </label>
                <input
                    id="password" name="password" type="password" required
                    class="form-input"
                    placeholder="••••••••"
                />
            </div>

            <button type="submit"
                class="w-full bg-gradient-primary text-white py-3.5 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition-opacity mt-2">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-secondary mt-6">
            Don't have an account?
            <a href="register.php" class="text-primary font-semibold hover:underline">Register here</a>
        </p>
    </div>

    <!-- Admin Portal Link -->
    <p class="text-center text-xs text-slate-400 mt-6 pt-4 border-t border-slate-100">
        <a href="admin_login.php" class="hover:text-primary transition-colors inline-flex items-center gap-1 font-medium">
            <span class="material-symbols-outlined text-[14px]">shield</span> Admin Portal
        </a>
    </p>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
