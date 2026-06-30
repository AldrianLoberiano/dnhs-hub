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
12. [Tips & Best Practices](#tips--best-practices)

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
2. Use the search bar to find students by name, LRN, or student number (results update instantly as you type)
3. Use the **Status** or **Batch** dropdown to filter (results update instantly)
4. Click the **eye icon** to view full profile

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
3. Click the green **Save** floating action button (bottom-right corner)
4. Or click **Cancel** to go back

### Editing a Student

1. Go to the student's profile
2. Click the **pen icon** or **Edit** button
3. Make your changes
4. Click the green **Save** floating action button (bottom-right corner)

### Archiving a Student

1. Go to the student list
2. Click the **archive icon** next to the student
3. Confirm the action in the styled popup

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
2. Type in the **Student** field to search by name or student number (autocomplete results appear as you type)
3. Select the student from the dropdown
4. Choose the document type
5. Select the file (PDF, JPG, JPEG, PNG - Max 10MB)
6. Click **Upload**

### Viewing Documents

1. Click **Documents** in the sidebar
2. Use the search bar to filter by student name or document type (results update instantly)
3. Use the **Document Type** dropdown to filter (results update instantly)
4. Click the **eye icon** to preview
5. Click the **download icon** to download

### Deleting Documents

1. Go to the documents list
2. Click the **trash icon** next to the document
3. Confirm the deletion in the styled popup

---

## Document Requests

### Creating a Request

1. Click **Requests** → **New Request**
2. Type in the **Student** field to search by name or student number (autocomplete results appear as you type)
3. Select the student from the dropdown
4. Choose the document type
5. Enter the purpose
6. Set expected release date (optional)
7. Click **Submit Request**

The system will generate a tracking number: `DNHS-YYYY-NNNNNN`

### Tracking Request Status

1. Click **Requests** in the sidebar
2. Use the search bar to find by tracking number or student name (results update instantly)
3. Use the **Status** or **Document Type** dropdown to filter (results update instantly)
4. Status badges are color-coded:
   - **Pending** (yellow): Awaiting approval
   - **Approved** (blue): Approved for processing
   - **Processing** (green): Being prepared
   - **Ready for Release** (green): Ready for pickup
   - **Released** (gray): Completed
   - **Rejected** (red): Denied
   - **Cancelled** (dark): Withdrawn

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

### Exporting Reports

1. After generating a report, use the export buttons:
   - **CSV**: Comma-separated values (opens in spreadsheet apps)
   - **Excel**: Microsoft Excel format (.xls)
   - **Word**: Microsoft Word format (.doc)
2. The file will download to your computer

---

## User Management (Admin Only)

### Viewing Users

1. Click **User Management** in the sidebar
2. View all system users with their roles and status
3. Role badges are color-coded:
   - **Administrator** (red): Full system access
   - **Registrar** (blue): Limited access

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
3. Confirm the action in the styled popup

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
2. Select a backup file (.sql)
3. Click **Restore Database**
4. Confirm the action in the styled popup

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
6. **Export reports** for record-keeping and analysis

### For Registrars

1. **Verify student information** before processing requests
2. **Update request status** promptly
3. **Use the claim stub** for document release
4. **Add notes** when updating request status
5. **Check notifications** regularly
6. **Use search and filters** to find records quickly

### General

1. **Log out** when finished (especially on shared computers)
2. **Use the search** to find records quickly (results appear instantly)
3. **Use filters** to narrow down lists
4. **Print claim stubs** for document pickup
5. **Report issues** to the administrator

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

**Version:** 1.3.0
**Last Updated:** July 2026
