<?php
// ============================================================
// auth/register.php — Registration Page
// ============================================================
session_start();

// Already logged in? Redirect
if (!empty($_SESSION['user_id'])) {
    header('Location: ../user/dashboard.php');
    exit;
}

require_once '../config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password = trim($_POST['password']         ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    // --- Validation ---
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = 'An account with this email already exists.';
        } else {
            // Hash password and insert
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt2  = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            mysqli_stmt_bind_param($stmt2, 'sss', $name, $email, $hashed);

            if (mysqli_stmt_execute($stmt2)) {
                $success = 'Account created! You can now log in.';
                // Clear POST fields on success
                $_POST = [];
            } else {
                $error = 'Registration failed. Please try again.';
            }
            mysqli_stmt_close($stmt2);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register | Academic Archive</title>
    <meta name="description" content="Create your Academic Archive researcher account"/>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@400,0&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    "primary":"#00236f","primary-container":"#1e3a8a",
                    "secondary":"#505f76","surface":"#f7f9fb",
                    "surface-container-low":"#f2f4f6","error":"#ba1a1a"
                },
                fontFamily: { "headline":["Newsreader","serif"], "body":["Inter","sans-serif"] }
            }}
        }
    </script>
    <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body class="bg-surface font-body min-h-screen flex items-center justify-center px-4 py-8">

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
        <h2 class="font-headline text-2xl font-bold text-primary mb-2">Create Account</h2>
        <p class="text-secondary text-sm mb-8">Join the Academic Archive research community.</p>

        <!-- Alerts -->
        <?php if ($error): ?>
        <div class="alert alert-error">
            <span class="material-symbols-outlined text-base">error</span>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">
            <span class="material-symbols-outlined text-base">check_circle</span>
            <?= htmlspecialchars($success) ?>
            <a href="login.php" class="ml-2 font-bold underline">Login now →</a>
        </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="POST" action="register.php" class="space-y-5" id="register-form">

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="name">
                    Full Name
                </label>
                <input id="name" name="name" type="text" required
                    class="form-input" placeholder="Dr. Your Name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="email">
                    Email Address
                </label>
                <input id="email" name="email" type="email" required
                    class="form-input" placeholder="you@university.edu"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="password">
                    Password <span class="font-normal normal-case">(min 6 characters)</span>
                </label>
                <input id="password" name="password" type="password" required
                    class="form-input" placeholder="••••••••"
                    minlength="6"/>
            </div>

            <div>
                <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="confirm_password">
                    Confirm Password
                </label>
                <input id="confirm_password" name="confirm_password" type="password" required
                    class="form-input" placeholder="••••••••"
                    minlength="6"/>
            </div>

            <button type="submit"
                class="w-full bg-gradient-primary text-white py-3.5 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition-opacity mt-2">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm text-secondary mt-6">
            Already have an account?
            <a href="login.php" class="text-primary font-semibold hover:underline">Sign in</a>
        </p>
    </div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
