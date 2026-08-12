# مراجع الاستضافة / Hosting References

A bilingual (Arabic/English) affiliate hosting-review platform: provider
reviews, a comparison tool, discount coupons, a blog, and a **Proxy
Purchase** service (you buy hosting on behalf of visitors who don't have
an international payment method) — with a full admin dashboard.

Plain **PHP 8 + MySQL/MariaDB**, no Composer, no build step. Built to run
on ordinary shared hosting (cPanel) as-is.

---

## 1. Stack & requirements

- PHP 8.0+ with the `pdo_mysql` extension (standard on virtually every
  cPanel host)
- MySQL 5.7+ or MariaDB 10.3+
- No Composer, no Node build step — just upload the files

---

## 2. Project structure

```
├── index.php, reviews.php, review.php, compare.php, coupons.php,
│   blog.php, post.php, proxy-purchase.php, contact.php, go.php
│                                   ← public site pages
├── includes/                       ← config, DB, helpers, header/footer
├── lang/ar.php, lang/en.php        ← all translatable strings
├── assets/css, assets/js, assets/img
├── uploads/                        ← logos & article cover images (writable)
├── admin/                          ← the admin dashboard (see below)
└── database/schema.sql             ← full DB schema + starter data
```

---

## 3. Deploying to shared hosting (cPanel) via GitHub

### A. Push this project to GitHub

```bash
cd hosting-references
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/<your-username>/<your-repo>.git
git push -u origin main
```

`includes/config.local.php` and everything in `uploads/` are already
excluded via `.gitignore`, so your real DB password and uploaded images
never get pushed.

### B. Create the database in cPanel

1. cPanel → **MySQL® Databases** → create a database (e.g.
   `cpaneluser_hostingref`) and a user, then **add the user to the
   database** with **All Privileges**.
2. cPanel → **phpMyAdmin** → select the new database → **Import** →
   upload `database/schema.sql`. This creates every table and inserts:
   - Starter data for Hostinger, Namecheap, HostPapa, Contabo, Verpex
     (edit their `affiliate_url` values before going live — they're
     placeholders)
   - A default admin login: **admin@example.com / ChangeMe123!**
     (change this immediately after your first login)

### C. Pull the code onto the server

Most cPanel accounts include **Git™ Version Control** (cPanel home →
search "Git"):

1. **Create**, paste your GitHub repo URL, set the repository path to
   something like `/home/cpaneluser/hostingref-src`.
2. cPanel clones the repo there. Since that folder isn't your public
   web root, either:
   - point your subdomain/addon domain's document root directly at
     that cloned folder, **or**
   - use cPanel's **File Manager** to copy the cloned files into
     `public_html` (or `public_html/your-subfolder`).

If your host doesn't offer Git integration, just download the repo as
a ZIP from GitHub and upload/extract it via **File Manager** into
`public_html`.

### D. Configure the database connection

In **File Manager**, duplicate `includes/config.local.php.example` as
`includes/config.local.php` and fill in the real values cPanel gave you
in step B:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cpaneluser_hostingref');
define('DB_USER', 'cpaneluser_hostuser');
define('DB_PASS', 'the-real-password');
define('SITE_URL', 'https://your-domain.com'); // no trailing slash
```

This file is git-ignored, so it's safe to put real credentials in it —
it will never get pushed back to GitHub.

### E. File permissions

Make sure the `uploads/` folder is writable (755 is fine on most cPanel
setups; use File Manager → Permissions if uploads fail).

### F. First login & go live

1. Visit `https://your-domain.com/admin/login.php`, log in with
   `admin@example.com` / `ChangeMe123!`, and immediately go to
   **المسؤولون (Admin users)** to change your password (create a new
   admin account with your own email/password, then delete or disable
   the default one).
2. Go to **شركات الاستضافة (Companies)** and replace every
   `affiliate_url` placeholder (`?ref=REPLACE_ME`) with your real
   affiliate links.
3. Go to **الإعدادات العامة (Settings)** and set your real site name,
   meta description, logo, and social links.
4. Add your own coupons, comparison features, and blog posts.

---

## 4. Local development (optional)

To preview it on your own machine before deploying:

```bash
# import the schema into a local MySQL/MariaDB instance
mysql -u root -e "CREATE DATABASE hostingref_db CHARACTER SET utf8mb4;"
mysql -u root hostingref_db < database/schema.sql

# create includes/config.local.php with your local DB credentials
cp includes/config.local.php.example includes/config.local.php
# edit it, then:

php -S localhost:8000
# visit http://localhost:8000
```

---

## 5. How the pieces fit together

- **Bilingual content**: every table with translatable text has both a
  `_ar` and `_en` column (e.g. `title_ar` / `title_en`). The `field()`
  helper in `includes/functions.php` picks the right one based on the
  active language. All static UI strings live in `lang/ar.php` and
  `lang/en.php` — edit those files to change any label.
- **Affiliate tracking**: every "Get this hosting" / coupon button
  points to `go.php?company=slug&coupon=id`, which logs a row in the
  `clicks` table and then redirects. Coupons redirect to that coupon's
  own **redirect_url** if you set one when editing it in
  **admin/coupons.php** (useful for sending a coupon to a special
  landing page instead of the company's homepage) — leave it empty to
  fall back to the company's default `affiliate_url`. On the public
  site, clicking a coupon's code copies it to the clipboard **and**
  opens its destination in a new tab in one action.
- **Updating an existing install**: if your live database was created
  before the per-coupon redirect link existed, run
  `database/migrations/002_add_coupon_redirect_url.sql` once via
  phpMyAdmin's SQL tab (safe to run twice — it's a no-op if the column
  is already there). Fresh installs via `database/schema.sql` already
  include it.
- **Proxy Purchase**: submissions from `proxy-purchase.php` land in the
  `proxy_requests` table with status `pending`. Manage and update
  status (contacted → paid → delivered) from **admin/proxy_requests.php**.
- **Comparison table**: `feature_keys` defines the rows (e.g. "Disk
  Space", "Free SSL"); `company_features` holds each company's value
  for each row. Manage both from **admin/features.php** — no JSON
  editing required, it's a plain grid.
- **Admin roles**: `super_admin` sees everything, including Settings
  and Admin Users; `editor` can manage content but not site-wide
  settings or other admin accounts.

---

## 6. Security notes

- Change the default admin password immediately (step F above).
- All forms are CSRF-protected and all DB queries use prepared
  statements.
- Never commit `includes/config.local.php` — it's already in
  `.gitignore`.
- Consider enabling HTTPS (most cPanel hosts offer free AutoSSL) before
  going live, since the admin login sends a password over POST.
