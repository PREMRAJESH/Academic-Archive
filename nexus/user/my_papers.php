<?php
// ============================================================
// user/my_papers.php — All papers submitted by this user
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('user');
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch ALL papers for this user
$stmt = mysqli_prepare($conn,
    "SELECT id, title, category, keywords, file_name, status, created_at
     FROM papers WHERE user_id = ?
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$papers = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Flash message from redirect
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$page_title  = 'My Papers';
$active_page = 'my_papers';
$root_prefix = '../';
include '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Header row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h3 class="font-headline text-4xl font-bold text-primary mb-1">My Papers</h3>
            <p class="text-secondary text-sm"><?= count($papers) ?> total submission<?= count($papers) !== 1 ? 's' : '' ?></p>
        </div>
        <a href="submit.php"
           class="inline-flex items-center gap-2 bg-gradient-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition self-start">
            <span class="material-symbols-outlined text-base">add</span>
            New Submission
        </a>
    </div>

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div class="alert alert-success mb-6">
        <span class="material-symbols-outlined text-base">check_circle</span>
        <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Search + Filter bar -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
            <input id="search-input" type="text"
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Search by title…"/>
        </div>
        <select id="status-filter"
            class="px-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 min-w-[160px]">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <!-- Papers Table -->
    <div class="bg-white rounded-2xl editorial-shadow overflow-hidden border border-outline-variant/10">
        <?php if (empty($papers)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-7xl text-primary/20 block mb-4">description</span>
            <h4 class="font-headline text-2xl font-bold text-primary mb-2">No papers yet</h4>
            <p class="text-secondary mb-6">Start by submitting your first research paper.</p>
            <a href="submit.php" class="bg-gradient-primary text-white px-8 py-3 rounded-xl font-bold text-sm shadow-lg hover:opacity-90 transition inline-block">
                Submit Now
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">#</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Title</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Category</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">File</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Submitted</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($papers as $i => $paper): ?>
                    <tr data-title="<?= htmlspecialchars(strtolower($paper['title'])) ?>"
                        data-status="<?= $paper['status'] ?>"
                        class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-400 font-mono"><?= $i + 1 ?></td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="font-semibold text-primary text-sm leading-snug">
                                <?= htmlspecialchars($paper['title']) ?>
                            </p>
                        </td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">
                            <?= htmlspecialchars($paper['category'] ?? '—') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($paper['status'] === 'approved'): ?>
                            <a href="../<?= htmlspecialchars($paper['file_name'] ?? '') ?>"
                               target="_blank"
                               class="flex items-center gap-1 text-primary text-xs font-medium hover:underline">
                                <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                                <?= htmlspecialchars(substr($paper['file_name'] ?? 'View', 0, 20)) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-xs text-slate-400">
                                <?= htmlspecialchars(substr($paper['file_name'] ?? '—', 0, 20)) ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-<?= $paper['status'] ?>"><?= ucfirst($paper['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400 whitespace-nowrap">
                            <?= date('M d, Y', strtotime($paper['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Edit: only allowed if pending -->
                                <?php if ($paper['status'] === 'pending'): ?>
                                <a href="edit_paper.php?id=<?= $paper['id'] ?>"
                                   class="p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors"
                                   title="Edit">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </a>
                                <!-- Delete -->
                                <form method="POST" action="delete_paper.php"
                                      data-confirm="Are you sure you want to delete this paper? This cannot be undone.">
                                    <input type="hidden" name="paper_id" value="<?= $paper['id'] ?>"/>
                                    <button type="submit" class="p-2 text-error hover:bg-error/10 rounded-lg transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-xl">delete</span>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-xs text-slate-400 italic">
                                    <?= $paper['status'] === 'approved' ? 'Published' : 'Closed' ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <!-- No results row (shown by JS) -->
                    <tr id="no-results-row" style="display:none">
                        <td colspan="7" class="px-6 py-10 text-center text-secondary text-sm">
                            No papers match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
