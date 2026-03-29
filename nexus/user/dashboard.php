<?php
// ============================================================
// user/dashboard.php — Researcher Dashboard
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('user');
require_once '../config/db.php';

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

// --- Stats for this user ---
$stmt = mysqli_prepare($conn,
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'pending')  AS pending,
        SUM(status = 'approved') AS approved,
        SUM(status = 'rejected') AS rejected
     FROM papers WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// --- Recent 5 papers for activity table ---
$stmt2 = mysqli_prepare($conn,
    "SELECT id, title, category, status, created_at
     FROM papers WHERE user_id = ?
     ORDER BY created_at DESC LIMIT 5"
);
mysqli_stmt_bind_param($stmt2, 'i', $user_id);
mysqli_stmt_execute($stmt2);
$recent_papers = mysqli_fetch_all(mysqli_stmt_get_result($stmt2), MYSQLI_ASSOC);
mysqli_stmt_close($stmt2);

$page_title  = 'Dashboard';
$active_page = 'dashboard';
$root_prefix = '../';
include '../includes/header.php';
?>

<!-- Welcome Header -->
<section class="mb-10">
    <h3 class="font-headline text-5xl font-bold text-primary mb-2 tracking-tight">
        Welcome back, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>.
    </h3>
    <p class="text-secondary font-body max-w-2xl leading-relaxed">
        Your research portfolio is active. Track submissions, manage papers, and stay on top of your review status below.
    </p>
</section>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
    <!-- Total -->
    <div class="stat-card group">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Total Submissions</p>
            <h3 class="text-3xl font-bold text-primary"><?= (int)$stats['total'] ?></h3>
            <p class="text-xs text-slate-400 mt-2">All time</p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-8xl text-primary/5 group-hover:scale-110 transition-transform">library_books</span>
    </div>
    <!-- Pending -->
    <div class="stat-card group">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Pending Review</p>
            <h3 class="text-3xl font-bold text-amber-600"><?= (int)$stats['pending'] ?></h3>
            <p class="text-xs text-slate-400 mt-2">Awaiting editor</p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-8xl text-amber-100 group-hover:scale-110 transition-transform">hourglass_empty</span>
    </div>
    <!-- Approved -->
    <div class="stat-card group border-l-4 border-green-500">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Approved</p>
            <h3 class="text-3xl font-bold text-green-600"><?= (int)$stats['approved'] ?></h3>
            <p class="text-xs text-slate-400 mt-2">Published papers</p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-8xl text-green-100 group-hover:scale-110 transition-transform">verified</span>
    </div>
    <!-- Rejected -->
    <div class="stat-card group border-l-4 border-red-400">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Rejected</p>
            <h3 class="text-3xl font-bold text-red-600"><?= (int)$stats['rejected'] ?></h3>
            <p class="text-xs text-slate-400 mt-2">Not accepted</p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-8xl text-red-100 group-hover:scale-110 transition-transform">cancel</span>
    </div>
</div>

<!-- Activity Table + Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- Recent Papers Table -->
    <div class="lg:col-span-2">
        <div class="flex justify-between items-center mb-5">
            <h4 class="font-headline text-2xl font-bold text-primary">Recent Activity</h4>
            <a href="my_papers.php" class="text-primary text-sm font-semibold hover:underline">View all papers →</a>
        </div>

        <div class="bg-white rounded-xl editorial-shadow overflow-hidden">
            <?php if (empty($recent_papers)): ?>
            <div class="p-12 text-center">
                <span class="material-symbols-outlined text-6xl text-primary/20 mb-4 block">description</span>
                <p class="text-secondary font-medium">No papers submitted yet.</p>
                <a href="submit.php" class="inline-block mt-4 bg-gradient-primary text-white px-6 py-2 rounded-lg text-sm font-bold shadow hover:opacity-90 transition">
                    Submit Your First Paper
                </a>
            </div>
            <?php else: ?>
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Title</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Category</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($recent_papers as $rp): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-headline text-base font-semibold text-primary">
                                <?= htmlspecialchars(substr($rp['title'], 0, 50)) ?><?= strlen($rp['title']) > 50 ? '…' : '' ?>
                            </p>
                        </td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">
                            <?= htmlspecialchars($rp['category'] ?? '—') ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-<?= $rp['status'] ?>"><?= ucfirst($rp['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-400">
                            <?= date('M d, Y', strtotime($rp['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="space-y-6">
        <div class="bg-primary text-white rounded-xl p-8 shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-headline text-2xl font-bold mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="submit.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-4 rounded-lg transition-all">
                        <span class="material-symbols-outlined">upload_file</span>
                        <div>
                            <p class="font-bold text-sm">New Submission</p>
                            <p class="text-[10px] opacity-70">Upload a research paper</p>
                        </div>
                    </a>
                    <a href="my_papers.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-4 rounded-lg transition-all">
                        <span class="material-symbols-outlined">description</span>
                        <div>
                            <p class="font-bold text-sm">My Papers</p>
                            <p class="text-[10px] opacity-70">View &amp; manage submissions</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="absolute top-0 right-0 p-4 opacity-20">
                <span class="material-symbols-outlined text-8xl">bolt</span>
            </div>
        </div>

        <!-- Tips card -->
        <div class="bg-white rounded-xl p-6 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-lg font-bold text-primary mb-4">Submission Tips</h4>
            <ul class="space-y-3 text-sm text-secondary">
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-primary text-base mt-0.5">check_circle</span>
                    Write a clear, concise abstract
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-primary text-base mt-0.5">check_circle</span>
                    PDF files only (max 5 MB)
                </li>
                <li class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-primary text-base mt-0.5">check_circle</span>
                    Add relevant keywords for discoverability
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
