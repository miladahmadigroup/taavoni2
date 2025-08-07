<?php
session_start();
require_once 'config.php';

check_login();

$conn = connectDB();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$success_message = '';
$error_message = '';

// Handle payment submission
if (isset($_POST['add_payment'])) {
    $project_id = sanitize_input($_POST['project_id']);
    $amount = sanitize_input($_POST['amount']);
    $payment_type = sanitize_input($_POST['payment_type']);
    
    // Construct date from separate fields
    $payment_date = $_POST['payment_date_year'] . '/' . 
                   str_pad($_POST['payment_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                   str_pad($_POST['payment_date_day'], 2, '0', STR_PAD_LEFT);
    $payment_date = persian_to_gregorian($payment_date);
    
    $member_id = $_SESSION['user_id'];
    
    $image_path = '';
    if (isset($_FILES['payment_image']) && $_FILES['payment_image']['size'] > 0) {
        $image_path = upload_file($_FILES['payment_image']);
        if (!$image_path) {
            $error_message = "خطا در آپلود تصویر!";
        }
    }
    
    if (!$error_message) {
        if ($payment_type == 'cash') {
            $payment_id = sanitize_input($_POST['payment_id']);
            
            $query = "INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, payment_id, image_path) 
                      VALUES ('$member_id', '$project_id', '$amount', '$payment_type', '$payment_date', '$payment_id', '$image_path')";
        } else { // check
            $check_number = sanitize_input($_POST['check_number']);
            $check_date = $_POST['check_date_year'] . '/' . 
                         str_pad($_POST['check_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                         str_pad($_POST['check_date_day'], 2, '0', STR_PAD_LEFT);
            $check_date = persian_to_gregorian($check_date);
            
            $query = "INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, check_number, check_date, image_path) 
                      VALUES ('$member_id', '$project_id', '$amount', '$payment_type', '$payment_date', '$check_number', '$check_date', '$image_path')";
        }
        
        if (mysqli_query($conn, $query)) {
            $success_message = get_setting('payment_success_message', 'پرداخت شما با موفقیت ثبت شد!');
        } else {
            $error_message = "خطا در ثبت پرداخت!";
        }
    }
}

// Get member projects
$member_projects = get_member_projects($_SESSION['user_id']);

// Get member stats
$member_stats = array();
$member_stats['total_payments'] = 0;
$member_stats['approved_payments'] = 0;
$member_stats['pending_payments'] = 0;

$query = "SELECT COUNT(*) as total, SUM(CASE WHEN is_approved = 1 THEN amount ELSE 0 END) as approved_amount,
          COUNT(CASE WHEN is_approved = 0 THEN 1 END) as pending_count
          FROM payments WHERE member_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);
$stats_row = mysqli_fetch_assoc($result);
$member_stats['total_payments'] = $stats_row['total'];
$member_stats['approved_payments'] = $stats_row['approved_amount'] ?: 0;
$member_stats['pending_payments'] = $stats_row['pending_count'];
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل کاربری - سیستم تعاونی مسکن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir-Regular.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazir';
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir-Bold.woff2') format('woff2');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }
        /* Apply Vazir to text elements, preserve Font Awesome for icons */
        body, h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, textarea, select, label, td, th {
            font-family: 'Vazir', 'Tahoma', 'Iranian Sans', Arial, sans-serif !important;
        }
        /* Ensure Font Awesome icons work properly */
        .fas, .far, .fab, .fa, [class*="fa-"], i[class*="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
            font-weight: 900;
        }
        i[class*="fab"] {
            font-family: "Font Awesome 6 Brands" !important;
            font-weight: 400;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">پنل کاربری</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">خوش آمدید، <?php echo $_SESSION['full_name']; ?></span>
                <a class="nav-link" href="index.php?logout=1">خروج</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="?page=dashboard">
                                <i class="fas fa-tachometer-alt"></i> داشبورد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'add_payment' ? 'active' : ''; ?>" href="?page=add_payment">
                                <i class="fas fa-plus-circle"></i> ثبت پرداخت
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'payments' ? 'active' : ''; ?>" href="?page=payments">
                                <i class="fas fa-list"></i> سوابق پرداخت
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'projects' ? 'active' : ''; ?>" href="?page=projects">
                                <i class="fas fa-building"></i> پروژه‌های من
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'notifications' ? 'active' : ''; ?>" href="?page=notifications">
                                <i class="fas fa-bell"></i> اطلاعیه‌ها
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php
                // Include page content
                switch ($page) {
                    case 'dashboard':
                        include 'member_dashboard.php';
                        break;
                    case 'add_payment':
                        include 'member_add_payment.php';
                        break;
                    case 'payments':
                        include 'member_payments.php';
                        break;
                    case 'projects':
                        include 'member_projects.php';
                        break;
                    case 'notifications':
                        include 'member_notifications.php';
                        break;
                    default:
                        echo "<h2>صفحه یافت نشد!</h2>";
                        break;
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="date_picker.js"></script>
    <script src="upload_progress.js"></script>
</body>
</html>

<?php if (isset($conn)) mysqli_close($conn); ?>