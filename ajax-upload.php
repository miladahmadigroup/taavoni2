<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log upload attempts
error_log("Upload attempt started at " . date('Y-m-d H:i:s'));

$response = array();

// Create upload directory if it doesn't exist
$target_dir = "uploads/payments/";
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        $response["status"] = "error";
        $response["message"] = "نمی‌توان پوشه آپلود را ایجاد کرد";
        error_log("Failed to create upload directory: $target_dir");
        echo json_encode($response);
        exit;
    }
    error_log("Created upload directory: $target_dir");
}

// Check if directory is writable
if (!is_writable($target_dir)) {
    $response["status"] = "error";
    $response["message"] = "پوشه آپلود قابل نوشتن نیست";
    error_log("Upload directory not writable: $target_dir");
    echo json_encode($response);
    exit;
}

if (!isset($_FILES["file"])) {
    $response["status"] = "error";
    $response["message"] = "هیچ فایلی دریافت نشد";
    error_log("No file received in upload request");
    echo json_encode($response);
    exit;
}

$file = $_FILES["file"];
error_log("File received: " . print_r($file, true));

// Check for upload errors
if ($file["error"] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'فایل بیشتر از upload_max_filesize است',
        UPLOAD_ERR_FORM_SIZE => 'فایل بیشتر از MAX_FILE_SIZE است',
        UPLOAD_ERR_PARTIAL => 'فایل به صورت جزئی آپلود شد',
        UPLOAD_ERR_NO_FILE => 'هیچ فایلی آپلود نشد',
        UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت وجود ندارد',
        UPLOAD_ERR_CANT_WRITE => 'نوشتن فایل امکان‌پذیر نیست',
        UPLOAD_ERR_EXTENSION => 'افزونه PHP آپلود را متوقف کرد'
    ];
    
    $response["status"] = "error";
    $response["message"] = $errors[$file["error"]] ?? "خطای نامشخص در آپلود";
    error_log("Upload error: " . $file["error"] . " - " . $response["message"]);
    echo json_encode($response);
    exit;
}

// File info
$fileName = $file["name"];
$fileSize = $file["size"];
$fileTempName = $file["tmp_name"];
$fileType = $file["type"];

error_log("Processing file: $fileName, Size: $fileSize, Type: $fileType, Temp: $fileTempName");

// Check if temp file exists
if (!file_exists($fileTempName)) {
    $response["status"] = "error";
    $response["message"] = "فایل موقت یافت نشد";
    error_log("Temp file not found: $fileTempName");
    echo json_encode($response);
    exit;
}

// Get file extension
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
error_log("File extension: $fileExtension");

// Allowed file extensions
$allowedExtensions = array("jpg", "jpeg", "png", "gif", "pdf");

// Check if file extension is allowed
if (!in_array($fileExtension, $allowedExtensions)) {
    $response["status"] = "error";
    $response["message"] = "فرمت فایل مجاز نیست. فقط " . implode(', ', $allowedExtensions) . " مجاز هستند.";
    error_log("File extension not allowed: $fileExtension");
    echo json_encode($response);
    exit;
}

// Check file size (5MB maximum)
if ($fileSize > 5242880) { // 5MB in bytes
    $response["status"] = "error";
    $response["message"] = "حجم فایل نباید بیشتر از 5MB باشد.";
    error_log("File too large: $fileSize bytes");
    echo json_encode($response);
    exit;
}

// Generate unique filename
$newFileName = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $fileExtension;
$targetFilePath = $target_dir . $newFileName;

error_log("Target file path: $targetFilePath");

// Move uploaded file to destination
if (move_uploaded_file($fileTempName, $targetFilePath)) {
    // Verify file was actually moved
    if (file_exists($targetFilePath)) {
        $actualFileSize = filesize($targetFilePath);
        error_log("File successfully uploaded: $targetFilePath, Size: $actualFileSize bytes");
        
        $response["status"] = "success";
        $response["message"] = "فایل با موفقیت آپلود شد";
        $response["file_path"] = $targetFilePath;
        $response["file_name"] = $newFileName;
        $response["file_size"] = formatBytes($actualFileSize);
        $response["original_name"] = $fileName;
    } else {
        $response["status"] = "error";
        $response["message"] = "فایل آپلود شد ولی در مقصد یافت نشد";
        error_log("File moved but not found at destination: $targetFilePath");
    }
} else {
    $response["status"] = "error";
    $response["message"] = "خطا در انتقال فایل به مقصد";
    error_log("Failed to move uploaded file from $fileTempName to $targetFilePath");
    
    // Additional debugging
    error_log("Source file exists: " . (file_exists($fileTempName) ? 'YES' : 'NO'));
    error_log("Target directory exists: " . (file_exists($target_dir) ? 'YES' : 'NO'));
    error_log("Target directory writable: " . (is_writable($target_dir) ? 'YES' : 'NO'));
}

echo json_encode($response);

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?>