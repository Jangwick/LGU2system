# 🔐 LRMS Test Account Credentials

## Development Login Accounts

Use these credentials to log into the Legislative Records Management System:

---

### 👨‍💼 Administrator Account
**Email:** `admin@lgu.gov.ph`  
**Password:** `Admin@123`  
**Role:** Administrator  
**Department:** IT Department  
**Access Level:** Full system access, user management, settings, all modules

---

### 👔 Legislative Officer Account
**Email:** `officer@lgu.gov.ph`  
**Password:** `Officer@123`  
**Role:** Officer  
**Department:** Legislative Office  
**Access Level:** Create, edit, approve documents, manage legislative records

---

### 👨‍💻 Staff Member Account
**Email:** `staff@lgu.gov.ph`  
**Password:** `Staff@123`  
**Role:** Staff  
**Department:** Document Management  
**Access Level:** Upload, edit documents, view reports

---

### 👁️ Viewer Account (Read-Only)
**Email:** `viewer@lgu.gov.ph`  
**Password:** `Viewer@123`  
**Role:** Viewer  
**Department:** Public Services  
**Access Level:** View and download documents only

---

## 🌐 Access the System

**Login URL:** `http://localhost/LLRMSystem/LLRMSystem/auth/login.php`

**Steps:**
1. Make sure XAMPP Apache is running
2. Open your browser
3. Go to: http://localhost/LLRMSystem/LLRMSystem/auth/login.php
4. Enter any of the credentials above
5. Click "Sign In"

---

## 📝 Features Available

After logging in, you can:
- ✅ View the dashboard with statistics and charts
- ✅ Upload documents (drag & drop)
- ✅ Search documents with advanced filters
- ✅ View document lists and details
- ✅ Download documents
- ✅ Manage user accounts (Admin only)
- ✅ View activity logs
- ✅ Generate reports

---

## 🔒 Security Notes

- These are **development/test accounts only**
- In production, passwords should be hashed with bcrypt
- Implement proper database authentication
- Add two-factor authentication (2FA)
- Use HTTPS in production
- Implement session timeout
- Add CSRF protection

---

## 🚀 Quick Start

**Recommended first login:** Use the **Administrator** account to explore all features

```
Email: admin@lgu.gov.ph
Password: Admin@123
```

---

**Last Updated:** November 20, 2025
