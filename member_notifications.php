<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">اطلاعیه‌ها</h1>
</div>

<?php
// Get member project IDs
$project_ids = array();
foreach ($member_projects as $project) {
    $project_ids[] = $project['id'];
}

if (!empty($project_ids)):
    $project_ids_str = implode(',', $project_ids);
    $query = "SELECT n.*, p.name as project_name FROM notifications n 
              JOIN projects p ON n.project_id = p.id 
              WHERE n.project_id IN ($project_ids_str) 
              ORDER BY n.created_at DESC";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0):
?>
<div class="row">
    <?php while ($notification = mysqli_fetch_assoc($result)): ?>
    <div class="col-md-6 mb-3">
        <div class="card notification-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><?php echo $notification['title']; ?></h6>
                <small class="text-muted">
                    <i class="fas fa-building"></i>
                    <?php echo $notification['project_name']; ?>
                </small>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo nl2br($notification['content']); ?></p>
                <small class="text-muted">
                    <i class="fas fa-calendar"></i>
                    <?php echo persian_date('full', strtotime($notification['created_at'])); ?>
                </small>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php 
    else:
        echo '<div class="alert alert-info text-center">
                <h5>اطلاعیه‌ای برای نمایش وجود ندارد</h5>
                <p>هنوز اطلاعیه‌ای برای پروژه‌های شما ثبت نشده است.</p>
              </div>';
    endif;
else:
?>
<div class="alert alert-warning text-center">
    <h5>شما در هیچ پروژه‌ای عضو نیستید</h5>
    <p>برای مشاهده اطلاعیه‌ها، ابتدا باید در حداقل یک پروژه عضو باشید.</p>
    <p>لطفاً با مدیر سیستم تماس بگیرید.</p>
</div>
<?php endif; ?>

<style>
.notification-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-left: 4px solid #007bff;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.notification-card .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.notification-card .card-body p {
    margin-bottom: 0.5rem;
    color: #495057;
    line-height: 1.6;
}

.notification-card .card-body small {
    display: block;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}
</style>