<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'شناسه اطلاعیه ارسال نشده']);
    exit();
}

$notification_id = intval($_GET['id']);
$conn = connectDB();

// Get notification details
$query = "SELECT n.*, p.name as project_name FROM notifications n 
          JOIN projects p ON n.project_id = p.id 
          WHERE n.id = '$notification_id' AND p.cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'error' => 'اطلاعیه یافت نشد']);
    exit();
}

$notification = mysqli_fetch_assoc($result);

// Convert created_at to Persian date
$notification['created_at_persian'] = persian_date('full', strtotime($notification['created_at']));

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'notification' => $notification
]);
?>