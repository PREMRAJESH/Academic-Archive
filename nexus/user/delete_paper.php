<?php
// ============================================================
// user/delete_paper.php — Delete a Paper (POST only)
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('user');
require_once '../config/db.php';

$user_id  = $_SESSION['user_id'];
$paper_id = (int)($_POST['paper_id'] ?? 0);

if (!$paper_id) {
    header('Location: my_papers.php');
    exit;
}

// Verify the paper belongs to this user
$stmt = mysqli_prepare($conn,
    "SELECT id, file_path FROM papers WHERE id = ? AND user_id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ii', $paper_id, $user_id);
mysqli_stmt_execute($stmt);
$paper = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$paper) {
    $_SESSION['flash'] = 'Paper not found.';
    header('Location: my_papers.php');
    exit;
}

// Delete the physical file from disk
$file_path = dirname(__DIR__) . '/' . $paper['file_path'];
if (file_exists($file_path)) {
    @unlink($file_path);
}

// Delete DB record
$stmt2 = mysqli_prepare($conn, "DELETE FROM papers WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt2, 'ii', $paper_id, $user_id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_close($stmt2);

$_SESSION['flash'] = 'Paper deleted successfully.';
header('Location: my_papers.php');
exit;
