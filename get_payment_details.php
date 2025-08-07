<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'شناسه پرداخت ارسال نشده']);
    exit();
}

$payment_id = intval($_GET['id']);
$conn = connectDB();

// Get payment details
$query = "SELECT p.*, m.full_name as member_name, pr.name as project_name 
          FROM payments p 
          JOIN members m ON p.member_id = m.id 
          JOIN projects pr ON p.project_id = pr.id 
          WHERE p.id = '$payment_id' AND pr.cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    mysqli_close($conn);
    echo json_encode(['success' => false, 'error' => 'پرداخت یافت نشد']);
    exit();
}

$payment = mysqli_fetch_assoc($result);

// Convert dates to Persian
$payment['payment_date_persian'] = persian_date('Y/m/d', strtotime($payment['payment_date']));
if ($payment['check_date']) {
    $payment['check_date_persian'] = persian_date('Y/m/d', strtotime($payment['check_date']));
}
if ($payment['collection_date']) {
    $payment['collection_date_persian'] = persian_date('Y/m/d', strtotime($payment['collection_date']));
}

mysqli_close($conn);

echo json_encode([
    'success' => true,
    'payment' => $payment
]);
?>