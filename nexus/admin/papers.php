<?php
// ============================================================
// admin/papers.php — Manage All Papers (Approve/Reject/Delete)
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('admin');
require_once '../config/db.php';

// Flash messages
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Fetch ALL papers with author name
$stmt = mysqli_prepare($conn,
    "SELECT p.id, p.title, p.category, p.file_name, p.file_path, p.status, p.created_at,
            u.name AS author_name, u.email AS author_email
     FROM papers p
     JOIN users u ON p.user_id = u.id
     ORDER BY p.created_at DESC"
);
mysqli_stmt_execute($stmt);
$papers = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$page_title  = 'Manage Papers';
$active_page = 'papers';
$root_prefix = '../';
include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="font-headline text-4xl font-bold text-primary mb-1">Manage Papers</h3>
            <p class="text-secondary text-sm"><?= count($papers) ?> total submission<?= count($papers) !== 1 ? 's' : '' ?></p>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="alert alert-success mb-6">
        <span class="material-symbols-outlined text-base">check_circle</span>
        <?= htmlspecialchars($flash) ?>
    </div>
    <?php endif; ?>

    <!-- Search + Filter Bar -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
            <input id="search-input" type="text"
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20"
                placeholder="Search by title or author…"/>
        </div>
        <select id="status-filter"
            class="px-4 py-2.5 bg-white border border-outline-variant/30 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 min-w-[160px]">
            <option value="">All statuses</option>
            <option value="pending"  <?= (($_GET['status'] ?? '') === 'pending')  ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= (($_GET['status'] ?? '') === 'approved') ? 'selected' : '' ?>>Approved</option>
            <option value="rejected" <?= (($_GET['status'] ?? '') === 'rejected') ? 'selected' : '' ?>>Rejected</option>
        </select>
    </div>

    <!-- Papers Table -->
    <div class="bg-white rounded-2xl editorial-shadow overflow-hidden border border-outline-variant/10">
        <?php if (empty($papers)): ?>
        <div class="p-16 text-center">
            <span class="material-symbols-outlined text-7xl text-primary/20 block mb-4">library_books</span>
            <p class="text-secondary font-medium">No papers submitted yet.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">#</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Title &amp; Author</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Category</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">File</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary">Date</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-secondary text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($papers as $i => $paper):
                        $search_str = strtolower($paper['title'] . ' ' . $paper['author_name']);
                    ?>
                    <tr data-title="<?= htmlspecialchars($search_str) ?>"
                        data-status="<?= $paper['status'] ?>"
                        class="hover:bg-slate-50/50 transition-colors"
                        id="paper-<?= $paper['id'] ?>">
                        <td class="px-6 py-4 text-sm text-slate-400 font-mono"><?= $i + 1 ?></td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="font-semibold text-primary text-sm leading-snug">
                                <?= htmlspecialchars(substr($paper['title'], 0, 60)) ?><?= strlen($paper['title']) > 60 ? '…' : '' ?>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">by <?= htmlspecialchars($paper['author_name']) ?></p>
                        </td>
                        <td class="px-6 py-4 text-sm text-on-surface-variant">
                            <?= htmlspecialchars($paper['category'] ?? '—') ?>
                        </td>
                        <td class="px-6 py-4">
                            <a href="../<?= htmlspecialchars($paper['file_path']) ?>"
                               target="_blank"
                               class="flex items-center gap-1 text-primary text-xs font-medium hover:underline whitespace-nowrap">
                                <span class="material-symbols-outlined text-sm">picture_as_pdf</span>
                                <?= htmlspecialchars(substr($paper['file_name'], 0, 18)) ?>…
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-<?= $paper['status'] ?>"><?= ucfirst($paper['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400 whitespace-nowrap">
                            <?= date('M d, Y', strtotime($paper['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Approve -->
                                <?php if ($paper['status'] !== 'approved'): ?>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="paper_id" value="<?= $paper['id'] ?>"/>
                                    <input type="hidden" name="status"   value="approved"/>
                                    <button type="submit" title="Approve"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-xl">check_circle</span>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reject -->
                                <?php if ($paper['status'] !== 'rejected'): ?>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="paper_id" value="<?= $paper['id'] ?>"/>
                                    <input type="hidden" name="status"   value="rejected"/>
                                    <button type="submit" title="Reject"
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-xl">cancel</span>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Reset to Pending -->
                                <?php if ($paper['status'] !== 'pending'): ?>
                                <form method="POST" action="update_status.php">
                                    <input type="hidden" name="paper_id" value="<?= $paper['id'] ?>"/>
                                    <input type="hidden" name="status"   value="pending"/>
                                    <button type="submit" title="Reset to Pending"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-xl">refresh</span>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="no-results-row" style="display:none">
                        <td colspan="7" class="px-6 py-10 text-center text-secondary text-sm">
                            No papers match your search or filter.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Pre-apply status filter if URL has ?status=
const urlStatus = new URLSearchParams(window.location.search).get('status');
if (urlStatus) {
    const sf = document.getElementById('status-filter');
    if (sf) { sf.value = urlStatus; sf.dispatchEvent(new Event('change')); }
}
</script>

<?php include '../includes/footer.php'; ?>
