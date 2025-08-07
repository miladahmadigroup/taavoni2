<?php
// Handle member operations
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $member_id = intval($_GET['id']);
    
    if ($action === 'delete' && $member_id != $_SESSION['user_id']) {
        $query = "DELETE FROM members WHERE id = '$member_id' AND cooperative_id = '{$_SESSION['cooperative_id']}'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('عضو حذف شد!'); window.location.href='?page=members';</script>";
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">مدیریت اعضا</h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
            <i class="fas fa-plus"></i> عضو جدید
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMemberToProjectModal">
            <i class="fas fa-user-plus"></i> افزودن به پروژه
        </button>
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addPaymentForMemberModal">
            <i class="fas fa-money-bill"></i> ثبت پرداخت برای عضو
        </button>
    </div>
</div>

<!-- Members Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>نام و نام خانوادگی</th>
                <th>نام کاربری</th>
                <th>تلفن</th>
                <th>ایمیل</th>
                <th>وضعیت</th>
                <th>تاریخ عضویت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}' ORDER BY created_at DESC";
            $result = mysqli_query($conn, $query);
            while ($member = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?php echo $member['full_name']; ?></td>
                <td><?php echo $member['username']; ?></td>
                <td><?php echo $member['phone']; ?></td>
                <td><?php echo $member['email']; ?></td>
                <td>
                    <?php if ($member['is_active']): ?>
                        <span class="badge bg-success">فعال</span>
                    <?php else: ?>
                        <span class="badge bg-danger">غیرفعال</span>
                    <?php endif; ?>
                </td>
                <td><?php echo persian_date('Y/m/d', strtotime($member['created_at'])); ?></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewMember(<?php echo $member['id']; ?>)" title="مشاهده جزئیات">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editMember(<?php echo $member['id']; ?>)" title="ویرایش عضو">
                        <i class="fas fa-edit"></i>
                    </button>
                    <?php if ($member['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                            <input type="hidden" name="new_status" value="<?php echo $member['is_active'] ? '0' : '1'; ?>">
                            <button type="submit" name="toggle_member" class="btn btn-sm <?php echo $member['is_active'] ? 'btn-secondary' : 'btn-success'; ?>" 
                                    onclick="return confirm('آیا مطمئن هستید؟')">
                                <?php echo $member['is_active'] ? 'غیرفعال' : 'فعال'; ?>
                            </button>
                        </form>
                        <button class="btn btn-sm btn-danger" onclick="deleteMember(<?php echo $member['id']; ?>)" title="حذف عضو">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">عضو جدید</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">نام و نام خانوادگی:</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نام کاربری:</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رمز عبور:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تلفن:</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ایمیل:</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_member" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش عضو</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="member_id" id="editMemberId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نام و نام خانوادگی:</label>
                                <input type="text" class="form-control" name="full_name" id="editMemberName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نام کاربری:</label>
                                <input type="text" class="form-control" name="username" id="editMemberUsername" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تلفن:</label>
                                <input type="text" class="form-control" name="phone" id="editMemberPhone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ایمیل:</label>
                                <input type="email" class="form-control" name="email" id="editMemberEmail">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رمز عبور جدید (اختیاری):</label>
                        <input type="password" class="form-control" name="password" placeholder="اگر می‌خواهید رمز را تغییر دهید وارد کنید">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">پروژه‌های عضو:</label>
                        <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ced4da; padding: 10px;">
                            <?php
                            $projects_query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
                            $projects_result = mysqli_query($conn, $projects_query);
                            while ($project = mysqli_fetch_assoc($projects_result)):
                            ?>
                            <div class="form-check">
                                <input class="form-check-input project-checkbox" type="checkbox" name="project_ids[]" value="<?php echo $project['id']; ?>" id="edit_project_<?php echo $project['id']; ?>">
                                <label class="form-check-label" for="edit_project_<?php echo $project['id']; ?>">
                                    <?php echo $project['name']; ?>
                                </label>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="edit_member" class="btn btn-primary">بروزرسانی</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Member to Project Modal -->
<div class="modal fade" id="addMemberToProjectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">افزودن عضو به پروژه</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">انتخاب عضو:</label>
                        <select class="form-control" name="member_id" required>
                            <option value="">انتخاب کنید...</option>
                            <?php
                            $query = "SELECT * FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}' AND is_active = 1";
                            $result = mysqli_query($conn, $query);
                            while ($member = mysqli_fetch_assoc($result)):
                            ?>
                            <option value="<?php echo $member['id']; ?>"><?php echo $member['full_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">انتخاب پروژه:</label>
                        <select class="form-control" name="project_id" required>
                            <option value="">انتخاب کنید...</option>
                            <?php
                            $query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
                            $result = mysqli_query($conn, $query);
                            while ($project = mysqli_fetch_assoc($result)):
                            ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_member_to_project" class="btn btn-primary">افزودن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Payment for Member Modal -->
<div class="modal fade" id="addPaymentForMemberModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="paymentForm">
                <div class="modal-header">
                    <h5 class="modal-title">ثبت پرداخت برای عضو</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">انتخاب عضو:</label>
                                <select class="form-control" name="member_id" required>
                                    <option value="">انتخاب کنید...</option>
                                    <?php
                                    $query = "SELECT * FROM members WHERE cooperative_id = '{$_SESSION['cooperative_id']}' AND is_active = 1";
                                    $result = mysqli_query($conn, $query);
                                    while ($member = mysqli_fetch_assoc($result)):
                                    ?>
                                    <option value="<?php echo $member['id']; ?>"><?php echo $member['full_name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">انتخاب پروژه:</label>
                                <select class="form-control" name="project_id" required>
                                    <option value="">انتخاب کنید...</option>
                                    <?php
                                    $query = "SELECT * FROM projects WHERE cooperative_id = '{$_SESSION['cooperative_id']}'";
                                    $result = mysqli_query($conn, $query);
                                    while ($project = mysqli_fetch_assoc($result)):
                                    ?>
                                    <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">مبلغ (تومان):</label>
                                <input type="number" class="form-control" name="amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نوع پرداخت:</label>
                                <select class="form-control" name="payment_type" id="adminPaymentType" required onchange="toggleAdminPaymentFields()">
                                    <option value="">انتخاب کنید...</option>
                                    <option value="cash">نقد</option>
                                    <option value="check">چک</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cash specific fields -->
                    <div id="adminCashFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">تاریخ پرداخت:</label>
                            <div id="admin_payment_date_picker" data-date-picker="payment_date" data-default-date="<?php echo persian_date('Y/m/d'); ?>"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شناسه پرداخت:</label>
                            <input type="text" class="form-control" name="payment_id_field" placeholder="کد پیگیری، شماره کارت و...">
                        </div>
                    </div>
                    
                    <!-- Check specific fields -->
                    <div id="adminCheckFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تاریخ سررسید چک:</label>
                                    <div id="admin_check_due_date_picker" data-date-picker="check_date" data-default-date="<?php echo persian_date('Y/m/d'); ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تصویر سند:</label>
                        <input type="file" class="form-control" name="payment_image" accept="image/*,.pdf">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">یادداشت:</label>
                        <textarea class="form-control" name="admin_note" rows="2" placeholder="یادداشت اختیاری..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="add_payment_for_member" class="btn btn-primary">ثبت پرداخت</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Member Details Modal -->
<div class="modal fade" id="memberDetailsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جزئیات عضو</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="memberDetailsContent">
                <!-- Member details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewMember(memberId) {
    // Show loading first
    document.getElementById('memberDetailsContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> در حال بارگیری...</div>';
    var modal = new bootstrap.Modal(document.getElementById('memberDetailsModal'));
    modal.show();
    
    fetch('get_member_details.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let projectsList = '';
                if (data.projects.length > 0) {
                    projectsList = data.projects.map(project => `<li><i class="fas fa-building"></i> ${project.name}</li>`).join('');
                } else {
                    projectsList = '<li class="text-muted">عضو هیچ پروژه‌ای نیست</li>';
                }
                
                document.getElementById('memberDetailsContent').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>اطلاعات شخصی</h6>
                            <table class="table table-borderless">
                                <tr><td><strong>نام و نام خانوادگی:</strong></td><td>${data.member.full_name}</td></tr>
                                <tr><td><strong>نام کاربری:</strong></td><td>${data.member.username}</td></tr>
                                <tr><td><strong>تلفن:</strong></td><td>${data.member.phone || 'ندارد'}</td></tr>
                                <tr><td><strong>ایمیل:</strong></td><td>${data.member.email || 'ندارد'}</td></tr>
                                <tr><td><strong>وضعیت:</strong></td><td>${data.member.is_active == 1 ? '<span class="badge bg-success">فعال</span>' : '<span class="badge bg-danger">غیرفعال</span>'}</td></tr>
                                <tr><td><strong>تاریخ عضویت:</strong></td><td>${data.member.created_at_persian}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>پروژه‌ها و آمار</h6>
                            <div class="mb-3">
                                <strong>پروژه‌های عضو:</strong>
                                <ul class="list-unstyled mt-2">
                                    ${projectsList}
                                </ul>
                            </div>
                            <table class="table table-borderless">
                                <tr><td><strong>کل پرداخت‌ها:</strong></td><td>${data.stats.total_payments} مورد</td></tr>
                                <tr><td><strong>پرداخت‌های تایید شده:</strong></td><td>${data.stats.approved_amount}</td></tr>
                                <tr><td><strong>در انتظار تایید:</strong></td><td>${data.stats.pending_payments} مورد</td></tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                document.getElementById('memberDetailsContent').innerHTML = 
                    '<div class="alert alert-danger">خطا در بارگیری اطلاعات عضو: ' + (data.error || 'خطای نامشخص') + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('memberDetailsContent').innerHTML = 
                '<div class="alert alert-danger">خطا در اتصال به سرور</div>';
        });
}

function editMember(memberId) {
    fetch('get_member_details.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editMemberId').value = memberId;
                document.getElementById('editMemberName').value = data.member.full_name;
                document.getElementById('editMemberUsername').value = data.member.username;
                document.getElementById('editMemberPhone').value = data.member.phone || '';
                document.getElementById('editMemberEmail').value = data.member.email || '';
                
                // Reset all project checkboxes
                document.querySelectorAll('.project-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Check member's projects
                data.projects.forEach(project => {
                    const checkbox = document.getElementById('edit_project_' + project.id);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                
                var modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات عضو: ' + (data.error || 'خطای نامشخص'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در اتصال به سرور');
        });
}

function deleteMember(memberId) {
    if (confirm('آیا از حذف این عضو مطمئن هستید؟\nتمام پرداخت‌های این عضو نیز حذف خواهند شد!')) {
        window.location.href = '?page=members&action=delete&id=' + memberId;
    }
}

function toggleAdminPaymentFields() {
    var paymentType = document.getElementById('adminPaymentType').value;
    var checkFields = document.getElementById('adminCheckFields');
    var cashFields = document.getElementById('adminCashFields');
    
    if (paymentType === 'check') {
        checkFields.style.display = 'block';
        cashFields.style.display = 'none';
        // Make check fields required
        document.querySelector('input[name="check_number"]').required = true;
        document.querySelector('input[name="payment_id_field"]').required = false;
    } else if (paymentType === 'cash') {
        checkFields.style.display = 'none';
        cashFields.style.display = 'block';
        // Make cash fields optional
        document.querySelector('input[name="check_number"]').required = false;
        document.querySelector('input[name="payment_id_field"]').required = false;
    } else {
        checkFields.style.display = 'none';
        cashFields.style.display = 'none';
        document.querySelector('input[name="check_number"]').required = false;
        document.querySelector('input[name="payment_id_field"]').required = false;
    }
}

// Ajax File Upload Handler
document.getElementById('fileInput').addEventListener('change', function() {
    const fileInput = this;
    const file = fileInput.files[0];
    
    if (!file) return;
    
    // Reset previous states
    document.getElementById('uploadProgressContainer').style.display = 'none';
    document.getElementById('uploadSuccess').style.display = 'none';
    document.getElementById('uploadError').style.display = 'none';
    document.getElementById('uploadedFilePath').value = '';
    
    // Validate file
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        showUploadError('فرمت فایل مجاز نیست. فقط jpg, png, gif, pdf مجاز هستند.');
        return;
    }
    
    if (file.size > 5242880) { // 5MB
        showUploadError('حجم فایل نباید بیشتر از 5MB باشد.');
        return;
    }
    
    // Show progress
    document.getElementById('uploadProgressContainer').style.display = 'block';
    
    // Create FormData
    const formData = new FormData();
    formData.append('file', file);
    
    // Create XMLHttpRequest
    const xhr = new XMLHttpRequest();
    
    // Upload progress
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            document.getElementById('uploadProgressBar').style.width = percentComplete + '%';
            document.getElementById('uploadProgressText').textContent = 'آپلود: ' + Math.round(percentComplete) + '%';
        }
    });
    
    // Upload complete
    xhr.addEventListener('load', function() {
        document.getElementById('uploadProgressContainer').style.display = 'none';
        
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                
                if (response.status === 'success') {
                    // Success
                    document.getElementById('uploadedFilePath').value = response.file_path;
                    document.getElementById('uploadedFileName').textContent = response.file_name + ' (' + response.file_size + ')';
                    document.getElementById('uploadSuccess').style.display = 'block';
                } else {
                    // Server error
                    showUploadError(response.message || 'خطا در آپلود فایل');
                }
            } catch (e) {
                showUploadError('خطا در پردازش پاسخ سرور');
            }
        } else {
            showUploadError('خطا در ارتباط با سرور');
        }
    });
    
    // Upload error
    xhr.addEventListener('error', function() {
        document.getElementById('uploadProgressContainer').style.display = 'none';
        showUploadError('خطا در اتصال به سرور');
    });
    
    // Send request
    xhr.open('POST', 'ajax-upload.php');
    xhr.send(formData);
});

function showUploadError(message) {
    document.getElementById('uploadErrorMessage').textContent = message;
    document.getElementById('uploadError').style.display = 'block';
    document.getElementById('fileInput').value = ''; // Reset file input
}

// Form submission handler
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitPaymentBtn');
    const originalText = submitBtn.innerHTML;
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ثبت...';
    
    // Create FormData from form
    const formData = new FormData(this);
    
    // Send form data
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('موفقیت') || data.includes('ثبت شد')) {
            // Success
            showAlert('success', 'پرداخت با موفقیت ثبت شد!');
            
            // Reset form and close modal
            this.reset();
            document.getElementById('uploadedFilePath').value = '';
            document.getElementById('uploadSuccess').style.display = 'none';
            bootstrap.Modal.getInstance(document.getElementById('addPaymentForMemberModal')).hide();
            
            // Reload page
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert('error', 'خطا در ثبت پرداخت');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'خطا در اتصال به سرور');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas ${icon}"></i> ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}
</script>