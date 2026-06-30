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
- CSRF token protection (regenerated after each use)
- XSS prevention (htmlspecialchars on all output)
- SQL injection prevention (PDO prepared statements)
- Session timeout (30 minutes)
- Session fixation protection (regenerate ID after login)
- Path traversal protection (realpath validation on file operations)
- File upload MIME type validation
- PHP execution blocked in uploads directory (.htaccess)
- Backup restore SQL statement validation
- Security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy)
- Session cookie security (HttpOnly, SameSite, Strict Mode, Secure when HTTPS)
- Strong password policy (8+ chars, uppercase, lowercase, number)
- POST-based logout with CSRF protection

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
- CSRF token protection (regenerated after successful validation)
- XSS protection (htmlspecialchars via `sanitize()` on all output)
- Session validation and timeout (30 minutes)
- Session fixation protection (`session_regenerate_id` after login)
- Role-based access control
- Path traversal protection (`realpath()` validation on file operations)
- Secure file upload validation (extension + MIME type via `finfo_file()`)
- File type and size restrictions (PDF, JPG, JPEG, PNG - 10MB max)
- PHP execution blocked in uploads directory (`.htaccess`)
- Backup restore SQL validation (blocks DROP DATABASE, GRANT, INTO OUTFILE, etc.)
- Backup file size limit (50MB max)
- Security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy)
- Session cookie security (HttpOnly, SameSite=Lax, Strict Mode, Secure when HTTPS)
- Activity logging (audit trail)

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
| 1.2.0 | 2026 | Security hardening: path traversal fixes, upload validation, session protection, security headers, backup validation, password policy enforcement |

---

## System Checklist

### Authentication & Authorization
- [x] Login with rate limiting (5 attempts / 15 min lockout)
- [x] Password hashing with bcrypt
- [x] Session fixation protection (regenerate ID after login)
- [x] Session timeout (30 minutes inactivity)
- [x] Session strict mode enabled
- [x] Session cookies: HttpOnly, SameSite=Lax, Secure (when HTTPS)
- [x] POST-based logout with CSRF token
- [x] Role-based access control (Admin, Registrar)
- [x] Password policy enforcement (8+ chars, uppercase, lowercase, number)

### Dashboard
- [x] Summary cards with month-over-month percentage change
- [x] Monthly Requests chart
- [x] Request Status Breakdown chart
- [x] Most Requested Documents chart
- [x] Recent Requests list
- [x] Quick action links

### Student Records
- [x] Add new student
- [x] Edit student information
- [x] View student profile
- [x] Archive student record
- [x] Restore archived student
- [x] List archived students
- [x] Search and filter (name, LRN, status, batch)
- [x] Print student profile

### Student Documents
- [x] Upload documents (PDF, JPG, JPEG, PNG - 10MB max)
- [x] MIME type validation (finfo_file)
- [x] PHP execution blocked in uploads directory
- [x] Version tracking for uploaded files
- [x] Download documents
- [x] Preview documents
- [x] Delete documents
- [x] Path traversal protection (realpath validation)
- [x] Filter by student and document type

### Document Requests
- [x] Create new request
- [x] Auto-generated tracking numbers (DNHS-YYYY-NNNNNN)
- [x] Status workflow: Pending → Approved → Processing → Ready for Release → Released
- [x] Status can be Rejected or Cancelled
- [x] QR code verification (public page)
- [x] Claim stub generation and printing
- [x] Status history tracking

### Reports
- [x] Daily, Weekly, Monthly, Yearly requests
- [x] Request Status Breakdown
- [x] Most Requested Documents
- [x] Registrar Activity Report

### Audit Trail
- [x] Tracks all system activities
- [x] Filterable by action, module, and date range
- [x] IP address logging

### User Management (Admin Only)
- [x] Create new user
- [x] Edit user information
- [x] Activate/Deactivate users
- [x] Reset passwords
- [x] Role assignment (Administrator, Registrar)
- [x] Strong password policy enforcement

### Backup & Restore (Admin Only)
- [x] Database backup creation
- [x] Database restore from backup file
- [x] SQL statement validation (blocks destructive operations)
- [x] Backup file size limit (50MB)
- [x] Backup history with download capability

### Notifications
- [x] In-app notification system
- [x] Popup modal for viewing notification details
- [x] Mark as read functionality
- [x] Unread count badge

### Security
- [x] SQL injection prevention (PDO prepared statements)
- [x] XSS prevention (htmlspecialchars on all output)
- [x] CSRF token protection (regenerated after use)
- [x] Path traversal protection (realpath validation)
- [x] File upload validation (extension + MIME type)
- [x] PHP execution blocked in uploads (.htaccess)
- [x] Backup restore SQL validation
- [x] Security headers (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy)
- [x] Session cookie security (HttpOnly, SameSite, Strict Mode)
- [x] Content-Disposition header escaping
- [x] $pageTitle escaping in HTML output
- [x] Notification URL escaping
- [x] Login error message anonymization (anti-enumeration)

### UI/UX
- [x] Responsive design (Bootstrap 5)
- [x] Sidebar navigation with role-based visibility
- [x] Top navigation bar with search, notifications, user menu
- [x] Toast notifications for flash messages
- [x] Confirmation modals for destructive actions
- [x] DataTables for sortable/searchable tables
- [x] Print-friendly student profiles
