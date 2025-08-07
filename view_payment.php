<?php
session_start();
require_once 'config.php';

check_login();

if (!isset($_GET['id'])) {
    die('شناسه پرداخت مشخص نشده است!');
}

$payment_id = sanitize_input($_GET['id']);
$conn = connectDB();

// Get payment details
$query = "SELECT p.*, m.full_name as member_name, pr.name as project_name, 
          c.name as cooperative_name
          FROM payments p 
          JOIN members m ON p.member_id = m.id 
          JOIN projects pr ON p.project_id = pr.id 
          JOIN cooperatives c ON pr.cooperative_id = c.id
          WHERE p.id = '$payment_id'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die('پرداخت مورد نظر یافت نشد!');
}

$payment = mysqli_fetch_assoc($result);

// Check permissions
if ($_SESSION['is_admin'] != 1 && $payment['member_id'] != $_SESSION['user_id']) {
    die('شما اجازه مشاهده این پرداخت را ندارید!');
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزئیات پرداخت - سیستم تعاونی مسکن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-size: 14px;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .info-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            flex: 0 0 40%;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
            text-align: left;
        }
        
        .status-badge {
            font-size: 0.9em;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .image-container {
            max-height: 400px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .image-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        
        .print-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media print {
            .print-btn, .btn, .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .payment-header {
                background: #007bff !important;
                color: white !important;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Header -->
        <div class="payment-header text-center">
            <h2><i class="fas fa-receipt"></i> جزئیات پرداخت</h2>
            <p class="mb-0">شناسه پرداخت: <?php echo persian_number($payment['id']); ?></p>
        </div>

        <div class="row">
            <!-- Payment Information -->
            <div class="col-md-8">
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> اطلاعات کلی</h5>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <span class="info-label">نام پرداخت کننده:</span>
                            <span class="info-value"><?php echo $payment['member_name']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">نام پروژه:</span>
                            <span class="info-value"><?php echo $payment['project_name']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">تعاونی:</span>
                            <span class="info-value"><?php echo $payment['cooperative_name']; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">مبلغ پرداخت:</span>
                            <span class="info-value">
                                <strong class="text-success"><?php echo format_currency($payment['amount']); ?></strong>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">نوع پرداخت:</span>
                            <span class="info-value">
                                <?php if ($payment['payment_type'] == 'cash'): ?>
                                    <span class="badge bg-success">نقدی</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">چک</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">تاریخ پرداخت:</span>
                            <span class="info-value"><?php echo persian_date('full', strtotime($payment['payment_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">تاریخ ثبت:</span>
                            <span class="info-value"><?php echo persian_date('full', strtotime($payment['created_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Type Specific Info -->
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $payment['payment_type'] == 'cash' ? 'money-bill' : 'check'; ?>"></i>
                            جزئیات <?php echo $payment['payment_type'] == 'cash' ? 'پرداخت نقدی' : 'چک'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($payment['payment_type'] == 'cash'): ?>
                            <div class="info-row">
                                <span class="info-label">شناسه پرداخت:</span>
                                <span class="info-value"><?php echo $payment['payment_id'] ?: '-'; ?></span>
                            </div>
                        <?php else: ?>
                            <div class="info-row">
                                <span class="info-label">شماره چک:</span>
                                <span class="info-value"><?php echo $payment['check_number'] ?: '-'; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">تاریخ چک:</span>
                                <span class="info-value">
                                    <?php echo $payment['check_date'] ? persian_date('full', strtotime($payment['check_date'])) : '-'; ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">وضعیت وصول:</span>
                                <span class="info-value">
                                    <?php if ($payment['is_collected']): ?>
                                        <span class="badge bg-success">وصول شده</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">وصول نشده</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($payment['collection_date']): ?>
                            <div class="info-row">
                                <span class="info-label">تاریخ وصول:</span>
                                <span class="info-value"><?php echo persian_date('full', strtotime($payment['collection_date'])); ?></span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Admin Notes -->
                <?php if ($_SESSION['is_admin'] == 1 || $payment['admin_note_public']): ?>
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-comment"></i> یادداشت‌های مدیر</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($payment['admin_note_public']): ?>
                        <div class="alert alert-info">
                            <strong>یادداشت عمومی:</strong><br>
                            <?php echo nl2br($payment['admin_note_public']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['is_admin'] == 1 && $payment['admin_note_private']): ?>
                        <div class="alert alert-warning">
                            <strong>یادداشت خصوصی (فقط مدیر):</strong><br>
                            <?php echo nl2br($payment['admin_note_private']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$payment['admin_note_public'] && (!$_SESSION['is_admin'] || !$payment['admin_note_private'])): ?>
                        <p class="text-muted">یادداشتی ثبت نشده است.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Status and Image -->
            <div class="col-md-4">
                <!-- Status Card -->
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> وضعیت</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if ($payment['is_approved']): ?>
                            <div class="status-badge bg-success text-white mb-3">
                                <i class="fas fa-check-circle"></i>
                                تایید شده
                            </div>
                            <p class="text-success">این پرداخت توسط مدیر تایید شده است.</p>
                        <?php else: ?>
                            <div class="status-badge bg-warning text-dark mb-3">
                                <i class="fas fa-clock"></i>
                                در انتظار تایید
                            </div>
                            <p class="text-warning">این پرداخت در انتظار بررسی و تایید مدیر می‌باشد.</p>
                        <?php endif; ?>

                        <?php if ($payment['payment_type'] == 'check'): ?>
                            <hr>
                            <?php if ($payment['is_collected']): ?>
                                <div class="status-badge bg-info text-white mb-2">
                                    <i class="fas fa-university"></i>
                                    چک وصول شده
                                </div>
                            <?php else: ?>
                                <div class="status-badge bg-secondary text-white mb-2">
                                    <i class="fas fa-hourglass-half"></i>
                                    چک وصول نشده
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Image -->
                <?php if ($payment['image_path'] && file_exists($payment['image_path'])): ?>
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-image"></i> 
                            تصویر <?php echo $payment['payment_type'] == 'cash' ? 'سند پرداخت' : 'چک'; ?>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="image-container">
                            <?php
                            $file_extension = strtolower(pathinfo($payment['image_path'], PATHINFO_EXTENSION));
                            if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])):
                            ?>
                                <img src="<?php echo $payment['image_path']; ?>" alt="تصویر سند پرداخت" class="img-fluid">
                                <div class="p-3">
                                    <a href="<?php echo $payment['image_path']; ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> مشاهده در اندازه کامل
                                    </a>
                                    <a href="<?php echo $payment['image_path']; ?>" download class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download"></i> دانلود
                                    </a>
                                </div>
                            <?php elseif ($file_extension == 'pdf'): ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                                    <p>فایل PDF</p>
                                    <a href="<?php echo $payment['image_path']; ?>" target="_blank" class="btn btn-outline-danger">
                                        <i class="fas fa-external-link-alt"></i> مشاهده PDF
                                    </a>
                                    <a href="<?php echo $payment['image_path']; ?>" download class="btn btn-outline-success">
                                        <i class="fas fa-download"></i> دانلود
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-image"></i> تصویر سند</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">تصویری آپلود نشده است</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Points Calculation -->
                <div class="card info-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> محاسبه امتیاز</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if ($payment['is_approved']) {
                            $start_date = $payment['payment_date'];
                            if ($payment['payment_type'] == 'check') {
                                if ($payment['is_collected'] && $payment['collection_date']) {
                                    $start_date = $payment['collection_date'];
                                } elseif (!$payment['is_collected']) {
                                    $start_date = null;
                                }
                            }
                            
                            if ($start_date) {
                                $points = calculate_points($payment['amount'], $start_date);
                                $days = (time() - strtotime($start_date)) / (60 * 60 * 24);
                            }
                        }
                        ?>
                        
                        <div class="info-row">
                            <span class="info-label">مبلغ:</span>
                            <span class="info-value"><?php echo format_currency($payment['amount']); ?></span>
                        </div>
                        
                        <?php if ($payment['is_approved']): ?>
                            <?php if (isset($start_date) && $start_date): ?>
                                <div class="info-row">
                                    <span class="info-label">تاریخ شروع امتیاز:</span>
                                    <span class="info-value"><?php echo persian_date('full', strtotime($start_date)); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">تعداد روز:</span>
                                    <span class="info-value"><?php echo persian_number(floor($days)); ?> روز</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">امتیاز کسب شده:</span>
                                    <span class="info-value">
                                        <strong class="text-primary"><?php echo persian_number(number_format($points, 2)); ?></strong>
                                    </span>
                                </div>
                            <?php elseif ($payment['payment_type'] == 'check' && !$payment['is_collected']): ?>
                                <div class="alert alert-warning">
                                    <small>امتیاز پس از وصول چک محاسبه خواهد شد</small>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <small>امتیاز پس از تایید پرداخت محاسبه خواهد شد</small>
                            </div>
                        <?php endif; ?>
                        
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            هر ۱۰۰,۰۰۰ تومان در روز = ۱ امتیاز
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> چاپ
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> بستن
                </button>
                <?php if ($_SESSION['is_admin'] == 1): ?>
                <a href="admin.php?page=payments" class="btn btn-info">
                    <i class="fas fa-arrow-left"></i> بازگشت به لیست پرداخت‌ها
                </a>
                <?php else: ?>
                <a href="member.php?page=payments" class="btn btn-info">
                    <i class="fas fa-arrow-left"></i> بازگشت به سوابق پرداخت
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Print Button (Fixed) -->
    <button onclick="window.print()" class="btn btn-primary btn-lg print-btn no-print">
        <i class="fas fa-print"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close after print (optional)
        window.addEventListener('afterprint', function() {
            // Uncomment the next line if you want the window to close after printing
            // window.close();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Escape to close
            if (e.key === 'Escape') {
                window.close();
            }
        });

        // Image zoom functionality
        document.querySelectorAll('.image-container img').forEach(function(img) {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function() {
                if (this.style.transform === 'scale(2)') {
                    this.style.transform = 'scale(1)';
                    this.style.cursor = 'zoom-in';
                } else {
                    this.style.transform = 'scale(2)';
                    this.style.cursor = 'zoom-out';
                }
            });
        });

        // Tooltip initialization (if needed)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>