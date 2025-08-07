<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">تنظیمات سیستم</h1>
</div>

<div class="card">
    <div class="card-header">
        <h5>پیام ثبت پرداخت</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">پیام نمایش داده شده پس از ثبت پرداخت:</label>
                <textarea class="form-control" name="payment_message" rows="4" required><?php echo get_setting('payment_success_message'); ?></textarea>
                <div class="form-text">این پیام پس از ثبت موفق پرداخت توسط کاربران نمایش داده می‌شود.</div>
            </div>
            <button type="submit" name="update_message" class="btn btn-primary">بروزرسانی</button>
        </form>
    </div>
</div>