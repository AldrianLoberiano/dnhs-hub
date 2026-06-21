# DNHS Hub

**A Centralized Student Records and Document Request Management System for Dayap National High School**

---

## Overview

DNHS Hub is a production-ready web application designed for the Registrar's Office of Dayap National High School (DNHS). It serves as a centralized repository for student information, academic records, and document requests, streamlining record retrieval, request processing, document issuance, reporting, and audit tracking.

This system is strictly for internal use by authorized personnel (Administrators and Registrars).

---

## Features

### Dashboard
- Summary cards with percentage indicators (month-over-month comparison)
- Quick action links for common tasks
- Monthly Requests chart
- Request Status Breakdown chart
- Most Requested Documents chart
- Recent Requests list

### Student Records
- Complete student profiles (Personal, Guardian, Academic information)
- Add, Edit, View, Archive, Restore students
- Search and filter by name, LRN, status, batch
- Print student profile
- Digital student folder with documents and request history

### Student Documents
- Upload documents (PDF, JPG, JPEG, PNG - Max 10MB)
- Version tracking for uploaded files
- Download, Preview, Delete documents
- Filter by student and document type

### Document Requests
- Auto-generated tracking numbers (DNHS-YYYY-NNNNNN)
- Status workflow: Pending → Approved → Processing → Ready for Release → Released
- QR code verification
- Claim stub generation and printing
- Status history tracking

### Reports
- Daily, Weekly, Monthly, Yearly requests
- Request Status Breakdown
- Most Requested Documents
- Registrar Activity Report

### Audit Trail
- Tracks all system activities (Login, CRUD operations, Document uploads, etc.)
- Filterable by action, module, and date range

### User Management (Admin Only)
- Create, Edit, Deactivate/Activate users
- Reset passwords
- Role assignment (Administrator, Registrar)

### Backup & Restore (Admin Only)
- Database backup creation
- Database restore from backup file
- Backup history with download capability

### Notifications
- In-app notification system
- Popup modal for viewing notification details
- Mark as read functionality

### Security
- Rate limiting on login (5 attempts, 15-minute lockout)
- Password hashing (bcrypt)
- CSRF token protection
- XSS prevention
- SQL injection prevention
- Session timeout

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8+ |
| Database | MySQL 8+ |
| Frontend | HTML5, CSS3, Bootstrap 5, JavaScript ES6 |
| Charts | Chart.js |
| Tables | DataTables |
| Server | Apache (XAMPP/LAMP) |

---

## Project Structure

```
dnhs-hub/
├── assets/
│   ├── css/
│   │   ├── login.css              # Login page styles
│   │   └── style.css              # Main application styles
│   ├── js/
│   │   └── app.js                 # JavaScript functions
│   ├── images/
│   │   ├── school-logo.png        # School logo
│   │   └── school-building.jpg    # School building photo
│   └── uploads/
│       └── documents/             # Uploaded student documents (versioned)
├── config/
│   ├── config.php                 # Application configuration
│   └── database.php               # Database connection
├── database/
│   └── schema.sql                 # MySQL database schema
├── helpers/
│   └── functions.php              # Utility functions
├── includes/
│   ├── header.php                 # Page header & navigation
│   └── footer.php                 # Page footer & scripts
├── audit/
│   └── index.php                  # Audit logs viewer
├── backup/
│   ├── index.php                  # Backup & restore page
│   └── download.php               # Download backup file
├── claims/
│   └── stub.php                   # Claim stub generator
├── documents/
│   ├── index.php                  # Documents list
│   ├── upload.php                 # Upload document form
│   ├── download.php               # Download document
│   ├── preview.php                # Preview document
│   └── delete.php                 # Delete document
├── notifications/
│   ├── index.php                  # Notifications list
│   └── mark_read.php              # Mark notification read (AJAX)
├── reports/
│   └── index.php                  # Reports page
├── requests/
│   ├── index.php                  # Requests list
│   ├── add.php                    # Create new request
│   ├── view.php                   # View request details
│   ├── update_status.php          # Update request status
│   └── verify.php                 # QR code verification
├── students/
│   ├── index.php                  # Students list
│   ├── add.php                    # Add new student
│   ├── edit.php                   # Edit student
│   ├── view.php                   # View student profile
│   ├── archive.php                # Archive student
│   ├── restore.php                # Restore archived student
│   └── archived.php               # Archived students list
├── users/
│   ├── index.php                  # Users list
│   ├── add.php                    # Add new user
│   ├── edit.php                   # Edit user
│   ├── reset_password.php         # Reset user password
│   └── toggle_status.php          # Activate/Deactivate user
├── .gitignore                     # Git ignore rules
├── index.php                      # Root redirect
├── login.php                      # Login page with rate limiting
├── logout.php                     # Logout handler
├── dashboard.php                  # Main dashboard
├── profile.php                    # User profile page
├── README.md                      # Project documentation
└── USER_MANUAL.md                 # User manual
```

