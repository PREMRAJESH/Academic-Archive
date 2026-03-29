<?php
// ============================================================
// admin/delete_user.php — Delete a User (POST only)
// Cascade: also removes all their papers + uploaded PDF files
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('admin');
require_once '../config/db.php';

$user_id = (int)($_POST['user_id'] ?? 0);

if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Safety: never delete an admin
$stmt = mysqli_prepare($conn, "SELECT id, role FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user || $user['role'] === 'admin') {
    $_SESSION['flash'] = 'Cannot delete this user.';
    header('Location: users.php');
    exit;
}

// Fetch all file paths for this user's papers before deletion
$pstmt = mysqli_prepare($conn, "SELECT file_path FROM papers WHERE user_id = ?");
mysqli_stmt_bind_param($pstmt, 'i', $user_id);
mysqli_stmt_execute($pstmt);
$files = mysqli_fetch_all(mysqli_stmt_get_result($pstmt), MYSQLI_ASSOC);
mysqli_stmt_close($pstmt);

// Delete physical PDF files from disk
$base = dirname(__DIR__) . '/';
foreach ($files as $f) {
    $path = $base . $f['file_path'];
    if (file_exists($path)) @unlink($path);
}

// Delete user — MySQL CASCADE will remove their papers automatically
$del = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
mysqli_stmt_bind_param($del, 'i', $user_id);

if (mysqli_stmt_execute($del)) {
    $_SESSION['flash'] = 'User and all their papers have been deleted successfully.';
} else {
    $_SESSION['flash'] = 'Failed to delete user.';
}
mysqli_stmt_close($del);

header('Location: users.php');
exit;
