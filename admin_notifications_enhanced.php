function deleteNotification(notificationId) {
    <?php
// Handle notification operations
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $notification_id = intval($_GET['id']);
    
    if ($action === 'delete') {
        // First check if notification belongs to this cooperative
        $check_query = "SELECT n.id FROM notifications n 
                        JOIN projects p ON n.project_id = p.id 
                        WHERE n.id = '$notification_id' AND p.cooperative_id = '{$_SESSION['cooperative_id']}'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $query = "DELETE FROM notifications WHERE id = '$notification_id'";
            if (mysqli_query($conn, $query)) {
                $success_message = "اطلاعیه حذف شد!";
            } else {
                $error_message = "خطا در حذف اطلاعیه: " . mysqli_error($conn);
            }
        } else {
            $error_message = "اطلاعیه یافت نشد یا دسترسی ندارید!";
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">مدیریت اطلاعیه‌ها</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNotificationModal">
        <i class="fas fa-plus"></i> اطلاعیه جدید
    </button>
</div>

<!-- Notifications Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>عنوان</th>
                <th>پروژه</th>
                <th>محتوا</th>
                <th>تاریخ ثبت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT n.*, p.name as project_name FROM notifications n 
                      JOIN projects p ON n.project_id = p.id 
                      WHERE p.cooperative_id = '{$_SESSION['cooperative_id']}' 
                      ORDER BY n.created_at DESC";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) == 0) {
                echo '<tr><td colspan="5" class="text-center">هیچ اطلاعیه‌ای ثبت نشده است</td></tr>';
            } else {
                while ($notification = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?php echo $notification['title']; ?></td>
                <td><?php echo $notification['project_name']; ?></td>
                <td><?php echo substr($notification['content'], 0, 100) . '...'; ?></td>
                <td><?php echo persian_date('Y/m/d', strtotime($notification['created_at'])); ?></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewNotification(<?php echo $notification['id']; ?>)" title="مشاهده جزئیات">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editNotification(<?php echo $notification['id']; ?>)" title="ویرایش اطلاعیه">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="?page=notifications&action=delete&id=<?php echo $notification['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('آیا از حذف این اطلاعیه مطمئن هستید؟')" 
                       title="حذف اطلاعیه">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php 
                endwhile;
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Notification Modal -->
<div class="modal fade" id="addNotificationModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">اطلاعیه جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">عنوان:</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">انتخاب پروژه(ها):</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px;">
                            <?php
                            $query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
                            $result = mysqli_query($conn, $query);
                            while ($project = mysqli_fetch_assoc($result)):
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="project_ids[]" value="<?php echo $project['id']; ?>" id="project_<?php echo $project['id']; ?>">
                                <label class="form-check-label" for="project_<?php echo $project['id']; ?>">
                                    <?php echo $project['name']; ?>
                                </label>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">محتوا:</label>
                        <textarea class="form-control" name="content" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_notification" class="btn btn-primary">ثبت اطلاعیه</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Notification Modal -->
<div class="modal fade" id="editNotificationModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش اطلاعیه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="notification_id" id="editNotificationId">
                    <div class="mb-3">
                        <label class="form-label">عنوان:</label>
                        <input type="text" class="form-control" name="title" id="editNotificationTitle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">محتوا:</label>
                        <textarea class="form-control" name="content" id="editNotificationContent" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="edit_notification" class="btn btn-primary">بروزرسانی</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جزئیات اطلاعیه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewNotificationContent">
                <!-- Notification details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewNotification(notificationId) {
    // Show loading first
    document.getElementById('viewNotificationContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> در حال بارگیری...</div>';
    var modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
    modal.show();
    
    fetch('get_notification_details.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('viewNotificationContent').innerHTML = `
                    <div class="card">
                        <div class="card-header">
                            <h6>${data.notification.title}</h6>
                            <small class="text-muted">پروژه: ${data.notification.project_name}</small>
                        </div>
                        <div class="card-body">
                            <p style="white-space: pre-wrap;">${data.notification.content}</p>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> تاریخ ثبت: ${data.notification.created_at_persian}
                            </small>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('viewNotificationContent').innerHTML = 
                    '<div class="alert alert-danger">خطا در بارگیری اطلاعات اطلاعیه: ' + (data.error || 'خطای نامشخص') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('viewNotificationContent').innerHTML = 
                '<div class="alert alert-danger">خطا در اتصال به سرور</div>';
        });
}

function editNotification(notificationId) {
    fetch('get_notification_details.php?id=' + notificationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editNotificationId').value = notificationId;
                document.getElementById('editNotificationTitle').value = data.notification.title;
                document.getElementById('editNotificationContent').value = data.notification.content;
                
                var modal = new bootstrap.Modal(document.getElementById('editNotificationModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات اطلاعیه: ' + (data.error || 'خطای نامشخص'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در اتصال به سرور');
        });
}

function deleteNotification(notificationId) {
    // این function حذف شد - حالا از href مستقیم استفاده می‌کنیم
}
</script>