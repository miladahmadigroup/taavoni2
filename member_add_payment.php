<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">ثبت پرداخت جدید</h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">انتخاب پروژه:</label>
                        <select class="form-control" name="project_id" required>
                            <option value="">انتخاب کنید...</option>
                            <?php foreach ($member_projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">مبلغ (تومان):</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">نوع سند:</label>
                        <select class="form-control" name="payment_type" id="paymentType" required onchange="togglePaymentFields()">
                            <option value="">انتخاب کنید...</option>
                            <option value="cash">نقد</option>
                            <option value="check">چک</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">تاریخ پرداخت:</label>
                        <div id="payment_date_picker" data-date-picker="payment_date" data-default-date="<?php echo persian_date('Y/m/d'); ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Cash Payment Fields -->
            <div id="cashFields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">شناسه پرداخت:</label>
                    <input type="text" class="form-control" name="payment_id">
                    <div class="form-text">شماره رسید، شماره کارت، کد پیگیری و...</div>
                </div>
            </div>

            <!-- Check Payment Fields -->
            <div id="checkFields" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">شماره چک:</label>
                            <input type="text" class="form-control" name="check_number">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">تاریخ چک:</label>
                            <div id="check_date_picker" data-date-picker="check_date" data-default-date="<?php echo persian_date('Y/m/d'); ?>"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">تصویر سند/چک:</label>
                <input type="file" class="form-control" name="payment_image" accept="image/*,.pdf">
                <div class="form-text">فرمت‌های مجاز: JPG, PNG, PDF</div>
            </div>

            <div class="text-center">
                <button type="submit" name="add_payment" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> ثبت پرداخت
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle payment type fields
function togglePaymentFields() {
    var paymentType = document.getElementById('paymentType').value;
    var cashFields = document.getElementById('cashFields');
    var checkFields = document.getElementById('checkFields');
    
    if (paymentType === 'cash') {
        cashFields.style.display = 'block';
        checkFields.style.display = 'none';
        
        // Make cash fields required
        document.querySelector('input[name="payment_id"]').required = false; // شناسه پرداخت اختیاری باشد
        document.querySelector('input[name="check_number"]').required = false;
    } else if (paymentType === 'check') {
        cashFields.style.display = 'none';
        checkFields.style.display = 'block';
        
        // Make check fields required
        document.querySelector('input[name="payment_id"]').required = false;
        document.querySelector('input[name="check_number"]').required = true;
    } else {
        cashFields.style.display = 'none';
        checkFields.style.display = 'none';
        
        // Reset required fields
        document.querySelector('input[name="payment_id"]').required = false;
        document.querySelector('input[name="check_number"]').required = false;
    }
}
</script>