<?php
// ============================================================
// admin/update_status.php — Update Paper Status (POST only)
// ============================================================
session_start();
require_once '../includes/auth_check.php';
auth_check('admin');
require_once '../config/db.php';

$paper_id = (int)($_POST['paper_id'] ?? 0);
$status   = trim($_POST['status']   ?? '');

// Whitelist allowed statuses
$allowed = ['pending', 'approved', 'rejected'];

if (!$paper_id || !in_array($status, $allowed, true)) {
    $_SESSION['flash'] = 'Invalid request.';
    header('Location: papers.php');
    exit;
}

$stmt = mysqli_prepare($conn,
    "UPDATE papers SET status = ? WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, 'si', $status, $paper_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash'] = 'Paper status updated to "' . ucfirst($status) . '" successfully.';
} else {
    $_SESSION['flash'] = 'Failed to update status. Please try again.';
}
mysqli_stmt_close($stmt);

header('Location: papers.php');
exit;
