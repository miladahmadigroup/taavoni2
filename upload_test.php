<?php
// Test upload functionality
echo "<h2>تست آپلود فایل</h2>";

// Display PHP upload settings
echo "<h3>تنظیمات PHP:</h3>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . " seconds<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

// Check uploads directory
$upload_dir = 'uploads/payments/';
echo "<h3>بررسی پوشه آپلود:</h3>";
echo "مسیر: " . realpath($upload_dir) . "<br>";
echo "وجود دارد: " . (file_exists($upload_dir) ? 'YES' : 'NO') . "<br>";
echo "قابل نوشتن: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "<br>";

// Create directory if not exists
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "پوشه ایجاد شد!<br>";
    } else {
        echo "<span style='color: red;'>خطا در ایجاد پوشه!</span><br>";
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    echo "<h3>نتیجه آپلود:</h3>";
    
    $file = $_FILES['test_file'];
    
    echo "نام فایل: " . $file['name'] . "<br>";
    echo "نوع فایل: " . $file['type'] . "<br>";
    echo "حجم فایل: " . $file['size'] . " bytes<br>";
    echo "فایل موقت: " . $file['tmp_name'] . "<br>";
    echo "کد خطا: " . $file['error'] . "<br>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $target_file = $upload_dir . basename($file['name']);
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            echo "<span style='color: green;'>✅ آپلود موفقیت‌آمیز به: " . $target_file . "</span><br>";
            
            // Show uploaded file
            if (file_exists($target_file)) {
                echo "اندازه فایل نهایی: " . filesize($target_file) . " bytes<br>";
                
                if (in_array(strtolower(pathinfo($target_file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo "<img src='$target_file' style='max-width: 200px; max-height: 200px; border: 1px solid #ccc;'><br>";
                }
            }
        } else {
            echo "<span style='color: red;'>❌ خطا در انتقال فایل!</span><br>";
        }
    } else {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'فایل بیشتر از upload_max_filesize است',
            UPLOAD_ERR_FORM_SIZE => 'فایل بیشتر از MAX_FILE_SIZE است',
            UPLOAD_ERR_PARTIAL => 'فایل به صورت جزئی آپلود شد',
            UPLOAD_ERR_NO_FILE => 'هیچ فایلی آپلود نشد',
            UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت وجود ندارد',
            UPLOAD_ERR_CANT_WRITE => 'نوشتن فایل امکان‌پذیر نیست',
            UPLOAD_ERR_EXTENSION => 'افزونه PHP آپلود را متوقف کرد'
        ];
        
        echo "<span style='color: red;'>❌ خطا: " . ($errors[$file['error']] ?? 'خطای نامشخص') . "</span><br>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <h3>تست آپلود:</h3>
    <input type="file" name="test_file" accept="image/*,.pdf">
    <button type="submit">آپلود</button>
</form>

<hr>
<a href="admin.php">بازگشت به پنل ادمین</a>