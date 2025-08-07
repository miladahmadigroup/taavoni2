<?php
// Handle project operations
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $project_id = intval($_GET['id']);
    
    if ($action === 'delete') {
        $query = "DELETE FROM projects WHERE id = '$project_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('پروژه حذف شد!'); window.location.href='?page=projects';</script>";
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">مدیریت پروژه‌ها</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProjectModal">
        <i class="fas fa-plus"></i> پروژه جدید
    </button>
</div>

<!-- Projects Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>نام پروژه</th>
                <th>توضیحات</th>
                <th>حداقل پرداخت</th>
                <th>مهلت پرداخت</th>
                <th>تعداد اعضا</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}' ORDER BY created_at DESC";
            $result = mysqli_query($conn, $query);
            while ($project = mysqli_fetch_assoc($result)):
                $project_stats = get_project_stats($project['id']);
            ?>
            <tr>
                <td><?php echo $project['name']; ?></td>
                <td><?php echo substr($project['description'], 0, 50) . '...'; ?></td>
                <td><?php echo format_currency($project['min_payment']); ?></td>
                <td><?php echo $project['payment_deadline'] ? persian_date('Y/m/d', strtotime($project['payment_deadline'])) : 'تعیین نشده'; ?></td>
                <td><?php echo persian_number($project_stats['total_members']); ?></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewProject(<?php echo $project['id']; ?>)" title="مشاهده جزئیات">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editProject(<?php echo $project['id']; ?>)" title="ویرایش پروژه">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProject(<?php echo $project['id']; ?>)" title="حذف پروژه">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Project Modal -->
<div class="modal fade" id="addProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">پروژه جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام پروژه:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات:</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">حداقل پرداخت (تومان):</label>
                        <input type="number" class="form-control" name="min_payment" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مهلت پرداخت:</label>
                        <div id="add_deadline_picker" data-date-picker="payment_deadline"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_project" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Project Modal -->
<div class="modal fade" id="editProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش پروژه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="project_id" id="editProjectId">
                    <div class="mb-3">
                        <label class="form-label">نام پروژه:</label>
                        <input type="text" class="form-control" name="name" id="editProjectName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات:</label>
                        <textarea class="form-control" name="description" id="editProjectDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">حداقل پرداخت (تومان):</label>
                        <input type="number" class="form-control" name="min_payment" id="editProjectMinPayment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">مهلت پرداخت:</label>
                        <div id="edit_deadline_picker" data-date-picker="payment_deadline"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="edit_project" class="btn btn-primary">بروزرسانی</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Project Details Modal -->
<div class="modal fade" id="projectDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جزئیات پروژه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="projectDetailsContent">
                <!-- Project details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewProject(projectId) {
    // Show loading first
    document.getElementById('projectDetailsContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> در حال بارگیری...</div>';
    var modal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
    modal.show();
    
    // Load project details via AJAX
    fetch('get_project_details.php?id=' + projectId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('projectDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>اطلاعات پروژه</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>نام پروژه:</strong></td><td>${data.project.name}</td></tr>
                                <tr><td><strong>توضیحات:</strong></td><td>${data.project.description || 'ندارد'}</td></tr>
                                <tr><td><strong>حداقل پرداخت:</strong></td><td>${data.project.min_payment} تومان</td></tr>
                                <tr><td><strong>مهلت پرداخت:</strong></td><td>${data.project.payment_deadline_persian || 'تعیین نشده'}</td></tr>
                                <tr><td><strong>تعداد اعضا:</strong></td><td>${data.stats.total_members} نفر</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>آمار پروژه</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>کل پرداخت‌های تایید شده:</strong></td><td>${data.stats.approved_payments}</td></tr>
                                <tr><td><strong>پرداخت‌های در انتظار:</strong></td><td>${data.stats.pending_payments} مورد</td></tr>
                                <tr><td><strong>چک‌های وصول نشده:</strong></td><td>${data.stats.uncollected_checks} مورد</td></tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('projectDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">خطا در بارگیری اطلاعات پروژه: ' + (data.error || 'خطای نامشخص') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('projectDetailsContent').innerHTML = 
                '<div class="alert alert-danger">خطا در اتصال به سرور</div>';
        });
}

function editProject(projectId) {
    // Load project data via AJAX
    fetch('get_project_details.php?id=' + projectId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editProjectId').value = projectId;
                document.getElementById('editProjectName').value = data.project.name;
                document.getElementById('editProjectDescription').value = data.project.description || '';
                document.getElementById('editProjectMinPayment').value = data.project.min_payment;
                
                // Set date picker for deadline
                if (data.project.payment_deadline_persian) {
                    createPersianDatePicker('edit_deadline_picker', 'payment_deadline', data.project.payment_deadline_persian);
                } else {
                    createPersianDatePicker('edit_deadline_picker', 'payment_deadline', '');
                }
                
                var modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات پروژه: ' + (data.error || 'خطای نامشخص'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در اتصال به سرور');
        });
}

function deleteProject(projectId) {
    if (confirm('آیا از حذف این پروژه مطمئن هستید؟\nتمام پرداخت‌ها و اطلاعیه‌های مربوط به این پروژه نیز حذف خواهند شد!')) {
        window.location.href = '?page=projects&action=delete&id=' + projectId;
    }
}
</script>