<?php
// ============================================================
// user/submit.php — Submit New Research Paper
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('user');
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// ── Upload directory (create if missing) ──────────────────────
$upload_dir = dirname(__DIR__) . '/uploads/papers/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// ── Handle POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($abstract)) {
        $error = 'Title and Abstract are required fields.';

    } elseif (empty($_FILES['paper_file']['name'])) {
        $error = 'Please upload a PDF file.';

    } else {
        $file      = $_FILES['paper_file'];
        $orig_name = basename($file['name']);
        $tmp_path  = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext  = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        // ── Validation checks ──────────────────────────

        // 1. File size (max 5 MB)
        if ($file_size > 5 * 1024 * 1024) {
            $error = 'File is too large. Maximum allowed size is 5 MB.';

        // 2. Extension check
        } elseif ($file_ext !== 'pdf') {
            $error = 'Only PDF files are accepted.';

        // 3. MIME type check (real content check)
        } elseif (mime_content_type($tmp_path) !== 'application/pdf') {
            $error = 'The uploaded file does not appear to be a valid PDF.';

        } else {
            // ── Safe to upload ──────────────────────────
            // Generate unique filename to prevent overwriting
            $new_file_name = uniqid('paper_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig_name);
            $dest_path     = $upload_dir . $new_file_name;

            if (move_uploaded_file($tmp_path, $dest_path)) {
                // Store relative path from nexus/ root
                $relative_path = 'uploads/papers/' . $new_file_name;

                // Insert into DB (prepared statement)
                $stmt = mysqli_prepare($conn,
                    "INSERT INTO papers (user_id, title, abstract, keywords, category, file_name, file_path, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')"
                );
                mysqli_stmt_bind_param($stmt, 'issssss',
                    $user_id, $title, $abstract, $keywords, $category,
                    $orig_name, $relative_path
                );

                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Paper submitted successfully! It is now pending admin review.';
                    // Clear form
                    $_POST = [];
                } else {
                    // Remove uploaded file if DB insert fails
                    @unlink($dest_path);
                    $error = 'Failed to save paper. Please try again.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to upload file. Check server permissions.';
            }
        }
    }
}

$page_title  = 'Submit Paper';
$active_page = 'submit';
$root_prefix = '../';
include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h3 class="font-headline text-4xl font-bold text-primary mb-2">New Submission</h3>
        <p class="text-secondary">Fill in your manuscript details and upload the PDF for editorial review.</p>
    </div>

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
        <a href="my_papers.php" class="ml-2 font-bold underline">View my papers →</a>
    </div>
    <?php endif; ?>

    <!-- Submission Form -->
    <form method="POST" action="submit.php" enctype="multipart/form-data" class="space-y-8" id="submit-form">

        <!-- Section 1: Metadata -->
        <div class="bg-white rounded-xl p-8 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-xl text-primary font-bold mb-6">Paper Metadata</h4>
            <div class="space-y-5">

                <!-- Title -->
                <div>
                    <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="title">
                        Manuscript Title <span class="text-error">*</span>
                    </label>
                    <input id="title" name="title" type="text" required
                        class="form-input"
                        placeholder="e.g., Quantum Entanglement in Macroscopic Biological Systems"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"/>
                </div>

                <!-- Category + Keywords -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="category">
                            Subject Category
                        </label>
                        <select id="category" name="category" class="form-input">
                            <option value="">Select a category…</option>
                            <?php
                            $categories = ['Theoretical Physics','Biochemistry','Computational Neuroscience',
                                           'Applied Mathematics','Computer Science','Economics',
                                           'Sociology','Biology','Chemistry','Environmental Science',
                                           'Medicine & Health','History','Philosophy','Engineering','Other'];
                            foreach ($categories as $cat):
                                $sel = (($_POST['category'] ?? '') === $cat) ? 'selected' : '';
                            ?>
                            <option value="<?= $cat ?>" <?= $sel ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="keywords">
                            Keywords
                        </label>
                        <input id="keywords" name="keywords" type="text"
                            class="form-input"
                            placeholder="Separate keywords with commas"
                            value="<?= htmlspecialchars($_POST['keywords'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Abstract -->
                <div>
                    <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="abstract">
                        Abstract <span class="text-error">*</span>
                    </label>
                    <textarea id="abstract" name="abstract" required rows="7"
                        class="form-input resize-none"
                        placeholder="Briefly describe the objective, methodology, and key findings…"
                    ><?= htmlspecialchars($_POST['abstract'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Section 2: File Upload -->
        <div class="bg-white rounded-xl p-8 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-xl text-primary font-bold mb-2">File Upload</h4>
            <p class="text-secondary text-sm mb-6">PDF only · Maximum 5 MB</p>

            <div id="upload-zone" class="upload-zone" onclick="document.getElementById('paper-file').click()">
                <input type="file" id="paper-file" name="paper_file" accept=".pdf,application/pdf" class="hidden" required/>
                <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-primary text-3xl">cloud_upload</span>
                </div>
                <h5 class="font-headline text-xl text-primary font-bold mb-2">Drag &amp; Drop or Click to Browse</h5>
                <p class="text-secondary text-sm">Accepted: PDF · Max size: 5 MB</p>
                <p id="file-label" class="text-primary font-medium text-sm mt-3 hidden"></p>
            </div>

            <!-- File preview area -->
            <div id="file-status" class="hidden"></div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4">
            <a href="dashboard.php" class="flex items-center gap-2 text-secondary font-semibold hover:text-primary transition-colors px-4 py-2 rounded-lg">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to Dashboard
            </a>
            <button type="submit"
                class="px-10 py-4 bg-gradient-primary text-white font-bold rounded-xl shadow-xl shadow-primary/30 hover:scale-105 transition-transform text-sm">
                Submit for Review
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