---

## Installation

### Prerequisites
- XAMPP, WAMP, or LAMP stack
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache web server

### Steps

1. **Clone or copy the project**
   ```
   Copy the dnhs-hub folder to your web server root:
   - XAMPP: C:\xampp\htdocs\
   - WAMP: C:\wamp\www\
   - LAMP: /var/www/html/
   ```

2. **Create the database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Go to the Import tab
   - Select `database/schema.sql`
   - Click Go to import

3. **Configure database connection**
   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'dnhs_hub');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Configure application URL**
   - Open `config/config.php`
   - Update `APP_URL` to match your setup:
     ```php
     define('APP_URL', 'http://localhost/dnhs-hub');
     ```

5. **Ensure upload directories are writable**
   ```
   Set permissions for:
   - assets/uploads/documents/
   - assets/uploads/profiles/
   ```

6. **Access the application**
   - Open your browser
   - Navigate to: `http://localhost/dnhs-hub/`

---

## Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Administrator | admin | admin123 |
| Registrar | registrar | registrar123 |

> **Important:** Change these passwords after first login!

---

## User Roles & Permissions

### Administrator
- Full system access
- Dashboard
- User Management
- Student Records (CRUD)
- Student Documents (CRUD)
- Document Requests
- Reports
- Audit Logs
- Backup & Restore
- System Settings

### Registrar
- Dashboard
- Student Records (CRUD)
- Student Documents (CRUD)
- Document Requests
- Reports
- **Cannot:** Manage users, access audit logs, backup database

---

## Document Request Workflow

```
Create Request
      ↓
    Pending
      ↓
   Approved
      ↓
  Processing
      ↓
Ready for Release
      ↓
   Released
```

Status can also be: **Rejected** or **Cancelled** at any stage before release.

---

## Tracking Number Format

```
DNHS-2026-000001
│     │    │
│     │    └── Sequential number (6 digits)
│     └── Year
└── School prefix
```

---

## Security Features

- Rate limiting on login (5 attempts, 15-minute lockout)
- Password hashing (bcrypt)
- PDO prepared statements (SQL injection prevention)
- CSRF token protection
- XSS protection (htmlspecialchars)
- Session validation
- Role-based access control
- Secure file upload validation
- File type and size restrictions
- Activity logging
- Automatic session timeout
- HTTP-only and SameSite cookies

---

## Supported Document Types

- SF10 (Secondary Form 10)
- Form 137 (Permanent Academic Record)
- Birth Certificate
- Good Moral Certificate
- Report Card
- Certificate of Enrollment
- Diploma Copy
- Transcript of Records
- Authentication Documents
- Other Registrar Documents

---

## Browser Support

- Google Chrome (latest)
- Mozilla Firefox (latest)
- Microsoft Edge (latest)
- Safari (latest)

---

## User Manual

See [USER_MANUAL.md](USER_MANUAL.md) for detailed instructions on using the system.

---

## Troubleshooting

### Database connection failed
- Ensure MySQL is running
- Verify credentials in `config/database.php`
- Check if `dnhs_hub` database exists

### Upload failed
- Check folder permissions for `assets/uploads/`
- Verify PHP upload settings in `php.ini`:
  - `file_uploads = On`
  - `upload_max_filesize = 10M`
  - `post_max_size = 12M`

### Blank page
- Enable error reporting in `config/config.php`:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```

### Login locked out
- Wait 15 minutes for the lockout to expire
- Or contact an administrator to clear the lockout

---

## License

This project is proprietary software developed for Dayap National High School. Unauthorized distribution or modification is prohibited.

---

## Developer

Developed by the Alumni for the Registrar's Office of Dayap National High School.

---

## Version History

| Version | Date | Description |
|---------|------|-------------|
| 1.0.0 | 2026 | Initial release |
| 1.1.0 | 2026 | UI improvements, security enhancements, notification modals |
