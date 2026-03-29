<?php
// ============================================================
// user/edit_paper.php — Edit a Pending Paper
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('user');
require_once '../config/db.php';

$user_id  = $_SESSION['user_id'];
$paper_id = (int)($_GET['id'] ?? $_POST['paper_id'] ?? 0);

if (!$paper_id) {
    header('Location: my_papers.php');
    exit;
}

// Fetch the paper — verify it belongs to this user and is pending
$stmt = mysqli_prepare($conn,
    "SELECT * FROM papers WHERE id = ? AND user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ii', $paper_id, $user_id);
mysqli_stmt_execute($stmt);
$paper = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$paper) {
    $_SESSION['flash'] = 'Paper not found or you do not have permission to edit it.';
    header('Location: my_papers.php');
    exit;
}

// Only pending papers can be edited
if ($paper['status'] !== 'pending') {
    $_SESSION['flash'] = 'Only pending papers can be edited.';
    header('Location: my_papers.php');
    exit;
}

$error   = '';
$success = '';
$upload_dir = dirname(__DIR__) . '/uploads/papers/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']    ?? '');
    $abstract = trim($_POST['abstract'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($abstract)) {
        $error = 'Title and Abstract are required.';
    } else {
        $new_file_name = $paper['file_name'];
        $new_file_path = $paper['file_path'];

        // Handle optional file re-upload
        if (!empty($_FILES['paper_file']['name'])) {
            $file     = $_FILES['paper_file'];
            $orig     = basename($file['name']);
            $tmp      = $file['tmp_name'];
            $ext      = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

            if ($file['size'] > 5 * 1024 * 1024) {
                $error = 'File is too large. Maximum 5 MB.';
            } elseif ($ext !== 'pdf') {
                $error = 'Only PDF files are accepted.';
            } elseif (mime_content_type($tmp) !== 'application/pdf') {
                $error = 'The file does not appear to be a valid PDF.';
            } else {
                $gen          = uniqid('paper_', true) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig);
                $dest         = $upload_dir . $gen;

                if (move_uploaded_file($tmp, $dest)) {
                    // Remove old file
                    $old_path = dirname(__DIR__) . '/' . $paper['file_path'];
                    if (file_exists($old_path)) @unlink($old_path);

                    $new_file_name = $orig;
                    $new_file_path = 'uploads/papers/' . $gen;
                } else {
                    $error = 'Failed to upload replacement file.';
                }
            }
        }

        if (!$error) {
            $stmt2 = mysqli_prepare($conn,
                "UPDATE papers SET title=?, abstract=?, keywords=?, category=?, file_name=?, file_path=?
                 WHERE id=? AND user_id=?"
            );
            mysqli_stmt_bind_param($stmt2, 'ssssssii',
                $title, $abstract, $keywords, $category,
                $new_file_name, $new_file_path, $paper_id, $user_id
            );

            if (mysqli_stmt_execute($stmt2)) {
                $_SESSION['flash'] = 'Paper updated successfully.';
                header('Location: my_papers.php');
                exit;
            } else {
                $error = 'Update failed. Please try again.';
            }
            mysqli_stmt_close($stmt2);
        }
    }
}

// Pre-fill form with existing values or POST values on error
$f_title    = htmlspecialchars($_POST['title']    ?? $paper['title']);
$f_abstract = htmlspecialchars($_POST['abstract'] ?? $paper['abstract']);
$f_keywords = htmlspecialchars($_POST['keywords'] ?? $paper['keywords']);
$f_category = $_POST['category'] ?? $paper['category'];

$categories = ['Theoretical Physics','Biochemistry','Computational Neuroscience',
               'Applied Mathematics','Computer Science','Economics',
               'Sociology','Biology','Chemistry','Environmental Science',
               'Medicine & Health','History','Philosophy','Engineering','Other'];

$page_title  = 'Edit Paper';
$active_page = 'my_papers';
$root_prefix = '../';
include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="my_papers.php" class="inline-flex items-center gap-1 text-secondary hover:text-primary text-sm font-medium mb-4 transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span> Back to My Papers
        </a>
        <h3 class="font-headline text-4xl font-bold text-primary mb-2">Edit Paper</h3>
        <p class="text-secondary text-sm">You can edit details while your paper is in pending review.</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <span class="material-symbols-outlined text-base">error</span>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="edit_paper.php?id=<?= $paper_id ?>" enctype="multipart/form-data" class="space-y-8">
        <input type="hidden" name="paper_id" value="<?= $paper_id ?>"/>

        <div class="bg-white rounded-xl p-8 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-xl text-primary font-bold mb-6">Paper Details</h4>
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="title">
                        Title <span class="text-error">*</span>
                    </label>
                    <input id="title" name="title" type="text" required
                        class="form-input" value="<?= $f_title ?>"/>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2">Category</label>
                        <select name="category" class="form-input">
                            <option value="">Select a category…</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($f_category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2">Keywords</label>
                        <input name="keywords" type="text" class="form-input"
                            placeholder="Separate with commas" value="<?= $f_keywords ?>"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-secondary uppercase tracking-widest mb-2" for="abstract">
                        Abstract <span class="text-error">*</span>
                    </label>
                    <textarea id="abstract" name="abstract" required rows="7" class="form-input resize-none"><?= $f_abstract ?></textarea>
                </div>
            </div>
        </div>

        <!-- File (optional replacement) -->
        <div class="bg-white rounded-xl p-8 editorial-shadow border border-outline-variant/10">
            <h4 class="font-headline text-xl text-primary font-bold mb-2">Replace PDF (optional)</h4>
            <p class="text-secondary text-sm mb-4">
                Current file: <span class="font-medium text-primary"><?= htmlspecialchars($paper['file_name']) ?></span>
            </p>
            <div id="upload-zone" class="upload-zone" onclick="document.getElementById('paper-file').click()">
                <input type="file" id="paper-file" name="paper_file" accept=".pdf,application/pdf" class="hidden"/>
                <span class="material-symbols-outlined text-primary text-4xl mb-2 block mx-auto text-center">upload_file</span>
                <p class="text-secondary text-sm text-center">Click to upload a new PDF (max 5 MB) · Leave empty to keep current file</p>
            </div>
            <div id="file-status" class="hidden mt-2"></div>
        </div>

        <div class="flex items-center justify-between pt-4">
            <a href="my_papers.php" class="text-secondary font-semibold hover:text-primary transition-colors px-4 py-3">
                Cancel
            </a>
            <button type="submit"
                class="px-10 py-4 bg-gradient-primary text-white font-bold rounded-xl shadow-xl hover:scale-105 transition-transform text-sm">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
