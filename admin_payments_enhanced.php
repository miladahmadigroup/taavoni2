<?php
// Handle payment operations
if (isset($_POST['edit_payment'])) {
    $payment_id = sanitize_input($_POST['payment_id']);
    $amount = sanitize_input($_POST['amount']);
    $payment_type = sanitize_input($_POST['payment_type']);
    
    // Build payment date
    $payment_date = $_POST['payment_date_year'] . '/' . 
                   str_pad($_POST['payment_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                   str_pad($_POST['payment_date_day'], 2, '0', STR_PAD_LEFT);
    $payment_date = persian_to_gregorian($payment_date);
    
    $is_approved = intval($_POST['is_approved']);
    $is_collected = intval($_POST['is_collected']);
    $admin_note_public = sanitize_input($_POST['admin_note_public']);
    $admin_note_private = sanitize_input($_POST['admin_note_private']);
    
    if ($payment_type == 'cash') {
        $payment_id_field = sanitize_input($_POST['payment_id_field']);
        $query = "UPDATE payments SET 
                  amount = '$amount',
                  payment_type = '$payment_type',
                  payment_date = '$payment_date',
                  payment_id = '$payment_id_field',
                  check_number = NULL,
                  check_date = NULL,
                  is_approved = '$is_approved',
                  is_collected = 0,
                  collection_date = NULL,
                  admin_note_public = '$admin_note_public',
                  admin_note_private = '$admin_note_private'
                  WHERE id = '$payment_id'";
    } else {
        $check_number = sanitize_input($_POST['check_number']);
        $check_date = $_POST['check_date_year'] . '/' . 
                     str_pad($_POST['check_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                     str_pad($_POST['check_date_day'], 2, '0', STR_PAD_LEFT);
        $check_date = persian_to_gregorian($check_date);
        
        $collection_date = null;
        if ($is_collected && $_POST['collection_date_year']) {
            $collection_date = $_POST['collection_date_year'] . '/' . 
                              str_pad($_POST['collection_date_month'], 2, '0', STR_PAD_LEFT) . '/' . 
                              str_pad($_POST['collection_date_day'], 2, '0', STR_PAD_LEFT);
            $collection_date = persian_to_gregorian($collection_date);
        }
        
        $query = "UPDATE payments SET 
                  amount = '$amount',
                  payment_type = '$payment_type',
                  payment_date = '$payment_date',
                  payment_id = NULL,
                  check_number = '$check_number',
                  check_date = '$check_date',
                  is_approved = '$is_approved',
                  is_collected = '$is_collected',
                  collection_date = " . ($collection_date ? "'$collection_date'" : "NULL") . ",
                  admin_note_public = '$admin_note_public',
                  admin_note_private = '$admin_note_private'
                  WHERE id = '$payment_id'";
    }
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('پرداخت بروزرسانی شد!'); window.location.href='?page=payments';</script>";
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">مدیریت پرداخت‌ها</h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentForMemberModal">
        <i class="fas fa-plus"></i> ثبت پرداخت برای عضو
    </button>
</div>

<!-- Add Payment for Member Modal -->
<div class="modal fade" id="addPaymentForMemberModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="paymentFormInPayments">
                <div class="modal-header">
                    <h5 class="modal-title">ثبت پرداخت برای عضو</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="uploaded_file_path" id="uploadedFilePathPayments">
                    
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
                                <select class="form-control" name="payment_type" id="paymentTypeInPayments" required onchange="togglePaymentFieldsInPayments()">
                                    <option value="">انتخاب کنید...</option>
                                    <option value="cash">نقد</option>
                                    <option value="check">چک</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="cashFieldsInPayments" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">تاریخ پرداخت:</label>
                            <div id="payment_date_picker_in_payments" data-date-picker="payment_date"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">شناسه پرداخت:</label>
                            <input type="text" class="form-control" name="payment_id_field" placeholder="کد پیگیری، شماره کارت و...">
                        </div>
                    </div>
                    
                    <div id="checkFieldsInPayments" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">شماره چک:</label>
                                    <input type="text" class="form-control" name="check_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تاریخ سررسید چک:</label>
                                    <div id="check_date_picker_in_payments" data-date-picker="check_date"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تصویر سند:</label>
                        <input type="file" class="form-control" id="fileInputInPayments" accept="image/*,.pdf">
                        <div id="uploadSuccessInPayments" style="display: none;" class="mt-2">
                            <div class="alert alert-success">فایل آپلود شد</div>
                        </div>
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

<script>
function togglePaymentFieldsInPayments() {
    var paymentType = document.getElementById('paymentTypeInPayments').value;
    var cashFields = document.getElementById('cashFieldsInPayments');
    var checkFields = document.getElementById('checkFieldsInPayments');
    
    if (paymentType === 'cash') {
        cashFields.style.display = 'block';
        checkFields.style.display = 'none';
        createPersianDatePicker('payment_date_picker_in_payments', 'payment_date', getCurrentPersianDate());
    } else if (paymentType === 'check') {
        cashFields.style.display = 'none';
        checkFields.style.display = 'block';
        createPersianDatePicker('check_date_picker_in_payments', 'check_date', getCurrentPersianDate());
    } else {
        cashFields.style.display = 'none';
        checkFields.style.display = 'none';
    }
}

// File upload for payments page
document.getElementById('fileInputInPayments').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    fetch('ajax-upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('uploadedFilePathPayments').value = data.file_path;
            document.getElementById('uploadSuccessInPayments').style.display = 'block';
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>

<!-- Payments Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>عضو</th>
                <th>پروژه</th>
                <th>مبلغ</th>
                <th>نوع</th>
                <th>تاریخ پرداخت</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT p.*, m.full_name as member_name, pr.name as project_name 
                      FROM payments p 
                      JOIN members m ON p.member_id = m.id 
                      JOIN projects pr ON p.project_id = pr.id 
                      WHERE pr.cooperative_id = '{$_SESSION['cooperative_id']}' 
                      ORDER BY p.created_at DESC";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) == 0) {
                echo '<tr><td colspan="7" class="text-center">هیچ پرداختی ثبت نشده است</td></tr>';
            } else {
                while ($payment = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?php echo $payment['member_name']; ?></td>
                <td><?php echo $payment['project_name']; ?></td>
                <td><?php echo format_currency($payment['amount']); ?></td>
                <td>
                    <?php if ($payment['payment_type'] == 'cash'): ?>
                        <span class="badge bg-success">نقد</span>
                    <?php else: ?>
                        <span class="badge bg-warning">چک</span>
                    <?php endif; ?>
                </td>
                <td><?php echo persian_date('Y/m/d', strtotime($payment['payment_date'])); ?></td>
                <td>
                    <?php if ($payment['is_approved']): ?>
                        <span class="badge bg-success">تایید شده</span>
                    <?php else: ?>
                        <span class="badge bg-warning">در انتظار</span>
                    <?php endif; ?>
                    
                    <?php if ($payment['payment_type'] == 'check'): ?>
                        <br>
                        <?php if ($payment['is_collected']): ?>
                            <span class="badge bg-info">وصول شده</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">وصول نشده</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewPayment(<?php echo $payment['id']; ?>)" title="مشاهده جزئیات">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editPayment(<?php echo $payment['id']; ?>)" title="ویرایش پرداخت">
                        <i class="fas fa-edit"></i>
                    </button>
                    
                    <?php if ($payment['payment_type'] == 'check' && !$payment['is_collected'] && $payment['is_approved']): ?>
                        <button class="btn btn-sm btn-primary" onclick="markCollected(<?php echo $payment['id']; ?>)" title="وصول">
                            وصول
                        </button>
                    <?php endif; ?>
                    
                    <a href="?page=payments&action=delete&id=<?php echo $payment['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('آیا از حذف این پرداخت مطمئن هستید؟')" 
                       title="حذف پرداخت">
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

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">ویرایش پرداخت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="editPaymentId">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">مبلغ (تومان):</label>
                                <input type="number" class="form-control" name="amount" id="editPaymentAmount" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">نوع پرداخت:</label>
                                <select class="form-control" name="payment_type" id="editPaymentType" onchange="toggleEditPaymentFields()">
                                    <option value="cash">نقد</option>
                                    <option value="check">چک</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">تاریخ پرداخت:</label>
                                <div id="edit_payment_date_picker" data-date-picker="payment_date"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cash fields -->
                    <div id="editCashFields">
                        <div class="mb-3">
                            <label class="form-label">شناسه پرداخت:</label>
                            <input type="text" class="form-control" name="payment_id_field" id="editPaymentIdField">
                        </div>
                    </div>
                    
                    <!-- Check fields -->
                    <div id="editCheckFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">شماره چک:</label>
                                    <input type="text" class="form-control" name="check_number" id="editCheckNumber">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تاریخ چک:</label>
                                    <div id="edit_check_date_picker" data-date-picker="check_date"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="is_collected" value="1" id="editIsCollected" onchange="toggleCollectionDate()">
                                    <label class="form-check-label" for="editIsCollected">
                                        چک وصول شده
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6" id="collectionDateContainer" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">تاریخ وصول:</label>
                                    <div id="edit_collection_date_picker" data-date-picker="collection_date"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_approved" value="1" id="editIsApproved">
                                <label class="form-check-label" for="editIsApproved">
                                    پرداخت تایید شده
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات عمومی:</label>
                        <textarea class="form-control" name="admin_note_public" id="editAdminNotePublic" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات خصوصی:</label>
                        <textarea class="form-control" name="admin_note_private" id="editAdminNotePrivate" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="edit_payment" class="btn btn-primary">بروزرسانی</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Payment Modal -->
<div class="modal fade" id="approveModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalTitle">تایید پرداخت</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="approvePaymentId">
                    <input type="hidden" name="action" id="approveAction">
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات عمومی (قابل مشاهده توسط کاربر):</label>
                        <textarea class="form-control" name="admin_note_public" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات خصوصی (فقط برای مدیر):</label>
                        <textarea class="form-control" name="admin_note_private" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="approve_payment" class="btn" id="approveBtn">تایید</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark Collected Modal -->
<div class="modal fade" id="collectedModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">علامت‌گذاری وصول چک</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="collectedPaymentId">
                    
                    <div class="mb-3">
                        <label class="form-label">تاریخ وصول:</label>
                        <div id="collection_date_picker" data-date-picker="collection_date" data-default-date="<?php echo persian_date('Y/m/d'); ?>"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" name="mark_collected" class="btn btn-success">ثبت وصول</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewPayment(paymentId) {
    window.open('view_payment.php?id=' + paymentId, '_blank', 'width=800,height=600');
}

function editPayment(paymentId) {
    fetch('get_payment_details.php?id=' + paymentId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                
                document.getElementById('editPaymentId').value = paymentId;
                document.getElementById('editPaymentAmount').value = payment.amount;
                document.getElementById('editPaymentType').value = payment.payment_type;
                document.getElementById('editIsApproved').checked = payment.is_approved == 1;
                document.getElementById('editAdminNotePublic').value = payment.admin_note_public || '';
                document.getElementById('editAdminNotePrivate').value = payment.admin_note_private || '';
                
                // Set payment date
                createPersianDatePicker('edit_payment_date_picker', 'payment_date', payment.payment_date_persian);
                
                if (payment.payment_type === 'cash') {
                    document.getElementById('editPaymentIdField').value = payment.payment_id || '';
                    document.getElementById('editCashFields').style.display = 'block';
                    document.getElementById('editCheckFields').style.display = 'none';
                } else {
                    document.getElementById('editCheckNumber').value = payment.check_number || '';
                    document.getElementById('editIsCollected').checked = payment.is_collected == 1;
                    
                    if (payment.check_date_persian) {
                        createPersianDatePicker('edit_check_date_picker', 'check_date', payment.check_date_persian);
                    }
                    
                    if (payment.is_collected == 1 && payment.collection_date_persian) {
                        document.getElementById('collectionDateContainer').style.display = 'block';
                        createPersianDatePicker('edit_collection_date_picker', 'collection_date', payment.collection_date_persian);
                    }
                    
                    document.getElementById('editCashFields').style.display = 'none';
                    document.getElementById('editCheckFields').style.display = 'block';
                }
                
                var modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات پرداخت: ' + (data.error || 'خطای نامشخص'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('خطا در اتصال به سرور');
        });
}

function approvePayment(paymentId, action) {
    document.getElementById('approvePaymentId').value = paymentId;
    document.getElementById('approveAction').value = action;
    
    if (action === 'approve') {
        document.getElementById('approveModalTitle').textContent = 'تایید پرداخت';
        document.getElementById('approveBtn').textContent = 'تایید';
        document.getElementById('approveBtn').className = 'btn btn-success';
    } else {
        document.getElementById('approveModalTitle').textContent = 'رد پرداخت';
        document.getElementById('approveBtn').textContent = 'رد';
        document.getElementById('approveBtn').className = 'btn btn-danger';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function markCollected(paymentId) {
    document.getElementById('collectedPaymentId').value = paymentId;
    document.getElementById('collectionDate').value = getCurrentPersianDate();
    
    var modal = new bootstrap.Modal(document.getElementById('collectedModal'));
    modal.show();
}

function getCurrentPersianDate() {
    var today = new Date();
    var year = today.getFullYear() - 621;
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var day = String(today.getDate()).padStart(2, '0');
    return year + '/' + month + '/' + day;
}

function toggleEditPaymentFields() {
    var paymentType = document.getElementById('editPaymentType').value;
    var cashFields = document.getElementById('editCashFields');
    var checkFields = document.getElementById('editCheckFields');
    
    if (paymentType === 'cash') {
        cashFields.style.display = 'block';
        checkFields.style.display = 'none';
    } else {
        cashFields.style.display = 'none';
        checkFields.style.display = 'block';
    }
}

function toggleCollectionDate() {
    var isCollected = document.getElementById('editIsCollected').checked;
    var container = document.getElementById('collectionDateContainer');
    
    if (isCollected) {
        container.style.display = 'block';
        createPersianDatePicker('edit_collection_date_picker', 'collection_date', getCurrentPersianDate());
    } else {
        container.style.display = 'none';
    }
}
</script>