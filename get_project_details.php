<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'شناسه پروژه ارسال نشده']);
    exit();
}

$project_id = intval($_GET['id']);
$conn = connectDB();

// Get project details
$query = "SELECT * FROM projects WHERE id = '$project_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'error' => 'پروژه یافت نشد']);
    exit();
}

$project = mysqli_fetch_assoc($result);

// Convert payment deadline to Persian date
if ($project['payment_deadline']) {
    $project['payment_deadline_persian'] = persian_date('Y/m/d', strtotime($project['payment_deadline']));
}

// Get project statistics
$stats = get_project_stats($project_id);

// Get additional stats
$query = "SELECT 
    COUNT(CASE WHEN p.payment_type = 'check' AND p.is_collected = 0 AND p.is_approved = 1 THEN 1 END) as uncollected_checks,
    SUM(CASE WHEN p.is_approved = 1 THEN p.amount ELSE 0 END) as approved_payments,
    COUNT(CASE WHEN p.is_approved = 0 THEN 1 END) as pending_payments
    FROM payments p WHERE p.project_id = '$project_id'";
$result = mysqli_query($conn, $query);
$additional_stats = mysqli_fetch_assoc($result);

$stats['uncollected_checks'] = $additional_stats['uncollected_checks'];
$stats['approved_payments'] = number_format($additional_stats['approved_payments'] ?: 0);
$stats['pending_payments'] = $additional_stats['pending_payments'];

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'project' => $project,
    'stats' => $stats
]);
?>