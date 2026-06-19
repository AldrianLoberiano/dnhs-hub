# DNHS Hub - User Manual

**Student Records and Document Request Management System**
**Dayap National High School**

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard](#dashboard)
3. [Student Records](#student-records)
4. [Document Management](#document-management)
5. [Document Requests](#document-requests)
6. [Reports](#reports)
7. [User Management](#user-management)
8. [Notifications](#notifications)
9. [Profile Settings](#profile-settings)
10. [Audit Trail](#audit-trail)
11. [Backup & Restore](#backup--restore)

---

## Getting Started

### Logging In

1. Open your browser and navigate to the DNHS Hub URL
2. Enter your **Username** and **Password**
3. Click **Sign In**

> **Note:** After 5 failed login attempts, your account will be locked for 15 minutes for security purposes.

### Navigation

- **Sidebar** (left side): Access all modules
- **Top Navbar**: Search, notifications, and user menu
- **Dashboard**: Overview of all system data

---

## Dashboard

The dashboard provides a quick overview of your school's records.

### Quick Stats Cards
- **Total Students**: Number of active students
- **Total Requests**: All document requests
- **Pending**: Requests awaiting approval
- **Processing**: Requests being prepared
- **Ready for Release**: Documents ready for pickup
- **Released**: Completed requests
- **Uploaded Documents**: Total documents on file
- **Active Users**: System users (Admin only)

### Percentage Indicators
- **Green arrow (↑)**: Increase compared to previous month
- **Red arrow (↓)**: Decrease compared to previous month

### Quick Actions
- Add Student
- New Request
- Upload Document
- View Students
- View Requests
- View Documents
- Reports

### Charts
- **Monthly Requests**: Line chart showing request trends
- **Request Status**: Doughnut chart of status breakdown
- **Most Requested Documents**: Bar chart of top documents
- **Recent Requests**: Table of latest requests

---

## Student Records

### Viewing Students

1. Click **Students** in the sidebar
2. Use the search bar to find students by name, LRN, or student number
3. Click the **eye icon** to view full profile

### Adding a New Student

1. Click **Students** → **Add Student**
2. Fill in the required fields:
   - Student Number (unique)
   - LRN (Learner Reference Number)
   - First Name, Last Name, Middle Name
   - Date of Birth, Gender
   - Contact Number, Email
   - Grade Level, Section
   - Guardian Information
3. Click **Save Student**

### Editing a Student

1. Go to the student's profile
2. Click the **pen icon** or **Edit** button
3. Make your changes
4. Click **Update Student**

### Archiving a Student

1. Go to the student list
2. Click the **archive icon** next to the student
3. Confirm the action

> **Note:** Archived students are hidden from the main list but can be restored.

### Restoring a Student

1. Click **Archived Students** in the sidebar
2. Find the student
3. Click the **restore icon**
4. Confirm the action

### Printing Student Profile

1. Go to the student's profile page
2. Click the **Print** button
3. Select your printer and print

---

## Document Management

### Uploading Documents

1. Click **Documents** → **Upload Document**
2. Select the student
3. Choose the document type
4. Select the file (PDF, JPG, JPEG, PNG - Max 10MB)
5. Click **Upload**

### Viewing Documents

1. Click **Documents** in the sidebar
2. Filter by student or document type
3. Click the **eye icon** to preview
4. Click the **download icon** to download

### Deleting Documents

1. Go to the documents list
2. Click the **trash icon** next to the document
3. Confirm the deletion

---

## Document Requests

### Creating a Request

1. Click **Requests** → **New Request**
2. Select the student
3. Choose the document type
4. Enter the purpose
5. Set expected release date (optional)
6. Click **Submit Request**

The system will generate a tracking number: `DNHS-YYYY-NNNNNN`

### Tracking Number Format

```
DNHS-2026-000001
│     │    │
│     │    └── Sequential number (6 digits)
│     └── Year
└── School prefix
```

### Request Status Workflow

```
Create Request → Pending → Approved → Processing → Ready for Release → Released
```

At any stage before release, a request can be:
- **Rejected**: Request denied
- **Cancelled**: Request withdrawn

### Updating Request Status

1. Go to the request details page
2. Click **Update Status**
3. Select the new status
4. Add notes (optional)
5. Click **Update**

### Generating Claim Stub

1. Go to the request details
2. Click **Print Claim Stub**
3. The stub includes:
   - Tracking number
   - Student information
   - Document requested
   - QR code for verification

### Verifying a Request

1. Scan the QR code on the claim stub
2. Or visit: `your-url/requests/verify.php?tracking=DNHS-YYYY-NNNNNN`
3. Enter the tracking number to verify

---

## Reports

### Available Reports

1. **Daily Requests**: Today's request count
2. **Weekly Requests**: This week's requests
3. **Monthly Requests**: This month's requests
4. **Yearly Requests**: This year's requests
5. **Status Breakdown**: Requests by status
6. **Top Documents**: Most requested document types
7. **Registrar Activity**: Activity per registrar

### Generating Reports

1. Click **Reports** in the sidebar
2. Select the report type
3. Set date range if applicable
4. Click **Generate Report**
5. Click **Print** to print the report

---

## User Management (Admin Only)

### Viewing Users

1. Click **User Management** in the sidebar
2. View all system users with their roles and status

### Adding a User

1. Click **Add User**
2. Fill in the required fields:
   - Username (unique)
   - Password (min 6 characters)
   - First Name, Last Name
   - Email
   - Role (Administrator or Registrar)
3. Click **Create User**

### Editing a User

1. Go to the user list
2. Click the **pen icon**
3. Make changes
4. Click **Update User**

### Resetting a Password

1. Go to the user list
2. Click the **key icon**
3. Enter the new password
4. Confirm the password
5. Click **Reset Password**

### Activating/Deactivating a User

1. Go to the user list
2. Click the **activate/deactivate icon**
3. Confirm the action

---

## Notifications

### Viewing Notifications

1. Click the **bell icon** in the top navbar
2. View recent notifications in the dropdown
3. Click a notification to view details

### Marking as Read

- Click a notification to mark it as read
- The badge count will update automatically

### Viewing All Notifications

1. Click the **bell icon**
2. Click **View All** at the bottom
3. Or click **Notifications** in the sidebar

---

## Profile Settings

### Viewing Your Profile

1. Click your name in the top navbar
2. Click **Profile**

### Changing Your Password

1. Go to your profile
2. Scroll to the password section
3. Enter your current password
4. Enter the new password
5. Confirm the new password
6. Click **Change Password**

---

## Audit Trail (Admin Only)

### Viewing Audit Logs

1. Click **Audit Logs** in the sidebar
2. View all system activities

### Filtering Logs

1. Use the filter options:
   - Action type
   - Module
   - Date range
   - User
2. Click **Filter**

### What is Logged

- User logins and logouts
- Student record changes
- Document uploads and deletions
- Request status changes
- User management actions
- Backup and restore operations

---

## Backup & Restore (Admin Only)

### Creating a Backup

1. Click **Backup & Restore** in the sidebar
2. Click **Create Backup**
3. The backup file will be generated
4. Click **Download** to save the file

### Restoring from Backup

1. Go to **Backup & Restore**
2. Select a backup file
3. Click **Restore**
4. Confirm the action

> **Warning:** Restoring a backup will replace all current data.

### Downloading Backups

1. Go to **Backup & Restore**
2. Find the backup in the list
3. Click the **download icon**

---

## Tips & Best Practices

### For Administrators

1. **Change default passwords** immediately after setup
2. **Create backups regularly** (daily recommended)
3. **Review audit logs** periodically
4. **Deactivate unused accounts** promptly
5. **Keep student records updated**

### For Registrars

1. **Verify student information** before processing requests
2. **Update request status** promptly
3. **Use the claim stub** for document release
4. **Add notes** when updating request status
5. **Check notifications** regularly

### General

1. **Log out** when finished (especially on shared computers)
2. **Use the search** to find records quickly
3. **Print claim stubs** for document pickup
4. **Report issues** to the administrator

---

## Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Search | Click search bar |
| Print | Ctrl + P |
| Close Modal | Escape |

---

## Support

For technical support or issues, contact your system administrator.

---

**Version:** 1.0.0
**Last Updated:** 2026
