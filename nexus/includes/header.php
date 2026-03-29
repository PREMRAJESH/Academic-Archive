<?php
// ============================================================
// includes/header.php — Shared Page Shell (Head + Sidebar)
// Usage: include at top of every protected page
//
// Expects these vars to be set before including:
//   $page_title  (string) — used in <title> and top bar
//   $active_page (string) — e.g. 'dashboard', 'submit', 'papers', 'users'
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
$user_name = $_SESSION['name']  ?? 'User';
$user_role = $_SESSION['role']  ?? 'user';

// Determine root path dynamically
$depth    = substr_count($_SERVER['SCRIPT_NAME'], '/') - 2; // relative to /nexus/
$root_prefix = str_repeat('../', $depth);

// Decide sidebar links based on role
$is_admin = ($user_role === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($page_title ?? 'Academic Archive') ?> | Academic Archive</title>
    <meta name="description" content="Academic Archive — Research Paper and Book Publication Management System"/>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;0,6..72,700;1,6..72,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary":                  "#00236f",
                        "primary-container":        "#1e3a8a",
                        "on-primary":               "#ffffff",
                        "secondary":                "#505f76",
                        "surface":                  "#f7f9fb",
                        "surface-container":        "#eceef0",
                        "surface-container-low":    "#f2f4f6",
                        "surface-container-high":   "#e6e8ea",
                        "surface-container-lowest": "#ffffff",
                        "on-surface":               "#191c1e",
                        "on-surface-variant":       "#444651",
                        "secondary-container":      "#d0e1fb",
                        "on-secondary-container":   "#54647a",
                        "error":                    "#ba1a1a",
                        "error-container":          "#ffdad6",
                        "on-error-container":       "#93000a",
                        "outline":                  "#757682",
                        "outline-variant":          "#c5c5d3",
                    },
                    fontFamily: {
                        "headline": ["Newsreader", "serif"],
                        "body":     ["Inter", "sans-serif"],
                    },
                }
            }
        }
    </script>
    <!-- Shared CSS -->
    <link rel="stylesheet" href="<?= $root_prefix ?>assets/css/app.css"/>
</head>
<body class="bg-surface font-body text-on-surface flex">

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<aside class="h-screen w-64 fixed left-0 top-0 bg-slate-50 flex flex-col border-r border-slate-200 z-40">
    <!-- Brand -->
    <div class="px-6 py-8">
        <a href="<?= $root_prefix ?>index.php">
            <h1 class="font-headline text-xl font-bold text-primary italic">Academic Archive</h1>
        </a>
        <p class="text-xs text-secondary mt-1 tracking-widest uppercase">
            <?= $is_admin ? 'Admin Portal' : 'Researcher Portal' ?>
        </p>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-1 overflow-y-auto no-scrollbar">

        <?php if ($is_admin): ?>
        <!-- ADMIN LINKS -->
        <a href="<?= $root_prefix ?>admin/dashboard.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='dashboard')?'active':'' ?>">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        <a href="<?= $root_prefix ?>admin/papers.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='papers')?'active':'' ?>">
            <span class="material-symbols-outlined">library_books</span>
            <span class="text-sm font-medium">Manage Papers</span>
        </a>
        <a href="<?= $root_prefix ?>admin/users.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='users')?'active':'' ?>">
            <span class="material-symbols-outlined">group</span>
            <span class="text-sm font-medium">Manage Users</span>
        </a>

        <?php else: ?>
        <!-- USER LINKS -->
        <a href="<?= $root_prefix ?>user/dashboard.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='dashboard')?'active':'' ?>">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        <a href="<?= $root_prefix ?>user/submit.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='submit')?'active':'' ?>">
            <span class="material-symbols-outlined">upload_file</span>
            <span class="text-sm font-medium">Submit Paper</span>
        </a>
        <a href="<?= $root_prefix ?>user/my_papers.php"
           class="sidebar-link flex items-center gap-3 px-4 py-3 mx-2 rounded-lg <?= ($active_page==='my_papers')?'active':'' ?>">
            <span class="material-symbols-outlined">description</span>
            <span class="text-sm font-medium">My Papers</span>
        </a>
        <?php endif; ?>
    </nav>

    <!-- Bottom: Logout -->
    <div class="p-4 border-t border-slate-200 mt-auto">
        <div class="flex items-center gap-3 px-2 mb-3">
            <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm shrink-0">
                <?= strtoupper(substr($user_name, 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-bold text-on-surface truncate"><?= htmlspecialchars($user_name) ?></p>
                <p class="text-[10px] text-secondary uppercase tracking-wider"><?= ucfirst($user_role) ?></p>
            </div>
        </div>
        <a href="<?= $root_prefix ?>auth/logout.php"
           class="flex items-center gap-2 px-4 py-2 text-slate-500 hover:text-error transition-colors text-sm rounded-lg hover:bg-red-50 w-full">
            <span class="material-symbols-outlined text-sm">logout</span>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</aside>

<!-- ============================================================
     MAIN WRAPPER
     ============================================================ -->
<main class="ml-64 flex-1 min-h-screen flex flex-col">

    <!-- Top App Bar -->
    <header class="fixed top-0 left-64 right-0 h-16 bg-white/90 backdrop-blur-md border-b border-slate-100 z-30 flex items-center justify-between px-8">
        <h2 class="font-headline text-lg font-semibold text-primary">
            <?= htmlspecialchars($page_title ?? '') ?>
        </h2>
        <div class="flex items-center gap-3">
            <span class="text-xs text-secondary font-medium hidden md:block">
                <?= htmlspecialchars($user_name) ?>
            </span>
            <a href="<?= $root_prefix ?>auth/logout.php"
               class="flex items-center gap-1 text-xs text-slate-500 hover:text-error transition-colors font-medium">
                <span class="material-symbols-outlined text-base">logout</span>
                Logout
            </a>
        </div>
    </header>

    <!-- Page Content starts here — pages close </main></body></html> -->
    <div class="pt-24 px-8 pb-12 flex-1">
