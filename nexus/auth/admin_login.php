<?php
// ============================================================
// auth/admin_login.php — Admin Portal Login Page
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
        // Fetch user by email
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $error = 'Access denied. Researchers must use the standard login portal.';
            } else {
                // ✅ Admin password correct — start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['email']   = $user['email'];
                $_SESSION['role']    = $user['role'];

                header('Location: ../admin/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid admin credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Portal Login | Academic Archive</title>
    <meta name="description" content="Admin Login to Academic Archive"/>
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
<body class="bg-slate-900 font-body min-h-screen flex items-center justify-center px-4">

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-10">
        <a href="../index.php" class="inline-block">
            <h1 class="font-headline text-3xl font-bold text-white italic">Academic Archive</h1>
            <p class="text-sm text-slate-400 mt-1 tracking-widest uppercase font-semibold">Admin Portal</p>
        </a>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-10 border-t-4 border-primary">
        <h2 class="font-headline text-2xl font-bold text-primary mb-2">Editor Access</h2>
        <p class="text-secondary text-sm mb-8">Sign in with your administrator credentials.</p>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert alert-error mb-6">
            <span class="material-symbols-outlined text-base">error</span>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="admin_login.php" class="space-y-5">

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="email">
                    Admin Email
                </label>
                <input
                    id="email" name="email" type="email" required
                    class="form-input focus:ring-primary/40 focus:border-primary"
                    placeholder="admin@nexus.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                />
            </div>

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="password">
                    Admin Password
                </label>
                <input
                    id="password" name="password" type="password" required
                    class="form-input focus:ring-primary/40 focus:border-primary"
                    placeholder="••••••••"
                />
            </div>

            <button type="submit"
                class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-bold text-sm shadow-lg hover:bg-slate-800 transition-colors mt-2 flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">shield</span>
                Secure Sign In
            </button>
        </form>

        <p class="text-center text-sm text-secondary mt-6">
            <a href="login.php" class="text-primary font-semibold hover:underline">← Back to Researcher Login</a>
        </p>
    </div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
