<?php
// Simple file uploader for payment documents
function uploadPaymentFile($file) {
    // Check if file was uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/payments/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Get file info
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Get file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Allowed extensions
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'pdf', 'gif');
    
    // Check extension
    if (!in_array($file_ext, $allowed_extensions)) {
        return false;
    }
    
    // Check file size (max 5MB)
    if ($file_size > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $new_filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        return $upload_path;
    }
    
    return false;
}

// Handle AJAX upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    header('Content-Type: application/json');
    
    $upload_result = uploadPaymentFile($_FILES['file']);
    
    if ($upload_result) {
        echo json_encode([
            'success' => true,
            'file_path' => $upload_result,
            'message' => 'فایل با موفقیت آپلود شد'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در آپلود فایل'
        ]);
    }
    exit;
}
?>