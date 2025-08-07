<?php
session_start();
require_once 'config.php';

check_login();
check_admin();

$conn = connectDB();
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Add new project
    if (isset($_POST['add_project'])) {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $min_payment = sanitize_input($_POST['min_payment']);
        $payment_deadline = $_POST['payment_deadline'] ? persian_to_gregorian($_POST['payment_deadline']) : null;
        $cooperative_id = $_SESSION['cooperative_id'];
        
        $query = "INSERT INTO projects (cooperative_id, name, description, min_payment, payment_deadline) 
                  VALUES ('$cooperative_id', '$name', '$description', '$min_payment', " . ($payment_deadline ? "'$payment_deadline'" : "NULL") . ")";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "پروژه با موفقیت اضافه شد!";
        } else {
            $error_message = "خطا در افزودن پروژه!";
        }
    }
    
    // Edit project
    if (isset($_POST['edit_project'])) {
        $project_id = sanitize_input($_POST['project_id']);
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $min_payment = sanitize_input($_POST['min_payment']);
        $payment_deadline = $_POST['payment_deadline'] ? persian_to_gregorian($_POST['payment_deadline']) : null;
        
        $query = "UPDATE projects SET name = '$name', description = '$description', 
                  min_payment = '$min_payment', payment_deadline = " . ($payment_deadline ? "'$payment_deadline'" : "NULL") . " 
                  WHERE id = '$project_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "پروژه بروزرسانی شد!";
        } else {
            $error_message = "خطا در بروزرسانی پروژه!";
        }
    }
    
    // Add new member
    if (isset($_POST['add_member'])) {
        $username = sanitize_input($_POST['username']);
        $password = md5(sanitize_input($_POST['password']));
        $full_name = sanitize_input($_POST['full_name']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $cooperative_id = $_SESSION['cooperative_id'];
        
        $query = "INSERT INTO members (cooperative_id, username, password, full_name, phone, email) 
                  VALUES ('$cooperative_id', '$username', '$password', '$full_name', '$phone', '$email')";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "عضو جدید با موفقیت اضافه شد!";
        } else {
            $error_message = "خطا در افزودن عضو!";
        }
    }
    
    // Edit member
    if (isset($_POST['edit_member'])) {
        $member_id = sanitize_input($_POST['member_id']);
        $username = sanitize_input($_POST['username']);
        $full_name = sanitize_input($_POST['full_name']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'] ? md5(sanitize_input($_POST['password'])) : '';
        
        if ($password) {
            $query = "UPDATE members SET username = '$username', full_name = '$full_name', 
                      phone = '$phone', email = '$email', password = '$password' 
                      WHERE id = '$member_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
        } else {
            $query = "UPDATE members SET username = '$username', full_name = '$full_name', 
                      phone = '$phone', email = '$email' 
                      WHERE id = '$member_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
        }
        
        if (mysqli_query($conn, $query)) {
            // Handle project assignments
            if (isset($_POST['project_ids']) && is_array($_POST['project_ids'])) {
                // First remove all existing assignments
                mysqli_query($conn, "DELETE FROM member_projects WHERE member_id = '$member_id'");
                
                // Add new assignments
                foreach ($_POST['project_ids'] as $project_id) {
                    $project_id = intval($project_id);
                    mysqli_query($conn, "INSERT INTO member_projects (member_id, project_id) VALUES ('$member_id', '$project_id')");
                }
            } else {
                // Remove all assignments if none selected
                mysqli_query($conn, "DELETE FROM member_projects WHERE member_id = '$member_id'");
            }
            
            $success_message = "عضو بروزرسانی شد!";
        } else {
            $error_message = "خطا در بروزرسانی عضو!";
        }
    }
    
    // Add member to project
    if (isset($_POST['add_member_to_project'])) {
        $member_id = sanitize_input($_POST['member_id']);
        $project_id = sanitize_input($_POST['project_id']);
        
        $query = "INSERT IGNORE INTO member_projects (member_id, project_id) VALUES ('$member_id', '$project_id')";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "عضو به پروژه اضافه شد!";
        } else {
            $error_message = "خطا در افزودن عضو به پروژه!";
        }
    }
    
    // Approve/reject payment
    if (isset($_POST['approve_payment'])) {
        $payment_id = sanitize_input($_POST['payment_id']);
        $action = sanitize_input($_POST['action']);
        $admin_note_public = sanitize_input($_POST['admin_note_public']);
        $admin_note_private = sanitize_input($_POST['admin_note_private']);
        
        $is_approved = ($action == 'approve') ? 1 : 0;
        
        $query = "UPDATE payments SET is_approved = '$is_approved', 
                  admin_note_public = '$admin_note_public', 
                  admin_note_private = '$admin_note_private' 
                  WHERE id = '$payment_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = $action == 'approve' ? "پرداخت تایید شد!" : "پرداخت رد شد!";
        }
    }
    
    // Edit payment
    if (isset($_POST['edit_payment'])) {
        $payment_id = sanitize_input($_POST['payment_id']);
        $amount = sanitize_input($_POST['amount']);
        $payment_type = sanitize_input($_POST['payment_type']);
        $is_approved = intval($_POST['is_approved']);
        $admin_note_public = sanitize_input($_POST['admin_note_public']);
        $admin_note_private = sanitize_input($_POST['admin_note_private']);
        
        // Build payment date
        $payment_date = $_POST['payment_date_year'] . '/' . 
                       str_pad($_POST['payment_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                       str_pad($_POST['payment_date_day'], 2, '0', STR_PAD_LEFT);
        $payment_date = persian_to_gregorian($payment_date);
        
        if ($payment_type == 'cash') {
            $payment_id_field = sanitize_input($_POST['payment_id_field']);
            $query = "UPDATE payments SET 
                      amount = '$amount',
                      payment_type = '$payment_type',
                      payment_date = '$payment_date',
                      payment_id = '$payment_id_field',
                      check_number = NULL,
                      check_date = NULL,
                      is_approved = '$is_approved',
                      is_collected = 0,
                      collection_date = NULL,
                      admin_note_public = '$admin_note_public',
                      admin_note_private = '$admin_note_private'
                      WHERE id = '$payment_id'";
        } else {
            $check_number = sanitize_input($_POST['check_number']);
            $check_date = $_POST['check_date_year'] . '/' . 
                         str_pad($_POST['check_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                         str_pad($_POST['check_date_day'], 2, '0', STR_PAD_LEFT);
            $check_date = persian_to_gregorian($check_date);
            
            $is_collected = intval($_POST['is_collected']);
            $collection_date = null;
            if ($is_collected && $_POST['collection_date_year']) {
                $collection_date = $_POST['collection_date_year'] . '/' . 
                                  str_pad($_POST['collection_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                                  str_pad($_POST['collection_date_day'], 2, '0', STR_PAD_LEFT);
                $collection_date = persian_to_gregorian($collection_date);
            }
            
            $query = "UPDATE payments SET 
                      amount = '$amount',
                      payment_type = '$payment_type',
                      payment_date = '$payment_date',
                      payment_id = NULL,
                      check_number = '$check_number',
                      check_date = '$check_date',
                      is_approved = '$is_approved',
                      is_collected = '$is_collected',
                      collection_date = " . ($collection_date ? "'$collection_date'" : "NULL") . ",
                      admin_note_public = '$admin_note_public',
                      admin_note_private = '$admin_note_private'
                      WHERE id = '$payment_id'";
        }
        
        if (mysqli_query($conn, $query)) {
            $success_message = "پرداخت بروزرسانی شد!";
        } else {
            $error_message = "خطا در بروزرسانی پرداخت!";
        }
    }
    
    // Mark check as collected
    if (isset($_POST['mark_collected'])) {
        $payment_id = sanitize_input($_POST['payment_id']);
        $collection_date = persian_to_gregorian($_POST['collection_date']);
        
        $query = "UPDATE payments SET is_collected = 1, collection_date = '$collection_date' 
                  WHERE id = '$payment_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "چک به عنوان وصول شده علامت‌گذاری شد!";
        }
    }
    
    // Toggle member status
    if (isset($_POST['toggle_member'])) {
        $member_id = sanitize_input($_POST['member_id']);
        $new_status = sanitize_input($_POST['new_status']);
        
        $query = "UPDATE members SET is_active = '$new_status' WHERE id = '$member_id'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = $new_status ? "عضو فعال شد!" : "عضو غیرفعال شد!";
        }
    }
    
    // Add notification
    if (isset($_POST['add_notification'])) {
        if (isset($_POST['project_ids']) && is_array($_POST['project_ids'])) {
            $project_ids = $_POST['project_ids'];
            $title = sanitize_input($_POST['title']);
            $content = sanitize_input($_POST['content']);
            
            foreach ($project_ids as $project_id) {
                $query = "INSERT INTO notifications (project_id, title, content) 
                          VALUES ('$project_id', '$title', '$content')";
                mysqli_query($conn, $query);
            }
            
            $success_message = "اطلاعیه با موفقیت ثبت شد!";
        } else {
            $error_message = "لطفاً حداقل یک پروژه انتخاب کنید!";
        }
    }
    
    // Edit notification
    if (isset($_POST['edit_notification'])) {
        $notification_id = sanitize_input($_POST['notification_id']);
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);
        
        $query = "UPDATE notifications n 
                  JOIN projects p ON n.project_id = p.id 
                  SET n.title = '$title', n.content = '$content' 
                  WHERE n.id = '$notification_id' AND p.cooperative_id = '{$_SESSION['cooperative_id']}'";
        
        if (mysqli_query($conn, $query)) {
            $success_message = "اطلاعیه بروزرسانی شد!";
        } else {
            $error_message = "خطا در بروزرسانی اطلاعیه!";
        }
    }
    
    // Update payment message
    if (isset($_POST['update_message'])) {
        $message = sanitize_input($_POST['payment_message']);
        set_setting('payment_success_message', $message);
        $success_message = "پیام ثبت پرداخت بروزرسانی شد!";
    }
    
    // Add payment for member (by admin)
    if (isset($_POST['add_payment_for_member'])) {
        $member_id = sanitize_input($_POST['member_id']);
        $project_id = sanitize_input($_POST['project_id']);
        $amount = sanitize_input($_POST['amount']);
        $payment_type = sanitize_input($_POST['payment_type']);
        $admin_note = sanitize_input($_POST['admin_note']);
        
        // Handle uploaded file - Check both methods
        $image_path = '';
        
        // Method 1: Ajax uploaded file path
        if (isset($_POST['uploaded_file_path']) && !empty($_POST['uploaded_file_path'])) {
            $temp_path = sanitize_input($_POST['uploaded_file_path']);
            // Verify file exists
            if (file_exists($temp_path)) {
                $image_path = $temp_path;
                error_log("Using ajax uploaded file: $image_path");
            } else {
                error_log("Ajax uploaded file not found: $temp_path");
            }
        }
        
        // Method 2: Traditional file upload (fallback)
        if (empty($image_path) && isset($_FILES['payment_image']) && $_FILES['payment_image']['size'] > 0) {
            $image_path = upload_file($_FILES['payment_image'], 'uploads/payments/');
            if ($image_path) {
                error_log("Using traditional upload: $image_path");
            } else {
                error_log("Traditional upload failed");
            }
        }
        
        if ($payment_type == 'cash') {
            // For cash payments
            if (isset($_POST['payment_date_year']) && $_POST['payment_date_year']) {
                $payment_date = $_POST['payment_date_year'] . '/' . 
                               str_pad($_POST['payment_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                               str_pad($_POST['payment_date_day'], 2, '0', STR_PAD_LEFT);
                $payment_date = persian_to_gregorian($payment_date);
            } else {
                $payment_date = date('Y-m-d');
            }
            
            $payment_id_field = isset($_POST['payment_id_field']) ? sanitize_input($_POST['payment_id_field']) : '';
            
            $query = "INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, payment_id, image_path, is_approved, admin_note_public) 
                      VALUES ('$member_id', '$project_id', '$amount', '$payment_type', '$payment_date', '$payment_id_field', '$image_path', 1, '$admin_note')";
                      
        } else {
            // For check payments
            if (isset($_POST['check_date_year']) && $_POST['check_date_year']) {
                $check_date = $_POST['check_date_year'] . '/' . 
                             str_pad($_POST['check_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                             str_pad($_POST['check_date_day'], 2, '0', STR_PAD_LEFT);
                $check_date = persian_to_gregorian($check_date);
            } else {
                $check_date = date('Y-m-d');
            }
            
            $check_number = isset($_POST['check_number']) ? sanitize_input($_POST['check_number']) : '';
            $payment_date = $check_date;
            
            $query = "INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, check_number, check_date, image_path, is_approved, admin_note_public) 
                      VALUES ('$member_id', '$project_id', '$amount', '$payment_type', '$payment_date', '$check_number', '$check_date', '$image_path', 1, '$admin_note')";
        }
        
        // Log the query for debugging
        error_log("Payment insert query: $query");
        error_log("Image path: $image_path");
        
        // Execute query
        if (mysqli_query($conn, $query)) {
            $payment_id = mysqli_insert_id($conn);
            $success_message = "پرداخت برای عضو با شناسه $payment_id با موفقیت ثبت شد!";
            if ($image_path) {
                $success_message .= " (همراه با تصویر)";
            }
            error_log("Payment inserted successfully with ID: $payment_id, Image: $image_path");
        } else {
            $error_message = "خطا در ثبت پرداخت: " . mysqli_error($conn);
            error_log("Payment insert failed: " . mysqli_error($conn));
        }
    }
}

// Handle GET actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    if ($action === 'delete') {
        if ($page === 'projects') {
            $query = "DELETE FROM projects WHERE id = '$id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
            if (mysqli_query($conn, $query)) {
                $success_message = "پروژه حذف شد!";
            }
        } elseif ($page === 'members') {
            if ($id != $_SESSION['user_id']) {
                $query = "DELETE FROM members WHERE id = '$id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
                if (mysqli_query($conn, $query)) {
                    $success_message = "عضو حذف شد!";
                }
            }
        } elseif ($page === 'notifications') {
            $query = "DELETE FROM notifications WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                $success_message = "اطلاعیه حذف شد!";
            }
        } elseif ($page === 'payments') {
            // Delete payment and its image file
            $query = "SELECT image_path FROM payments p 
                      JOIN projects pr ON p.project_id = pr.id 
                      WHERE p.id = '$id' AND pr.cooperative_id = '{$_SESSION['cooperative_id']}'";
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $payment = mysqli_fetch_assoc($result);
                
                // Delete payment record
                $delete_query = "DELETE p FROM payments p 
                                 JOIN projects pr ON p.project_id = pr.id 
                                 WHERE p.id = '$id' AND pr.cooperative_id = '{$_SESSION['cooperative_id']}'";
                
                if (mysqli_query($conn, $delete_query)) {
                    // Delete image file if exists
                    if ($payment['image_path'] && file_exists($payment['image_path'])) {
                        unlink($payment['image_path']);
                    }
                    $success_message = "پرداخت حذف شد!";
                } else {
                    $error_message = "خطا در حذف پرداخت!";
                }
            }
        }
    }
}

// Get statistics
$stats = array();

// Total members
$query = "SELECT COUNT(*) as count FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$stats['total_members'] = $row['count'];

// Total projects
$query = "SELECT COUNT(*) as count FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$stats['total_projects'] = $row['count'];

// Pending payments
$query = "SELECT COUNT(*) as count FROM payments p 
          JOIN projects pr ON p.project_id = pr.id 
          WHERE pr.cooperative_id = '{$_SESSION['cooperative_id']}' AND p.is_approved = 0";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$stats['pending_payments'] = $row['count'];

// Total payments this month
$query = "SELECT SUM(p.amount) as total FROM payments p 
          JOIN projects pr ON p.project_id = pr.id 
          WHERE pr.cooperative_id = '{$_SESSION['cooperative_id']}' 
          AND p.is_approved = 1 AND MONTH(p.created_at) = MONTH(NOW()) AND YEAR(p.created_at) = YEAR(NOW())";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$stats['monthly_payments'] = $row['total'] ?: 0;
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت - سیستم تعاونی مسکن</title>
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
        body, h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, textarea, select, label, td, th {
            font-family: 'Vazir', 'Tahoma', 'Iranian Sans', Arial, sans-serif !important;
        }
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">پنل مدیریت</a>
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
                            <a class="nav-link <?php echo $page == 'projects' ? 'active' : ''; ?>" href="?page=projects">
                                <i class="fas fa-building"></i> پروژه‌ها
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'members' ? 'active' : ''; ?>" href="?page=members">
                                <i class="fas fa-users"></i> اعضا
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'payments' ? 'active' : ''; ?>" href="?page=payments">
                                <i class="fas fa-money-bill"></i> پرداخت‌ها
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'notifications' ? 'active' : ''; ?>" href="?page=notifications">
                                <i class="fas fa-bell"></i> اطلاعیه‌ها
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page == 'settings' ? 'active' : ''; ?>" href="?page=settings">
                                <i class="fas fa-cog"></i> تنظیمات
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
                        include 'admin_dashboard.php';
                        break;
                    case 'projects':
                        include 'admin_projects_enhanced.php';
                        break;
                    case 'members':
                        include 'admin_members_enhanced.php';
                        break;
                    case 'payments':
                        include 'admin_payments_enhanced.php';
                        break;
                    case 'notifications':
                        include 'admin_notifications_enhanced.php';
                        break;
                    case 'settings':
                        include 'admin_settings.php';
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
    <script src="admin.js"></script>
</body>
</html>

<?php mysqli_close($conn); ?>