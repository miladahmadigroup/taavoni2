<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'شناسه عضو ارسال نشده']);
    exit();
}

$member_id = intval($_GET['id']);
$conn = connectDB();

// Get member details
$query = "SELECT * FROM members WHERE id = '$member_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    mysqli_close($conn);
    echo json_encode(['success' => false, 'error' => 'عضو یافت نشد']);
    exit();
}

$member = mysqli_fetch_assoc($result);

// Convert created_at to Persian date
$member['created_at_persian'] = persian_date('full', strtotime($member['created_at']));

// Get member projects
$projects_query = "SELECT p.* FROM projects p 
                   JOIN member_projects mp ON p.id = mp.project_id 
                   WHERE mp.member_id = '$member_id'";
$projects_result = mysqli_query($conn, $projects_query);
$projects = array();
while ($project = mysqli_fetch_assoc($projects_result)) {
    $projects[] = $project;
}

// Get member statistics
$stats_query = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN is_approved = 1 THEN amount ELSE 0 END) as approved_amount,
    COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_payments
    FROM payments WHERE member_id = '$member_id'";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Format approved amount
$stats['approved_amount'] = format_currency($stats['approved_amount'] ?: 0);

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'member' => $member,
    'projects' => $projects,
    'stats' => $stats
]);
?>