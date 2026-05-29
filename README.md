# JBH Hostel Management System
**Junior Boys Hostel – Dayalbagh Educational Institute, Agra**

A complete hostel management and information website with student portal, admin panel, and online mess bill payment.

---

## 🚀 Quick Start

### 1. Requirements
- PHP 7.4+ (with PDO, mysqli)
- MySQL 5.7+ or MariaDB
- Web server (XAMPP, WAMP, or Apache)

### 2. Database Setup
1. Create MySQL database
2. Import `database/hostel.sql` in phpMyAdmin or:
   ```bash
   mysql -u root -p < database/hostel.sql
   ```

### 3. Configuration
Edit `config/database.php` with your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'jbh_hostel');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Run
- Place project in `htdocs` (XAMPP) or `www` (WAMP)
- Visit `http://localhost/your-folder/`

---

## 🔐 Demo Credentials

| Role   | Username / ID      | Password  |
|--------|--------------------|-----------|
| Student| DEI-2K23-CS-042    | password  |
| Admin  | admin              | password  |
| Warden | warden             | password  |

**⚠️ Change default passwords before production!**

---

## 📁 Project Structure

```
├── index.html          # Public homepage
├── login.php           # Login page (Student/Admin/Warden)
├── config/
│   ├── database.php    # DB connection
│   └── razorpay.php    # Payment gateway config
├── includes/
│   └── auth.php        # Session & auth helpers
├── api/
│   ├── login.php       # Auth API
│   ├── logout.php
│   ├── contact.php     # Contact form handler
│   ├── leave.php       # Leave application
│   ├── complaint.php   # Submit complaint
│   └── verify-payment.php
├── dashboard/
│   ├── student.php     # Student dashboard
│   ├── profile.php
│   ├── mess-menu.php
│   ├── mess-bills.php
│   ├── pay.php         # Razorpay payment page
│   ├── complaints.php
│   ├── leave.php
│   ├── notices.php
│   ├── admin.php       # Admin dashboard
│   ├── admin-notices.php
│   ├── admin-complaints.php
│   ├── admin-students.php
│   ├── admin-mess.php
│   └── admin-leave.php
└── database/
    └── hostel.sql      # Schema + seed data
```

---

## 💳 Razorpay Setup (Mess Bill Payment)

1. Sign up at [razorpay.com](https://razorpay.com)
2. Get **Test** keys from Dashboard → API Keys
3. Edit `config/razorpay.php`:
   ```php
   define('RAZORPAY_KEY_ID', 'rzp_test_xxxx');
   define('RAZORPAY_KEY_SECRET', 'your_secret');
   ```
4. For full integration, install: `composer require razorpay/razorpay`
5. Create `api/create-order.php` to generate orders before payment

---

## ✨ Features

### Public
- Home, About, Facilities, Rules
- Seat Availability
- Photo Gallery
- Notices & Contact Form

### Student Portal
- Profile, Mess Menu, Mess Bills
- Online Payment (Razorpay)
- Complaints / Maintenance
- Leave Application
- Notices

### Admin / Warden
- Add Notices
- Manage Complaints
- Add Students
- Approve Leave
- Mess Bills Overview

---

## 📄 License
Academic project for B.Sc CS – Dayalbagh Educational Institute.
