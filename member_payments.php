<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">سوابق پرداخت</h1>
</div>

<!-- Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="payments">
            <div class="col-md-4">
                <label class="form-label">فیلتر بر اساس پروژه:</label>
                <select class="form-control" name="project_filter">
                    <option value="">همه پروژه‌ها</option>
                    <?php foreach ($member_projects as $project): ?>
                    <option value="<?php echo $project['id']; ?>" <?php echo (isset($_GET['project_filter']) && $_GET['project_filter'] == $project['id']) ? 'selected' : ''; ?>>
                        <?php echo $project['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">فیلتر</button>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>پروژه</th>
                <th>مبلغ</th>
                <th>نوع</th>
                <th>تاریخ پرداخت</th>
                <th>تاریخ ثبت</th>
                <th>وضعیت</th>
                <th>توضیحات مدیر</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $project_filter = isset($_GET['project_filter']) ? $_GET['project_filter'] : null;
            $payments = get_member_payments($_SESSION['user_id'], $project_filter);
            foreach ($payments as $payment):
            ?>
            <tr>
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
                <td><?php echo persian_date('Y/m/d', strtotime($payment['created_at'])); ?></td>
                <td>
                    <?php if ($payment['is_approved']): ?>
                        <span class="badge bg-success">تایید شده</span>
                    <?php else: ?>
                        <span class="badge bg-warning">در انتظار تایید</span>
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
                    <?php echo $payment['admin_note_public'] ? $payment['admin_note_public'] : '-'; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewPaymentDetails(<?php echo $payment['id']; ?>)">
                        <i class="fas fa-eye"></i>
                    </button>
                    <?php if ($payment['image_path'] && file_exists($payment['image_path'])): ?>
                        <button class="btn btn-sm btn-secondary" onclick="viewPaymentImage('<?php echo $payment['image_path']; ?>')">
                            <i class="fas fa-image"></i>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Image View Modal -->
<div class="modal fade" id="imageModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">مشاهده تصویر سند</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="تصویر سند" class="img-fluid" style="max-height: 70vh;">
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="" download class="btn btn-primary">
                    <i class="fas fa-download"></i> دانلود
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<script>
// View payment details
function viewPaymentDetails(paymentId) {
    window.open('view_payment.php?id=' + paymentId, '_blank', 'width=800,height=600');
}

// View payment image
function viewPaymentImage(imagePath) {
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('downloadLink').href = imagePath;
    
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>