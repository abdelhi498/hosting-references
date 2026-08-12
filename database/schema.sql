-- ============================================================
-- مراجع الاستضافة / Hosting References — Database Schema
-- MySQL 5.7+ / MariaDB. Plain SQL — no ORM/migrations needed.
-- Import this once via phpMyAdmin or: mysql -u USER -p DB < schema.sql
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS companies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  logo VARCHAR(255) DEFAULT NULL,
  website_url VARCHAR(255) DEFAULT NULL,
  affiliate_url VARCHAR(500) NOT NULL,
  trust_score DECIMAL(3,1) NOT NULL DEFAULT 8.0,
  summary_ar TEXT,
  summary_en TEXT,
  pros_ar TEXT,
  pros_en TEXT,
  cons_ar TEXT,
  cons_en TEXT,
  starting_price VARCHAR(50) DEFAULT NULL,
  price_note_ar VARCHAR(255) DEFAULT NULL,
  price_note_en VARCHAR(255) DEFAULT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feature_keys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(80) NOT NULL UNIQUE,
  label_ar VARCHAR(120) NOT NULL,
  label_en VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS company_features (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  feature_key_id INT NOT NULL,
  value_ar VARCHAR(150) DEFAULT NULL,
  value_en VARCHAR(150) DEFAULT NULL,
  is_included TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_company_feature (company_id, feature_key_id),
  CONSTRAINT fk_cf_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_cf_feature FOREIGN KEY (feature_key_id) REFERENCES feature_keys(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  code VARCHAR(60) NOT NULL,
  title_ar VARCHAR(150) NOT NULL,
  title_en VARCHAR(150) NOT NULL,
  description_ar VARCHAR(255) DEFAULT NULL,
  description_en VARCHAR(255) DEFAULT NULL,
  discount_text VARCHAR(60) DEFAULT NULL,
  expires_at DATE DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  clicks INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_coupon_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('blog','guide') NOT NULL DEFAULT 'blog',
  slug VARCHAR(150) NOT NULL UNIQUE,
  title_ar VARCHAR(200) NOT NULL,
  title_en VARCHAR(200) NOT NULL,
  excerpt_ar VARCHAR(300) DEFAULT NULL,
  excerpt_en VARCHAR(300) DEFAULT NULL,
  content_ar LONGTEXT,
  content_en LONGTEXT,
  cover_image VARCHAR(255) DEFAULT NULL,
  related_company_id INT DEFAULT NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_article_company FOREIGN KEY (related_company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(40) DEFAULT NULL,
  status ENUM('active','banned') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proxy_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT DEFAULT NULL,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  company_id INT DEFAULT NULL,
  plan_desired VARCHAR(150) DEFAULT NULL,
  notes TEXT,
  status ENUM('pending','contacted','paid','delivered','cancelled') NOT NULL DEFAULT 'pending',
  admin_notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pr_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_pr_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clicks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  company_id INT NOT NULL,
  coupon_id INT DEFAULT NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_click_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
  CONSTRAINT fk_click_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) DEFAULT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Default data ----------

INSERT INTO settings (setting_key, setting_value) VALUES
('site_name_ar', 'مراجع الاستضافة'),
('site_name_en', 'Hosting References'),
('site_logo', ''),
('meta_description_ar', 'مراجع صادقة لأفضل شركات الاستضافة، جداول مقارنة، وكوبونات خصم حصرية.'),
('meta_description_en', 'Honest hosting provider reviews, comparison tables, and exclusive discount coupons.'),
('social_facebook', ''),
('social_youtube', ''),
('social_whatsapp', ''),
('social_telegram', '')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Default admin login: admin@example.com / ChangeMe123!  -- CHANGE THIS AFTER FIRST LOGIN
INSERT INTO admins (name, email, password_hash, role) VALUES
('Admin', 'admin@example.com', '$2y$10$STchFRhXRPEZn9HZBDbfgOkkFVC/p46G3Eq9A3nmMMSOMuYV31ijC', 'super_admin')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO feature_keys (key_name, label_ar, label_en, sort_order) VALUES
('disk_space', 'مساحة التخزين', 'Disk Space', 1),
('bandwidth', 'الباقة الشهرية', 'Bandwidth', 2),
('free_ssl', 'شهادة SSL مجانية', 'Free SSL', 3),
('free_domain', 'دومين مجاني', 'Free Domain', 4),
('backups', 'نسخ احتياطي', 'Backups', 5),
('uptime', 'ضمان التوافر', 'Uptime Guarantee', 6),
('support', 'الدعم الفني', '24/7 Support', 7)
ON DUPLICATE KEY UPDATE key_name = key_name;

INSERT INTO companies (slug, name, logo, website_url, affiliate_url, trust_score, summary_ar, summary_en, pros_ar, pros_en, cons_ar, cons_en, starting_price, price_note_ar, price_note_en, is_featured, sort_order) VALUES
('hostinger', 'Hostinger', 'hostinger.svg', 'https://www.hostinger.com', 'https://www.hostinger.com/?ref=REPLACE_ME', 9.2,
 'من أفضل شركات الاستضافة من حيث السعر والأداء، مناسبة جدًا للمبتدئين والمشاريع الصغيرة.',
 'One of the best hosting providers for price and performance — great for beginners and small projects.',
 '["سعر منافس جدًا","لوحة تحكم مبسطة hPanel","سرعة تحميل ممتازة"]', '["Very competitive pricing","Simplified hPanel dashboard","Excellent loading speed"]',
 '["الدعم الفني بالعربي محدود","بعض الميزات المتقدمة في الباقات الأعلى فقط"]', '["Limited Arabic support","Some advanced features are higher-tier only"]',
 '$2.49/mo', 'يرتفع السعر عند التجديد', 'Renewal price increases', 1, 1),
('namecheap', 'Namecheap', 'namecheap.svg', 'https://www.namecheap.com', 'https://www.namecheap.com/?ref=REPLACE_ME', 8.6,
 'معروفة أساسًا بأسعار الدومينات المنافسة، وتقدم استضافة موثوقة بضمانات جيدة.',
 'Best known for competitive domain pricing, and offers reliable hosting with solid guarantees.',
 '["أسعار دومينات ممتازة","ضمان استرجاع الأموال","خصوصية دومين مجانية"]', '["Excellent domain pricing","Money-back guarantee","Free domain privacy"]',
 '["واجهة الاستضافة أقل حداثة","الأداء متوسط في الباقات الأساسية"]', '["Hosting UI feels dated","Average performance on entry plans"]',
 '$1.98/mo', 'سعر خاص للسنة الأولى فقط', 'Introductory price for year one only', 1, 2),
('hostpapa', 'HostPapa', 'hostpapa.svg', 'https://www.hostpapa.com', 'https://www.hostpapa.com/?ref=REPLACE_ME', 8.1,
 'استضافة صديقة للبيئة مع دعم فني جيد، ومناسبة للمواقع التجارية الصغيرة.',
 'Eco-friendly hosting with good support, suitable for small business sites.',
 '["دعم فني متجاوب","مناسبة للمواقع التجارية"]', '["Responsive support","Good fit for business sites"]',
 '["أسعار التجديد مرتفعة نسبيًا"]', '["Renewal prices are relatively high"]',
 '$3.95/mo', NULL, NULL, 0, 3),
('contabo', 'Contabo', 'contabo.svg', 'https://www.contabo.com', 'https://www.contabo.com/?ref=REPLACE_ME', 8.4,
 'الأفضل لمن يريد VPS بموارد كبيرة وسعر منخفض جدًا مقارنة بالمنافسين.',
 'The best pick for large-resource VPS at a very low price compared to competitors.',
 '["موارد VPS ضخمة بسعر منافس","مناسبة للمشاريع المتقدمة"]', '["Huge VPS resources for the price","Great for advanced projects"]',
 '["يحتاج خبرة تقنية للإدارة","دعم فني أساسي في الباقات الرخيصة"]', '["Requires technical know-how","Basic support on cheaper plans"]',
 '€4.99/mo', 'مناسبة أكثر للمستخدمين المتقدمين', 'Better suited to advanced users', 0, 4),
('verpex', 'Verpex', 'verpex.svg', 'https://verpex.com', 'https://verpex.com/?ref=REPLACE_ME', 8.8,
 'استضافة سريعة النمو تقدم أداء قوي وضمان استرجاع أموال ممتد.',
 'A fast-growing host offering strong performance and an extended money-back guarantee.',
 '["أداء سريع بفضل LiteSpeed","ضمان استرجاع أموال 45 يوم"]', '["Fast performance via LiteSpeed","45-day money-back guarantee"]',
 '["قاعدة مستخدمين أصغر نسبيًا","مراجعات أقل توفرًا بالعربي"]', '["Smaller user base","Fewer Arabic reviews available"]',
 '$1.99/mo', NULL, NULL, 1, 5)
ON DUPLICATE KEY UPDATE slug = slug;

INSERT INTO company_features (company_id, feature_key_id, value_ar, value_en, is_included) VALUES
(1,1,'100 GB SSD','100 GB SSD',1),(1,2,'غير محدودة','Unmetered',1),(1,3,'مجانية','Free',1),(1,4,'سنة أولى','1st year',1),(1,5,'أسبوعي','Weekly',1),(1,6,'99.9%','99.9%',1),(1,7,'دردشة مباشرة','Live chat',1),
(2,1,'20 GB SSD','20 GB SSD',1),(2,2,'غير محدودة','Unmetered',1),(2,3,'مجانية','Free',1),(2,4,'سنة أولى','1st year',1),(2,5,'غير مضمّن','Not included',0),(2,6,'99.9%','99.9%',1),(2,7,'تذاكر دعم','Ticket support',1),
(3,1,'100 GB SSD','100 GB SSD',1),(3,2,'غير محدودة','Unmetered',1),(3,3,'مجانية','Free',1),(3,4,'سنة أولى','1st year',1),(3,5,'أسبوعي','Weekly',1),(3,6,'99.9%','99.9%',1),(3,7,'هاتف ودردشة','Phone + chat',1),
(4,1,'حتى 1600 GB NVMe','Up to 1600 GB NVMe',1),(4,2,'32 TB','32 TB',1),(4,3,'غير مضمّن','Not included',0),(4,4,'غير مضمّن','Not included',0),(4,5,'اختياري (مدفوع)','Optional (paid)',0),(4,6,'99.9%','99.9%',1),(4,7,'تذاكر دعم','Ticket support',1),
(5,1,'50 GB NVMe','50 GB NVMe',1),(5,2,'غير محدودة','Unmetered',1),(5,3,'مجانية','Free',1),(5,4,'غير مضمّن','Not included',0),(5,5,'يومي','Daily',1),(5,6,'99.9%','99.9%',1),(5,7,'دردشة مباشرة','Live chat',1)
ON DUPLICATE KEY UPDATE value_ar = VALUES(value_ar);

INSERT INTO coupons (company_id, code, title_ar, title_en, description_ar, description_en, discount_text, is_active) VALUES
(1, 'HOSTREF75', 'خصم 75% على استضافة Hostinger', '75% off Hostinger hosting', 'خصم إضافي عند الاشتراك بخطة سنتين', 'Extra discount on the 2-year plan', '75% OFF', 1),
(2, 'CHEAPNAME', 'خصم على دومينات Namecheap', 'Namecheap domain discount', 'يشمل دومينات .com الجديدة', 'Applies to new .com domains', 'UP TO 30%', 1),
(5, 'VERPEX70', 'خصم 70% على Verpex', '70% off Verpex', 'لأول فاتورة فقط', 'First invoice only', '70% OFF', 1)
ON DUPLICATE KEY UPDATE code = code;
