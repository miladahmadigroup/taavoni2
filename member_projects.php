<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">پروژه‌های من</h1>
</div>

<div class="row">
    <?php foreach ($member_projects as $project): 
        $member_points = get_member_points($_SESSION['user_id'], $project['id']);
        $member_rank = get_member_rank($_SESSION['user_id'], $project['id']);
        $member_payments_total = 0;
        
        $payments = get_member_payments($_SESSION['user_id'], $project['id']);
        foreach ($payments as $payment) {
            if ($payment['is_approved']) {
                $member_payments_total += floatval($payment['amount']);
            }
        }
        
        $progress_percent = $project['min_payment'] > 0 ? ($member_payments_total / floatval($project['min_payment'])) * 100 : 100;
        if ($progress_percent > 100) $progress_percent = 100;
    ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><?php echo $project['name']; ?></h5>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                
                <div class="mb-2">
                    <strong>امتیاز من:</strong> <?php echo persian_number(round($member_points, 2)); ?>
                </div>
                <div class="mb-2">
                    <strong>رتبه من:</strong> <?php echo persian_number($member_rank); ?>
                </div>
                <div class="mb-2">
                    <strong>مجموع پرداخت:</strong> <?php echo format_currency($member_payments_total); ?>
                </div>
                
                <?php if ($project['min_payment'] > 0): ?>
                <div class="mb-2">
                    <strong>پیشرفت پرداخت:</strong>
                    <div class="progress mt-1">
                        <div class="progress-bar <?php echo $progress_percent == 100 ? 'bg-success' : 'bg-primary'; ?>" 
                             style="width: <?php echo $progress_percent; ?>%">
                            <?php echo persian_number(round($progress_percent, 1)); ?>%
                        </div>
                    </div>
                    <small class="text-muted">
                        حداقل مورد نیاز: <?php echo format_currency($project['min_payment']); ?>
                    </small>
                </div>
                <?php endif; ?>
                
                <?php if ($project['payment_deadline']): ?>
                <div class="mb-2">
                    <strong>مهلت پرداخت:</strong> 
                    <span class="text-warning"><?php echo persian_date('full', strtotime($project['payment_deadline'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="?page=add_payment" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> پرداخت جدید
                </a>
                <a href="?page=payments&project_filter=<?php echo $project['id']; ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-list"></i> سوابق
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($member_projects)): ?>
<div class="alert alert-info text-center">
    <h5>شما در هیچ پروژه‌ای عضو نیستید</h5>
    <p>لطفاً با مدیر سیستم تماس بگیرید تا شما را به پروژه‌های مناسب اضافه کند.</p>
</div>
<?php endif; ?>