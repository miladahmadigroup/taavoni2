<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'housing_coop');

// Connect to database using mysqli
function connectDB() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        die('اتصال به دیتابیس برقرار نشد: ' . mysqli_connect_error());
    }
    mysqli_query($conn, "SET NAMES utf8");
    return $conn;
}

// Persian date functions
function persian_date($format = 'Y/m/d', $timestamp = null) {
    if ($timestamp === null) $timestamp = time();
    
    $months = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
               'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    
    $weekdays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'];
    
    $g_y = date('Y', $timestamp);
    $g_m = date('n', $timestamp);
    $g_d = date('j', $timestamp);
    
    // Simple Gregorian to Persian conversion
    $p_y = $g_y - 621;
    if ($g_m > 3 || ($g_m == 3 && $g_d >= 21)) {
        $p_y++;
    }
    
    // Approximate month conversion
    if ($g_m >= 3 && $g_m <= 5) $p_m = $g_m - 2;
    elseif ($g_m >= 6 && $g_m <= 8) $p_m = $g_m - 2;
    elseif ($g_m >= 9 && $g_m <= 11) $p_m = $g_m - 2;
    else $p_m = ($g_m + 10) % 12;
    if ($p_m == 0) $p_m = 12;
    
    $p_d = $g_d;
    
    if ($format == 'Y/m/d') {
        return $p_y . '/' . sprintf('%02d', $p_m) . '/' . sprintf('%02d', $p_d);
    } elseif ($format == 'Y-m-d') {
        return $p_y . '-' . sprintf('%02d', $p_m) . '-' . sprintf('%02d', $p_d);
    } elseif ($format == 'full') {
        return $p_d . ' ' . $months[$p_m] . ' ' . $p_y;
    }
    
    return $p_d . ' ' . $months[$p_m] . ' ' . $p_y;
}

// Convert Persian date to Gregorian (simplified)
function persian_to_gregorian($persian_date) {
    $parts = explode('/', $persian_date);
    if (count($parts) != 3) return date('Y-m-d');
    
    $p_y = intval($parts[0]);
    $p_m = intval($parts[1]);
    $p_d = intval($parts[2]);
    
    // Simple conversion
    $g_y = $p_y + 621;
    if ($p_m <= 6) {
        $g_m = $p_m + 3;
    } else {
        $g_m = $p_m - 9;
        $g_y++;
    }
    
    return $g_y . '-' . sprintf('%02d', $g_m) . '-' . sprintf('%02d', $p_d);
}

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function check_login() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        header('Location: index.php?page=login');
        exit();
    }
}

function check_admin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: index.php?page=member');
        exit();
    }
}

// Calculate points (100,000 Toman = 1 point per day)
function calculate_points($amount, $start_date, $end_date = null) {
    if ($end_date === null) $end_date = date('Y-m-d');
    
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $days = ($end - $start) / (60 * 60 * 24);
    
    $points_per_day = $amount / 100000; // 100,000 Toman = 1 point per day
    return $points_per_day * $days;
}

// File upload function - Enhanced
function upload_file($file, $target_dir = 'uploads/payments/') {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            error_log("Failed to create directory: $target_dir");
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($target_dir)) {
        error_log("Directory not writable: $target_dir");
        return false;
    }
    
    // Check if file was uploaded without errors
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        error_log("Upload error: " . ($file['error'] ?? 'No file'));
        return false;
    }
    
    // Get file extension
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png", "pdf", "gif");
    
    // Check file extension
    if (!in_array($file_extension, $allowed_extensions)) {
        error_log("File extension not allowed: $file_extension");
        return false;
    }
    
    // Check file size (5MB maximum)
    if ($file['size'] > 5242880) { // 5MB
        error_log("File too large: " . $file['size']);
        return false;
    }
    
    // Generate unique filename
    $new_filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Verify file was actually moved
        if (file_exists($target_file)) {
            error_log("File uploaded successfully: $target_file");
            return $target_file;
        } else {
            error_log("File moved but not found: $target_file");
            return false;
        }
    } else {
        error_log("Failed to move uploaded file to: $target_file");
        return false;
    }
}

// Format number to Persian
function persian_number($number) {
    // Handle string numbers that are already formatted
    if (is_string($number)) {
        // Remove any existing formatting
        $number = str_replace([',', ' ', 'تومان'], '', $number);
        // Convert to float if possible
        if (is_numeric($number)) {
            $number = floatval($number);
        } else {
            return $number; // Return as is if not numeric
        }
    }
    
    $persian_digits = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    $english_digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($english_digits, $persian_digits, number_format($number));
}

// Format currency
function format_currency($amount) {
    return persian_number($amount) . ' تومان';
}

// Get member projects
function get_member_projects($member_id) {
    $conn = connectDB();
    $query = "SELECT p.*, mp.joined_at FROM projects p 
              JOIN member_projects mp ON p.id = mp.project_id 
              WHERE mp.member_id = '$member_id'";
    $result = mysqli_query($conn, $query);
    $projects = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $projects[] = $row;
    }
    mysqli_close($conn);
    return $projects;
}

