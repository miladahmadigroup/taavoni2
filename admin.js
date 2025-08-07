// View payment details
function viewPayment(paymentId) {
    window.open('view_payment.php?id=' + paymentId, '_blank', 'width=800,height=600');
}

// Approve/reject payment
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

// Mark check as collected
function markCollected(paymentId) {
    document.getElementById('collectedPaymentId').value = paymentId;
    document.getElementById('collectionDate').value = getCurrentPersianDate();
    
    var modal = new bootstrap.Modal(document.getElementById('collectedModal'));
    modal.show();
}

// Get current Persian date
function getCurrentPersianDate() {
    var today = new Date();
    var year = today.getFullYear() - 621;
    var month = String(today.getMonth() + 1).padStart(2, '0');
    var day = String(today.getDate()).padStart(2, '0');
    return year + '/' + month + '/' + day;
}

// Delete notification
function deleteNotification(notificationId) {
    if (confirm('آیا از حذف این اطلاعیه مطمئن هستید؟')) {
        window.location.href = '?page=notifications&delete=' + notificationId;
    }
}

// View project - اصلاح شده
function viewProject(projectId) {
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
                                <tr><td><strong>کل پرداخت‌های تایید شده:</strong></td><td>${data.stats.approved_payments} تومان</td></tr>
                                <tr><td><strong>پرداخت‌های در انتظار:</strong></td><td>${data.stats.pending_payments} مورد</td></tr>
                                <tr><td><strong>چک‌های وصول نشده:</strong></td><td>${data.stats.uncollected_checks} مورد</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                var modal = new bootstrap.Modal(document.getElementById('projectDetailsModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات پروژه');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Edit project - اصلاح شده
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
                }
                
                var modal = new bootstrap.Modal(document.getElementById('editProjectModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات پروژه');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Delete project
function deleteProject(projectId) {
    if (confirm('آیا از حذف این پروژه مطمئن هستید؟\nتمام پرداخت‌ها و اطلاعیه‌های مربوط به این پروژه نیز حذف خواهند شد!')) {
        window.location.href = '?page=projects&action=delete&id=' + projectId;
    }
}

// View member - اصلاح شده
function viewMember(memberId) {
    fetch('get_member_details.php?id=' + memberId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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
                                    ${data.projects.map(project => `<li><i class="fas fa-building"></i> ${project.name}</li>`).join('')}
                                </ul>
                            </div>
                            <table class="table table-borderless">
                                <tr><td><strong>کل پرداخت‌ها:</strong></td><td>${data.stats.total_payments} مورد</td></tr>
                                <tr><td><strong>پرداخت‌های تایید شده:</strong></td><td>${data.stats.approved_amount} تومان</td></tr>
                                <tr><td><strong>در انتظار تایید:</strong></td><td>${data.stats.pending_payments} مورد</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                var modal = new bootstrap.Modal(document.getElementById('memberDetailsModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات عضو');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Edit member - اصلاح شده
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
                alert('خطا در بارگیری اطلاعات عضو');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Delete member
function deleteMember(memberId) {
    if (confirm('آیا از حذف این عضو مطمئن هستید؟\nتمام پرداخت‌های این عضو نیز حذف خواهند شد!')) {
        window.location.href = '?page=members&action=delete&id=' + memberId;
    }
}

// Edit payment - اصلاح شده
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
                alert('خطا در بارگیری اطلاعات پرداخت');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Toggle admin payment fields
function toggleAdminPaymentFields() {
    var paymentType = document.getElementById('adminPaymentType').value;
    var checkFields = document.getElementById('adminCheckFields');
    var cashDateContainer = document.getElementById('adminCashDateContainer');
    
    if (paymentType === 'check') {
        checkFields.style.display = 'block';
        cashDateContainer.style.display = 'none';
    } else if (paymentType === 'cash') {
        checkFields.style.display = 'none';
        cashDateContainer.style.display = 'block';
    } else {
        checkFields.style.display = 'none';
        cashDateContainer.style.display = 'none';
    }
}

// Toggle edit payment fields
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

// Toggle collection date
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

// View notification - اصلاح شده
function viewNotification(notificationId) {
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
                
                var modal = new bootstrap.Modal(document.getElementById('viewNotificationModal'));
                modal.show();
            } else {
                alert('خطا در بارگیری اطلاعات اطلاعیه');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}

// Edit notification - اصلاح شده
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
                alert('خطا در بارگیری اطلاعات اطلاعیه');
            }
        })
        .catch(error => {
            alert('خطا در اتصال به سرور');
        });
}