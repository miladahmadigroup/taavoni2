-- ایجاد دیتابیس تعاونی مسکن
CREATE DATABASE IF NOT EXISTS housing_coop CHARACTER SET utf8 COLLATE utf8_general_ci;
USE housing_coop;

-- جدول تعاونی‌ها
CREATE TABLE IF NOT EXISTS cooperatives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL COMMENT 'نام تعاونی',
    description TEXT COMMENT 'توضیحات تعاونی',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول پروژه‌ها
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL COMMENT 'شناسه تعاونی',
    name VARCHAR(255) NOT NULL COMMENT 'نام پروژه',
    description TEXT COMMENT 'توضیحات پروژه',
    min_payment DECIMAL(15,2) DEFAULT 0 COMMENT 'حداقل پرداخت مورد نیاز',
    payment_deadline DATE COMMENT 'مهلت پرداخت',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول اعضا
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id INT NOT NULL COMMENT 'شناسه تعاونی',
    username VARCHAR(100) UNIQUE NOT NULL COMMENT 'نام کاربری',
    password VARCHAR(255) NOT NULL COMMENT 'رمز عبور (هش شده)',
    full_name VARCHAR(255) NOT NULL COMMENT 'نام و نام خانوادگی',
    phone VARCHAR(20) COMMENT 'شماره تلفن',
    email VARCHAR(100) COMMENT 'آدرس ایمیل',
    is_active TINYINT DEFAULT 1 COMMENT 'وضعیت فعال بودن (0: غیرفعال، 1: فعال)',
    is_admin TINYINT DEFAULT 0 COMMENT 'نقش مدیر (0: عادی، 1: مدیر)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ عضویت',
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول عضویت اعضا در پروژه‌ها
CREATE TABLE IF NOT EXISTS member_projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL COMMENT 'شناسه عضو',
    project_id INT NOT NULL COMMENT 'شناسه پروژه',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ پیوستن به پروژه',
    UNIQUE KEY unique_member_project (member_id, project_id),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول پرداخت‌ها
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL COMMENT 'شناسه عضو پرداخت کننده',
    project_id INT NOT NULL COMMENT 'شناسه پروژه',
    amount DECIMAL(15,2) NOT NULL COMMENT 'مبلغ پرداخت (تومان)',
    payment_type ENUM('cash', 'check') NOT NULL COMMENT 'نوع پرداخت (نقد یا چک)',
    payment_date DATE NOT NULL COMMENT 'تاریخ پرداخت',
    
    -- فیلدهای مخصوص پرداخت نقدی
    payment_id VARCHAR(100) COMMENT 'شناسه پرداخت (برای نقد)',
    
    -- فیلدهای مخصوص چک
    check_number VARCHAR(100) COMMENT 'شماره چک',
    check_date DATE COMMENT 'تاریخ چک',
    
    -- فایل ضمیمه
    image_path VARCHAR(255) COMMENT 'مسیر تصویر سند/چک',
    
    -- وضعیت تایید
    is_approved TINYINT DEFAULT 0 COMMENT 'وضعیت تایید (0: در انتظار، 1: تایید شده)',
    
    -- وضعیت وصول چک
    is_collected TINYINT DEFAULT 0 COMMENT 'وضعیت وصول چک (فقط برای چک)',
    collection_date DATE COMMENT 'تاریخ وصول چک',
    
    -- یادداشت‌های مدیر
    admin_note_public TEXT COMMENT 'یادداشت مدیر (قابل مشاهده توسط کاربر)',
    admin_note_private TEXT COMMENT 'یادداشت مدیر (غیرقابل مشاهده توسط کاربر)',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ثبت پرداخت',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    
    INDEX idx_member_project (member_id, project_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_is_approved (is_approved),
    INDEX idx_payment_type (payment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول اطلاعیه‌ها
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL COMMENT 'شناسه پروژه مربوطه',
    title VARCHAR(255) NOT NULL COMMENT 'عنوان اطلاعیه',
    content TEXT NOT NULL COMMENT 'متن اطلاعیه',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_date (project_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- جدول تنظیمات سیستم
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL COMMENT 'کلید تنظیم',
    setting_value TEXT COMMENT 'مقدار تنظیم',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    UNIQUE KEY unique_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- درج تنظیمات پیش‌فرض
INSERT INTO settings (setting_key, setting_value) VALUES 
('payment_success_message', 'سند پرداخت شما با موفقیت ثبت شد و در انتظار تایید مدیر می‌باشد.'),
('system_version', '1.0.0'),
('system_name', 'سیستم مدیریت تعاونی مسکن')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ایجاد داده‌های نمونه (اختیاری)
-- تعاونی نمونه
INSERT INTO cooperatives (name, description) VALUES 
('تعاونی مسکن پردیس', 'تعاونی مسکن منطقه پردیس تهران')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- پروژه نمونه
INSERT INTO projects (cooperative_id, name, description, min_payment, payment_deadline) VALUES 
(1, 'پروژه مسکونی فاز یک', 'ساخت 100 واحد مسکونی در فاز اول', 50000000, '2024-12-29'),
(1, 'پروژه مسکونی فاز دو', 'ساخت 150 واحد مسکونی در فاز دوم', 75000000, '2025-06-21')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- کاربر مدیر نمونه (رمز عبور: admin123)
INSERT INTO members (cooperative_id, username, password, full_name, is_admin, phone, email) VALUES 
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'مدیر سیستم', 1, '09123456789', 'admin@example.com')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- اعضای نمونه
INSERT INTO members (cooperative_id, username, password, full_name, phone, email) VALUES 
(1, 'member1', '25f9e794323b453885f5181f1b624d0b', 'علی احمدی', '09121111111', 'ali@example.com'),
(1, 'member2', '25f9e794323b453885f5181f1b624d0b', 'مریم محمدی', '09122222222', 'maryam@example.com'),
(1, 'member3', '25f9e794323b453885f5181f1b624d0b', 'حسین رضایی', '09123333333', 'hossein@example.com')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- عضویت اعضا در پروژه‌ها
INSERT INTO member_projects (member_id, project_id) VALUES 
(2, 1), (2, 2),
(3, 1),
(4, 1), (4, 2)
ON DUPLICATE KEY UPDATE member_id = VALUES(member_id);

-- پرداخت‌های نمونه
INSERT INTO payments (member_id, project_id, amount, payment_type, payment_date, payment_id, is_approved) VALUES 
(2, 1, 10000000, 'cash', '2024-01-15', 'PAY123456', 1),
(2, 1, 5000000, 'check', '2024-02-10', NULL, 1),
(3, 1, 15000000, 'cash', '2024-01-20', 'PAY789012', 0),
(4, 1, 8000000, 'check', '2024-02-05', NULL, 1)
ON DUPLICATE KEY UPDATE amount = VALUES(amount);

-- بروزرسانی فیلدهای چک برای پرداخت‌های نوع چک
UPDATE payments SET 
    check_number = '1234567', 
    check_date = '2024-03-01', 
    is_collected = 1, 
    collection_date = '2024-03-01' 
WHERE id = 2 AND payment_type = 'check';

UPDATE payments SET 
    check_number = '7891011', 
    check_date = '2024-03-15', 
    is_collected = 0 
WHERE id = 4 AND payment_type = 'check';

-- اطلاعیه‌های نمونه
INSERT INTO notifications (project_id, title, content) VALUES 
(1, 'آغاز مرحله ساخت', 'با سلام و احترام، اعلام می‌گردد که مرحله ساخت پروژه فاز یک آغاز شده است. لطفاً پرداخت‌های خود را در مهلت مقرر انجام دهید.'),
(1, 'جلسه هیئت مدیره', 'جلسه هیئت مدیره روز شنبه ساعت 10 صبح در محل دفتر تعاونی برگزار خواهد شد.'),
(2, 'اطلاعیه مهم', 'به اطلاع اعضای محترم پروژه فاز دو می‌رساند که مهلت پرداخت اقساط تا پایان ماه جاری تمدید شده است.')
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ایجاد View برای گزارش‌گیری سریع
CREATE OR REPLACE VIEW payment_summary AS
SELECT 
    m.full_name as member_name,
    p.name as project_name,
    COUNT(pay.id) as total_payments,
    SUM(CASE WHEN pay.is_approved = 1 THEN pay.amount ELSE 0 END) as approved_amount,
    SUM(CASE WHEN pay.is_approved = 0 THEN pay.amount ELSE 0 END) as pending_amount,
    COUNT(CASE WHEN pay.payment_type = 'check' AND pay.is_collected = 0 THEN 1 END) as uncollected_checks
FROM members m
LEFT JOIN member_projects mp ON m.id = mp.member_id
LEFT JOIN projects p ON mp.project_id = p.id
LEFT JOIN payments pay ON m.id = pay.member_id AND p.id = pay.project_id
WHERE m.is_active = 1
GROUP BY m.id, p.id;

-- ایجاد View برای آمار پروژه‌ها
CREATE OR REPLACE VIEW project_stats AS
SELECT 
    p.id,
    p.name,
    p.cooperative_id,
    COUNT(DISTINCT mp.member_id) as total_members,
    COUNT(pay.id) as total_payments,
    SUM(CASE WHEN pay.is_approved = 1 THEN pay.amount ELSE 0 END) as approved_payments,
    SUM(CASE WHEN pay.is_approved = 0 THEN pay.amount ELSE 0 END) as pending_payments,
    COUNT(CASE WHEN pay.payment_type = 'check' AND pay.is_collected = 0 AND pay.is_approved = 1 THEN 1 END) as uncollected_checks,
    p.min_payment,
    p.payment_deadline
FROM projects p
LEFT JOIN member_projects mp ON p.id = mp.project_id
LEFT JOIN payments pay ON p.id = pay.project_id
GROUP BY p.id;

-- ایجاد Stored Procedure برای محاسبه امتیاز
DELIMITER //
CREATE PROCEDURE CalculateMemberPoints(
    IN member_id INT,
    IN project_id INT,
    OUT total_points DECIMAL(10,2)
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE payment_amount DECIMAL(15,2);
    DECLARE payment_start_date DATE;
    DECLARE days_diff INT;
    DECLARE points_per_day DECIMAL(10,2);
    
    DECLARE payment_cursor CURSOR FOR
        SELECT amount, 
               CASE 
                   WHEN payment_type = 'check' AND is_collected = 1 THEN collection_date
                   WHEN payment_type = 'check' AND is_collected = 0 THEN NULL
                   ELSE payment_date
               END as start_date
        FROM payments 
        WHERE member_id = member_id AND project_id = project_id AND is_approved = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET total_points = 0;
    
    OPEN payment_cursor;
    
    payment_loop: LOOP
        FETCH payment_cursor INTO payment_amount, payment_start_date;
        
        IF done THEN
            LEAVE payment_loop;
        END IF;
        
        IF payment_start_date IS NOT NULL THEN
            SET days_diff = DATEDIFF(CURDATE(), payment_start_date);
            SET points_per_day = payment_amount / 100000; -- هر 100 هزار تومان = 1 امتیاز در روز
            SET total_points = total_points + (points_per_day * days_diff);
        END IF;
        
    END LOOP;
    
    CLOSE payment_cursor;
END //
DELIMITER ;

-- ایجاد Trigger برای بروزرسانی خودکار تاریخ
DELIMITER //
CREATE TRIGGER update_payment_timestamp 
    BEFORE UPDATE ON payments 
    FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- ایجاد Index‌های بهینه‌سازی
CREATE INDEX idx_members_cooperative ON members(cooperative_id, is_active);
CREATE INDEX idx_projects_cooperative ON projects(cooperative_id);
CREATE INDEX idx_payments_member_project_date ON payments(member_id, project_id, payment_date);
CREATE INDEX idx_payments_approval_status ON payments(is_approved, payment_type, is_collected);
CREATE INDEX idx_notifications_project_date ON notifications(project_id, created_at DESC);

-- نهایی سازی
COMMIT;
    