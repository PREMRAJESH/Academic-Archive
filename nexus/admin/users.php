<?php
// ============================================================
// admin/users.php — Manage Registered Users
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('admin');
require_once '../config/db.php';

// Flash message
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Fetch all non-admin users with their paper count
$stmt = mysqli_prepare($conn,
    "SELECT u.id, u.name, u.email, u.created_at,
            COUNT(p.id) AS paper_count
     FROM users u
     LEFT JOIN papers p ON p.user_id = u.id
     WHERE u.role = 'user'
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);
mysqli_stmt_execute($stmt);
$users = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$page_title  = 'Manage Users';
$active_page = 'users';
$root_prefix = '../';
include '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="font-headline text-4xl font-bold text-primary mb-1">Manage Users</h3>
            <p class="text-secondary text-sm"><?= count($users) ?> registered researcher<?= count($users) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-success mb-6">
        <span class="material-symbols-outlined text-base">check_circle</span>
        <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="flex gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
            <input id="search-input" type="text"
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Search by name or email…"/>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl editorial-shadow overflow-hidden border border-outline-variant/10">
        <?php if (empty($users)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-7xl text-primary/20 block mb-4">person_off</span>
            <p class="text-secondary font-medium">No researchers registered yet.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">#</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Email</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Papers</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Joined</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($users as $i => $user): ?>
                    <tr data-title="<?= htmlspecialchars(strtolower($user['name'] . ' ' . $user['email'])) ?>"
                        class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-400 font-mono"><?= $i + 1 ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <span class="font-semibold text-on-surface text-sm"><?= htmlspecialchars($user['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">
                            <?= htmlspecialchars($user['email']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-primary">
                                <span class="material-symbols-outlined text-base">description</span>
                                <?= (int)$user['paper_count'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400 whitespace-nowrap">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="delete_user.php"
                                  data-confirm="Delete <?= htmlspecialchars($user['name']) ?>? This will also delete all their papers and uploaded files. This cannot be undone.">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>"/>
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-4 py-2 text-error hover:bg-error/10 rounded-lg transition-colors text-sm font-semibold">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="no-results-row" style="display:none">
                        <td colspan="6" class="px-6 py-10 text-center text-secondary text-sm">
                            No users match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
