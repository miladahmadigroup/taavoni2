<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">داشبورد</h1>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo persian_number(count($member_projects)); ?></h4>
                        <p class="card-text">پروژه‌های من</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo format_currency($member_stats['approved_payments']); ?></h4>
                        <p class="card-text">پرداخت تایید شده</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo persian_number($member_stats['pending_payments']); ?></h4>
                        <p class="card-text">در انتظار تایید</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo persian_number($member_stats['total_payments']); ?></h4>
                        <p class="card-text">کل پرداخت‌ها</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Projects Overview -->
<div class="card">
    <div class="card-header">
        <h5>وضعیت پروژه‌ها</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>نام پروژه</th>
                        <th>امتیاز من</th>
                        <th>رتبه من</th>
                        <th>مجموع پرداخت</th>
                        <th>حداقل مورد نیاز</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($member_projects as $project): 
                        $member_points = get_member_points($_SESSION['user_id'], $project['id']);
                        $member_rank = get_member_rank($_SESSION['user_id'], $project['id']);
                        $member_payments_total = 0;
                        
                        $payments = get_member_payments($_SESSION['user_id'], $project['id']);
                        foreach ($payments as $payment) {
                            if ($payment['is_approved']) {
                                $member_payments_total += $payment['amount'];
                            }
                        }
                    ?>
                    <tr>
                        <td><?php echo $project['name']; ?></td>
                        <td><?php echo persian_number(round($member_points, 2)); ?></td>
                        <td><?php echo persian_number($member_rank); ?></td>
                        <td><?php echo format_currency($member_payments_total); ?></td>
                        <td><?php echo format_currency($project['min_payment']); ?></td>
                        <td>
                            <?php if ($member_payments_total >= $project['min_payment']): ?>
                                <span class="badge bg-success">کامل</span>
                            <?php else: ?>
                                <span class="badge bg-warning">کمبود: <?php echo format_currency($project['min_payment'] - $member_payments_total); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>