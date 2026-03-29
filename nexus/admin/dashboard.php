<?php
// ============================================================
// admin/dashboard.php — Admin System Overview
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('admin');
require_once '../config/db.php';

// === System Stats ===
$total_users   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE role='user'"))['c'];
$total_papers  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM papers"))['c'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM papers WHERE status='pending'"))['c'];
$approved_count= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM papers WHERE status='approved'"))['c'];
$rejected_count= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM papers WHERE status='rejected'"))['c'];

// === Recent 8 Submissions ===
$recent_stmt = mysqli_prepare($conn,
    "SELECT p.id, p.title, p.status, p.created_at, u.name AS author_name
     FROM papers p JOIN users u ON p.user_id = u.id
     ORDER BY p.created_at DESC LIMIT 8"
);
mysqli_stmt_execute($recent_stmt);
$recent_papers = mysqli_fetch_all(mysqli_stmt_get_result($recent_stmt), MYSQLI_ASSOC);
mysqli_stmt_close($recent_stmt);

$page_title  = 'Admin Dashboard';
$active_page = 'dashboard';
$root_prefix = '../';
include '../includes/header.php';
?>

<!-- System Overview Header -->
<section class="mb-10">
    <h3 class="font-headline text-4xl font-bold text-primary tracking-tight">System Overview</h3>
    <p class="text-secondary text-xs font-bold uppercase tracking-widest mt-1">Real-time Performance Metrics</p>
</section>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

    <div class="stat-card group">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Total Researchers</p>
            <h3 class="text-3xl font-bold text-primary"><?= number_format($total_users) ?></h3>
            <p class="text-xs text-green-600 font-semibold mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">person</span> Registered users
            </p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-9xl text-primary/5 group-hover:scale-110 transition-transform duration-500">group</span>
    </div>

    <div class="stat-card group">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Total Papers</p>
            <h3 class="text-3xl font-bold text-primary"><?= number_format($total_papers) ?></h3>
            <p class="text-xs text-blue-600 font-semibold mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">description</span> All submissions
            </p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-9xl text-primary/5 group-hover:scale-110 transition-transform duration-500">library_books</span>
    </div>

    <div class="stat-card group border-l-4 border-amber-400">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Pending Review</p>
            <h3 class="text-3xl font-bold text-amber-600"><?= number_format($pending_count) ?></h3>
            <p class="text-xs text-amber-600 font-semibold mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">hourglass_empty</span> Need action
            </p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-9xl text-amber-100 group-hover:scale-110 transition-transform duration-500">pending</span>
    </div>

    <div class="stat-card group border-l-4 border-green-500">
        <div class="relative z-10">
            <p class="text-secondary text-xs font-bold uppercase tracking-widest mb-1">Approved</p>
            <h3 class="text-3xl font-bold text-green-600"><?= number_format($approved_count) ?></h3>
            <p class="text-xs text-green-600 font-semibold mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">check_circle</span> Published
            </p>
        </div>
        <span class="material-symbols-outlined absolute -right-3 -bottom-3 text-9xl text-green-100 group-hover:scale-110 transition-transform duration-500">verified</span>
    </div>
</div>

<!-- Main Content: Recent Submissions + Quick Actions -->
<div class="grid grid-cols-12 gap-8">

    <!-- Recent Submissions Table — 8 columns wide -->
    <div class="col-span-12 lg:col-span-8">
        <div class="bg-white rounded-xl editorial-shadow overflow-hidden border border-outline-variant/10">
            <div class="flex justify-between items-center px-8 py-6 border-b border-slate-50">
                <h4 class="font-headline text-2xl text-primary font-bold">Recent Submissions</h4>
                <a href="papers.php" class="text-primary text-sm font-semibold hover:underline">View all papers →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-widest">Title</th>
                            <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-widest">Author</th>
                            <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-secondary uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($recent_papers)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-secondary text-sm">No submissions yet.</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent_papers as $rp): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5 pr-4 max-w-xs">
                                <p class="font-headline text-base font-semibold text-primary leading-snug">
                                    <?= htmlspecialchars(substr($rp['title'], 0, 55)) ?><?= strlen($rp['title']) > 55 ? '…' : '' ?>
                                </p>
                                <p class="text-xs text-slate-400 mt-1"><?= date('M d, Y · g:i A', strtotime($rp['created_at'])) ?></p>
                            </td>
                            <td class="px-6 py-5 text-sm text-on-surface"><?= htmlspecialchars($rp['author_name']) ?></td>
                            <td class="px-6 py-5">
                                <span class="badge badge-<?= $rp['status'] ?>"><?= ucfirst($rp['status']) ?></span>
                            </td>
                            <td class="px-6 py-5">
                                <a href="papers.php?highlight=<?= $rp['id'] ?>"
                                   class="p-2 hover:bg-white rounded-lg transition-all shadow-sm inline-flex items-center">
                                    <span class="material-symbols-outlined text-primary text-xl">visibility</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions + Status Breakdown — 4 columns wide -->
    <div class="col-span-12 lg:col-span-4 space-y-6">

        <!-- Quick Actions -->
        <div class="bg-primary text-white rounded-xl p-8 shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <h3 class="font-headline text-2xl font-bold mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="users.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-4 rounded-lg transition-all">
                        <span class="material-symbols-outlined">manage_accounts</span>
                        <div>
                            <p class="font-bold text-sm">Manage Users</p>
                            <p class="text-[10px] opacity-70">View &amp; delete researchers</p>
                        </div>
                    </a>
                    <a href="papers.php" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-4 rounded-lg transition-all">
                        <span class="material-symbols-outlined">edit_document</span>
                        <div>
                            <p class="font-bold text-sm">Manage Papers</p>
                            <p class="text-[10px] opacity-70">Approve or reject submissions</p>
                        </div>
                    </a>
                    <a href="papers.php?status=pending" class="flex items-center gap-3 bg-white/10 hover:bg-white/20 p-4 rounded-lg transition-all">
                        <span class="material-symbols-outlined">pending_actions</span>
                        <div>
                            <p class="font-bold text-sm">Review Queue</p>
                            <p class="text-[10px] opacity-70"><?= $pending_count ?> paper<?= $pending_count !== 1 ? 's' : '' ?> pending</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="absolute top-0 right-0 p-4 opacity-20">
                <span class="material-symbols-outlined text-8xl">bolt</span>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="bg-white rounded-xl p-6 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-lg font-bold text-primary mb-5">Status Breakdown</h4>
            <?php
            $breakdown = [
                ['label'=>'Approved','count'=>$approved_count,'color'=>'bg-green-500','pct'=>$total_papers?round($approved_count/$total_papers*100):0],
                ['label'=>'Pending', 'count'=>$pending_count, 'color'=>'bg-amber-400','pct'=>$total_papers?round($pending_count/$total_papers*100):0],
                ['label'=>'Rejected','count'=>$rejected_count,'color'=>'bg-red-400',  'pct'=>$total_papers?round($rejected_count/$total_papers*100):0],
            ];
            foreach ($breakdown as $b): ?>
            <div class="mb-4">
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-on-surface"><?= $b['label'] ?></span>
                    <span class="text-secondary"><?= $b['count'] ?> (<?= $b['pct'] ?>%)</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full <?= $b['color'] ?> rounded-full" style="width:<?= $b['pct'] ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
