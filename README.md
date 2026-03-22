# SPD Jobs Inc. — Recruitment & Employee Status Monitoring System
**Bataan Branch | Capstone Project**

---

## 📋 Project Overview

A centralized web-based recruitment and employee status monitoring system for **SPD Jobs Inc. – Bataan Branch**, located at Manalo Village, Palihan, Hermosa, Bataan, Philippines.

Built with: **HTML, CSS, PHP, MySQL (SQL)**

---

## 🗂️ Project Structure

```
spd_jobs/
├── index.php               ← Homepage (hero, job listings, ads)
├── jobs.php                ← Browse all jobs with search & filter
├── job-detail.php          ← Single job detail + requirements + salary
├── apply.php               ← Application form (login required)
├── register.php            ← Applicant sign up
├── login.php               ← Applicant login
├── logout.php              ← Logout handler
├── dashboard.php           ← Applicant dashboard (status timeline, applications)
├── profile.php             ← Applicant profile editor
├── database.sql            ← Full database schema + sample data
│
├── admin/
│   ├── login.php           ← Admin/HR login
│   ├── logout.php          ← Admin logout
│   ├── index.php           ← Admin dashboard (stats + pipeline)
│   ├── applications.php    ← Manage applications (approve/decline/stage)
│   ├── jobs.php            ← CRUD for job posts
│   └── users.php           ← View all registered applicants
│
├── includes/
│   ├── config.php          ← Database config + helper functions
│   ├── header.php          ← Shared navbar + contact bar
│   └── footer.php          ← Shared footer
│
├── assets/
│   ├── css/style.css       ← Full stylesheet (SPD red theme)
│   ├── js/main.js          ← JavaScript helpers
│   └── img/favicon.svg     ← Site favicon
│
└── uploads/                ← Uploaded documents (auto-created)
```

---

## ⚙️ Installation Steps

### 1. Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache (XAMPP / WAMP / LAMP recommended)

### 2. Copy Project Files
Place the `spd_jobs` folder inside your web server root:
- XAMPP: `C:/xampp/htdocs/spd_jobs`
- WAMP: `C:/wamp64/www/spd_jobs`
- Linux: `/var/www/html/spd_jobs`

### 3. Create the Database
1. Open **phpMyAdmin** (http://localhost/phpmyadmin)
2. Click **Import**
3. Select the file `database.sql`
4. Click **Go**

This will create the database `spd_jobs_db` with all tables and sample data.

### 4. Configure Database Connection
Open `includes/config.php` and update:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password
define('DB_NAME', 'spd_jobs_db');

define('SITE_URL', 'http://localhost/spd_jobs');  // Your local URL
```

### 5. Create the Uploads Folder
Make sure the `uploads/` folder exists and is writable:
```bash
mkdir uploads
chmod 755 uploads
```
On XAMPP/WAMP, this is usually automatic.

### 6. Open in Browser
Visit: **http://localhost/spd_jobs**

---

## 🔑 Default Login Credentials

### Admin / HR Login
- URL: http://localhost/spd_jobs/admin/login.php
- Email: `admin@spdjobs.com`
- Password: `password`

### HR Officer Login
- Email: `hr@spdjobs.com`
- Password: `password`

> ⚠️ **Change these passwords immediately** after first login in production!

---

## ✅ Features Included

### Applicant Side
- [x] Sign up and login for applicants
- [x] Browse all available jobs with search & filter
- [x] Job detail page with requirements, salary, and benefits
- [x] Application form per job with document uploads
- [x] Applicant dashboard showing application statuses
- [x] Visual progress timeline (Applied → Exam → Interview → Medical → Final → Orientation → Hired)
- [x] Notifications when status is updated by HR
- [x] Edit profile + government numbers (SSS, PhilHealth, Pag-IBIG, TIN)

### Admin / HR Side
- [x] Separate admin login
- [x] Admin dashboard with stats and application pipeline
- [x] View, filter, and search all applications
- [x] Update application status (Pending, For Exam, For Initial Interview, For Medical, For Final Interview, For Orientation, Approved, Declined)
- [x] Send notes to applicants when updating status
- [x] Full CRUD for job posts (Create, Read, Update, Delete)
- [x] View all registered applicants

### System Features
- [x] Homepage with hero section, ads, in-demand jobs
- [x] Advertisement banners on homepage
- [x] Company contact info on every page
- [x] Application source tracking (Online vs Walk-in)
- [x] Responsive design (mobile-friendly)
- [x] SPD red brand color throughout

---

## 📱 Contact Information (System Default)

- **Address:** Manalo Village, Palihan, Hermosa, Bataan, Philippines
- **Globe:** 0917-621-1262
- **Smart:** 0998-570-8638
- **Sun:** 0925-338-8905

---

## 💡 Additional Improvement Ideas (for future versions)

1. **Email Notifications** — Use PHPMailer to send email when status changes
2. **PDF Export** — Export application forms or reports as PDF
3. **SMS Notifications** — Integrate Semaphore or Globe Labs SMS API
4. **Analytics Charts** — Monthly applicant trend charts for admin
5. **Walk-in Registration** — Admin can directly register walk-in applicants
6. **Employee Monitoring** — Track deployed/hired employees post-hiring
7. **Two-Factor Login** — OTP via SMS for admin security

---

## 👨‍💻 Tech Stack

| Component | Technology |
|-----------|-----------|
| Frontend | HTML5, CSS3 |
| Backend | PHP 7.4+ |
| Database | MySQL (via MySQLi) |
| Fonts | Google Fonts — Plus Jakarta Sans |
| Server | Apache (XAMPP recommended) |

---

*Developed as a Capstone Project for SPD Jobs Inc. Bataan Branch*
