<?php
session_start();
require_once 'config.php';

// این فایل فقط برای تست و debug است
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die('دسترسی مجاز نیست');
}

$conn = connectDB();

echo "<h2>بررسی وضعیت پرداخت‌ها</h2>";

// Check last 10 payments
echo "<h3>آخرین 10 پرداخت:</h3>";
$query = "SELECT p.*, m.full_name as member_name, pr.name as project_name 
          FROM payments p 
          JOIN members m ON p.member_id = m.id 
          JOIN projects pr ON p.project_id = pr.id 
          WHERE pr.cooperative_id = '{$_SESSION['cooperative_id']}' 
          ORDER BY p.id DESC LIMIT 10";
          
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>هیچ پرداختی در دیتابیس یافت نشد!</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>عضو</th><th>پروژه</th><th>مبلغ</th><th>نوع</th><th>تاریخ ثبت</th><th>تایید شده</th></tr>";
    
    while ($payment = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $payment['id'] . "</td>";
        echo "<td>" . $payment['member_name'] . "</td>";
        echo "<td>" . $payment['project_name'] . "</td>";
        echo "<td>" . number_format($payment['amount']) . "</td>";
        echo "<td>" . $payment['payment_type'] . "</td>";
        echo "<td>" . $payment['created_at'] . "</td>";
        echo "<td>" . ($payment['is_approved'] ? 'بله' : 'خیر') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check cooperative info
echo "<h3>اطلاعات تعاونی شما:</h3>";
echo "Cooperative ID: " . $_SESSION['cooperative_id'] . "<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "نام کامل: " . $_SESSION['full_name'] . "<br>";

// Check members
echo "<h3>اعضای تعاونی:</h3>";
$query = "SELECT * FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);
echo "تعداد اعضا: " . mysqli_num_rows($result) . "<br>";

// Check projects
echo "<h3>پروژه‌های تعاونی:</h3>";
$query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
$result = mysqli_query($conn, $query);
echo "تعداد پروژه‌ها: " . mysqli_num_rows($result) . "<br>";

if (mysqli_num_rows($result) > 0) {
    while ($project = mysqli_fetch_assoc($result)) {
        echo "- " . $project['name'] . " (ID: " . $project['id'] . ")<br>";
    }
}

// Test insert payment
if (isset($_POST['test_insert'])) {
    echo "<h3>تست درج پرداخت:</h3>";
    
    $member_id = $_POST['member_id'];
    $project_id = $_POST['project_id'];
    $amount = 1000000;
    $payment_type = 'cash';
    $payment_date = date('Y-m-d');
    
    $query = "INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, is_approved, admin_note_public) 
              VALUES ('$member_id', '$project_id', '$amount', '$payment_type', '$payment_date', 1, 'تست توسط مدیر')";
              
    if (mysqli_query($conn, $query)) {
        $payment_id = mysqli_insert_id($conn);
        echo "<p style='color: green;'>پرداخت تست با ID $payment_id ثبت شد!</p>";
    } else {
        echo "<p style='color: red;'>خطا در ثبت: " . mysqli_error($conn) . "</p>";
        echo "<p>Query: $query</p>";
    }
}

mysqli_close($conn);
?>

<form method="POST">
    <h3>تست درج پرداخت جدید:</h3>
    <label>عضو:</label>
    <select name="member_id" required>
        <?php
        $conn = connectDB();
        $query = "SELECT * FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
        $result = mysqli_query($conn, $query);
        while ($member = mysqli_fetch_assoc($result)) {
            echo "<option value='{$member['id']}'>{$member['full_name']}</option>";
        }
        ?>
    </select><br><br>
    
    <label>پروژه:</label>
    <select name="project_id" required>
        <?php
        $query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
        $result = mysqli_query($conn, $query);
        while ($project = mysqli_fetch_assoc($result)) {
            echo "<option value='{$project['id']}'>{$project['name']}</option>";
        }
        mysqli_close($conn);
        ?>
    </select><br><br>
    
    <button type="submit" name="test_insert">درج پرداخت تست</button>
</form>

<hr>
<a href="admin.php?page=payments">بازگشت به مدیریت پرداخت‌ها</a>