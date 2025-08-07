<?php
session_start();
require_once 'config.php';

// Check installation
if (!file_exists('installed.txt')) {
    if (isset($_POST['install'])) {
        create_tables();
        
        // Create default admin user
        $conn = connectDB();
        
        // Create default cooperative
        $coop_query = "INSERT INTO cooperatives (name, description) VALUES ('تعاونی مسکن نمونه', 'تعاونی مسکن پیش‌فرض')";
        mysqli_query($conn, $coop_query);
        $coop_id = mysqli_insert_id($conn);
        
        // Create admin user
        $username = sanitize_input($_POST['admin_username']);
        $password = md5(sanitize_input($_POST['admin_password']));
        $full_name = sanitize_input($_POST['admin_name']);
        
        $admin_query = "INSERT INTO members (cooperative_id, username, password, full_name, is_admin) 
                        VALUES ('$coop_id', '$username', '$password', '$full_name', 1)";
        mysqli_query($conn, $admin_query);
        mysqli_close($conn);
        
        // Create installation marker
        file_put_contents('installed.txt', date('Y-m-d H:i:s'));
        
        echo "<script>alert('نصب با موفقیت انجام شد!'); window.location.href='index.php';</script>";
        exit();
    }
    
    // Show installation form
    ?>
    <!DOCTYPE html>
    <html dir="rtl" lang="fa">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>نصب سیستم مدیریت تعاونی مسکن</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
        <link href="style.css?v=<?php echo time(); ?>" rel="stylesheet">
        <style>
            @font-face {
                font-family: 'Vazir';
                src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir-Regular.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
            body, * {
                font-family: 'Vazir', 'Tahoma', 'Iranian Sans', Arial, sans-serif !important;
            }
        </style>
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3>نصب سیستم مدیریت تعاونی مسکن</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">نام مدیر کل:</label>
                                    <input type="text" class="form-control" name="admin_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">نام کاربری مدیر:</label>
                                    <input type="text" class="form-control" name="admin_username" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">رمز عبور مدیر:</label>
                                    <input type="password" class="form-control" name="admin_password" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="install" class="btn btn-primary">نصب سیستم</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Handle login
if (isset($_POST['login'])) {
    $username = sanitize_input($_POST['username']);
    $password = md5(sanitize_input($_POST['password']));
    
    $conn = connectDB();
    $query = "SELECT * FROM members WHERE username = '$username' AND password = '$password' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['is_admin'] = $row['is_admin'];
        $_SESSION['cooperative_id'] = $row['cooperative_id'];
        
        if ($row['is_admin'] == 1) {
            header('Location: admin.php');
        } else {
            header('Location: member.php');
        }
        exit();
    } else {
        $login_error = "نام کاربری یا رمز عبور اشتباه است!";
    }
    mysqli_close($conn);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Get page parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// If user is logged in, redirect to appropriate panel
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['is_admin'] == 1) {
        header('Location: admin.php');
    } else {
        header('Location: member.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم مدیریت تعاونی مسکن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir-Regular.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        body, h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, textarea, select, label {
            font-family: 'Vazir', 'Tahoma', 'Iranian Sans', Arial, sans-serif !important;
        }
        .fas, .far, .fab, .fa, [class*="fa-"], i[class*="fa-"] {
            font-family: "Font Awesome 6 Free" !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>ورود به سیستم</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($login_error)): ?>
                            <div class="alert alert-danger"><?php echo $login_error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">نام کاربری:</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رمز عبور:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" name="login" class="btn btn-primary">ورود</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>