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
                        <h4 class="card-title"><?php echo persian_number($stats['total_members']); ?></h4>
                        <p class="card-text">کل اعضا</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
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
                        <h4 class="card-title"><?php echo persian_number($stats['total_projects']); ?></h4>
                        <p class="card-text">کل پروژه‌ها</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
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
                        <h4 class="card-title"><?php echo persian_number($stats['pending_payments']); ?></h4>
                        <p class="card-text">پرداخت‌های در انتظار</p>
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
                        <h4 class="card-title"><?php echo format_currency($stats['monthly_payments']); ?></h4>
                        <p class="card-text">پرداخت این ماه</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="card">
    <div class="card-header">
        <h5>پرداخت‌های اخیر</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>عضو</th>
                        <th>پروژه</th>
                        <th>مبلغ</th>
                        <th>نوع</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT p.*, m.full_name as member_name, pr.name as project_name 
                              FROM payments p 
                              JOIN members m ON p.member_id = m.id 
                              JOIN projects pr ON p.project_id = pr.id 
                              WHERE pr.cooperative_id = '{$_SESSION['cooperative_id']}' 
                              ORDER BY p.created_at DESC LIMIT 10";
                    $result = mysqli_query($conn, $query);
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
                        <td>
                            <?php if ($payment['is_approved']): ?>
                                <span class="badge bg-success">تایید شده</span>
                            <?php else: ?>
                                <span class="badge bg-warning">در انتظار</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo persian_date('Y/m/d', strtotime($payment['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>