// Get member payments for a project
function get_member_payments($member_id, $project_id = null) {
    $conn = connectDB();
    $where_clause = "WHERE p.member_id = '$member_id'";
    if ($project_id) {
        $where_clause .= " AND p.project_id = '$project_id'";
    }
    
    $query = "SELECT p.*, pr.name as project_name FROM payments p 
              JOIN projects pr ON p.project_id = pr.id 
              $where_clause ORDER BY p.created_at DESC";
    $result = mysqli_query($conn, $query);
    $payments = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_close($conn);
    return $payments;
}

// Get member total points for a project
function get_member_points($member_id, $project_id) {
    $conn = connectDB();
    $query = "SELECT * FROM payments WHERE member_id = '$member_id' 
              AND project_id = '$project_id' AND is_approved = 1";
    $result = mysqli_query($conn, $query);
    
    $total_points = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $start_date = $row['payment_date'];
        if ($row['payment_type'] == 'check' && $row['is_collected'] == 1) {
            $start_date = $row['collection_date'];
        } elseif ($row['payment_type'] == 'check' && $row['is_collected'] == 0) {
            continue; // Skip uncollected checks
        }
        
        $total_points += calculate_points($row['amount'], $start_date);
    }
    
    mysqli_close($conn);
    return $total_points;
}

// Get member rank in project
function get_member_rank($member_id, $project_id) {
    $conn = connectDB();
    
    // Get all members in this project with their points
    $query = "SELECT DISTINCT mp.member_id FROM member_projects mp 
              WHERE mp.project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    
    $members_points = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $points = get_member_points($row['member_id'], $project_id);
        $members_points[$row['member_id']] = $points;
    }
    
    // Sort by points (descending)
    arsort($members_points);
    
    $rank = 1;
    foreach ($members_points as $mid => $points) {
        if ($mid == $member_id) {
            return $rank;
        }
        $rank++;
    }
    
    mysqli_close($conn);
    return 0;
}

// Get project statistics
function get_project_stats($project_id) {
    $conn = connectDB();
    
    $stats = array();
    
    // Total members
    $query = "SELECT COUNT(*) as count FROM member_projects WHERE project_id = '$project_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_members'] = $row['count'];
    
    // Total payments
    $query = "SELECT SUM(amount) as total FROM payments WHERE project_id = '$project_id' AND is_approved = 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['total_payments'] = $row['total'] ?: 0;
    
    // Pending payments
    $query = "SELECT COUNT(*) as count FROM payments WHERE project_id = '$project_id' AND is_approved = 0";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $stats['pending_payments'] = $row['count'];
    
    mysqli_close($conn);
    return $stats;
}

// Create database tables
function create_tables() {
    $conn = connectDB();
    
    $queries = [
        "CREATE TABLE IF NOT EXISTS cooperatives (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS projects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            cooperative_id INT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            min_payment DECIMAL(15,2) DEFAULT 0,
            payment_deadline DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS members (
            id INT PRIMARY KEY AUTO_INCREMENT,
            cooperative_id INT,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            email VARCHAR(100),
            is_active TINYINT DEFAULT 1,
            is_admin TINYINT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS member_projects (
            id INT PRIMARY KEY AUTO_INCREMENT,
            member_id INT,
            project_id INT,
            joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            member_id INT,
            project_id INT,
            amount DECIMAL(15,2) NOT NULL,
            payment_type ENUM('cash', 'check') NOT NULL,
            payment_date DATE NOT NULL,
            payment_id VARCHAR(100),
            check_number VARCHAR(100),
            check_date DATE,
            image_path VARCHAR(255),
            is_approved TINYINT DEFAULT 0,
            is_collected TINYINT DEFAULT 0,
            collection_date DATE,
            admin_note_public TEXT,
            admin_note_private TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            project_id INT,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS settings (
            id INT PRIMARY KEY AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT,
            UNIQUE KEY unique_key (setting_key)
        )"
    ];
    
    foreach ($queries as $query) {
        if (!mysqli_query($conn, $query)) {
            die('Error creating table: ' . mysqli_error($conn));
        }
    }
    
    // Insert default settings
    $default_message = mysqli_real_escape_string($conn, "سند پرداخت شما با موفقیت ثبت شد و در انتظار تایید مدیر می‌باشد.");
    $insert_setting = "INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('payment_success_message', '$default_message')";
    mysqli_query($conn, $insert_setting);
    
    mysqli_close($conn);
}

// Get setting value
function get_setting($key, $default = '') {
    $conn = connectDB();
    $key = mysqli_real_escape_string($conn, $key);
    $query = "SELECT setting_value FROM settings WHERE setting_key = '$key'";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_close($conn);
        return $row['setting_value'];
    }
    mysqli_close($conn);
    return $default;
}

// Set setting value
function set_setting($key, $value) {
    $conn = connectDB();
    $key = mysqli_real_escape_string($conn, $key);
    $value = mysqli_real_escape_string($conn, $value);
    $query = "INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value') 
              ON DUPLICATE KEY UPDATE setting_value = '$value'";
    $result = mysqli_query($conn, $query);
    mysqli_close($conn);
    return $result;
}
?>