# System Features Summary

## ✅ Implemented Features

### 1. **Resume Viewing (Employer Dashboard)**
- Employer can click "Resume" link to view applicant's uploaded resume
- Opens in new tab/window for easy review

### 2. **Business Permit Upload (Company Registration)**
- Required upload during company registration
- Accepts: PDF, JPG, PNG (max 5MB)
- File saved to: `uploads/permits/`

### 3. **Document Review (Admin Dashboard)**
- Admin can see "📄 Review" button for pending employers
- Clicking opens the uploaded business permit
- Allows admin to verify business legitimacy before approval

### 4. **Application Status Tracking**
Status options (in employer dashboard):
- ✅ **Pending** - Initial state when applicant applies
- 📅 **Interview** - Schedule interview date/time (8am-5pm, weekdays only)
- ✅ **Passed** - Applicant passed, sends email notification
- ❌ **Reject** - Application rejected

### 5. **Email Notifications**
**When applicant is marked as "Passed":**
- Auto-sends email: "Congratulations! You Passed the Interview Round"
- Professional HTML template
- Sent to applicant's email address

### 6. **Google Calendar Integration**
**When "Interview" is selected:**
- Requires OAuth authentication (first time only)
- Auto-creates calendar event with:
  - Title: "Interview: [Applicant Name] - [Position]"
  - Time: Selected date & time
  - Duration: 1 hour
  - Attendee: Applicant's email
  - Reminders: 24h email + 30min popup
- Sends calendar invite to applicant
- Applicant gets notification on Google Calendar

**Restrictions:**
- Can only schedule 8:00 AM - 5:00 PM
- Weekdays only (Mon-Fri)
- Cannot schedule past dates

### 7. **Last Login Tracking**
Admin Home tab now shows:
- Applicants: Name | Email | **Last Login** | Action
- Employers: Name | Email | **Last Login** | Action
- Shows "Never" if account never logged in
- Helps admin identify inactive accounts for deactivation

### 8. **Job Search (Applicant Dashboard)**
- Search bar in Companies section
- Type position name (e.g., "Full Stack", "Front-end Developer")
- Filters companies in real-time
- Shows only companies with matching job positions

### 9. **Applicant Application Tracking**
Three tabs:
- 📅 **For Interview** - Applications scheduled for interview with date
- 🔍 **For Review** - Applications pending review (waiting for employer decision)
- ❌ **Rejected** - Applications that were rejected

---

## 🔧 Database Changes Required

Run in phpMyAdmin SQL tab:

```sql
-- Add business_permit column to companies table
ALTER TABLE companies 
ADD COLUMN business_permit VARCHAR(255) DEFAULT NULL;

-- Add last_login column to applicants, companies tables
ALTER TABLE applicants 
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER status;

ALTER TABLE companies 
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER status;
```

---

## 📋 Remaining Setup

### Google Calendar API (for interview notifications)
1. Follow `GOOGLE_CALENDAR_SETUP.md`
2. Get API credentials
3. Replace in `app/views/company/EmployerDashboard.php`:
   ```javascript
   const CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
   const API_KEY = 'YOUR_GOOGLE_API_KEY';
   ```

### Google Maps API (for company registration)
1. Follow `GOOGLE_MAPS_SETUP.md`
2. Get API key
3. Replace in `app/views/company/register.php`:
   ```javascript
   script.src = 'https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places&callback=initMap';
   ```

---

## 📂 Directory Structure

Created:
- `uploads/permits/` - Business permit files from company registration

---

## ✨ User Flow Summary

**Applicant:**
1. Register → Upload resume → Verify email
2. Wait for admin approval
3. After approved → Complete profile → Apply to jobs
4. See applications in dashboard (For Review / For Interview / Rejected tabs)
5. Get email when "Passed" by employer
6. See interview schedule in calendar invite

**Employer:**
1. Register → Upload business permit → Verify email
2. Wait for admin approval & verification
3. After approved → Post jobs
4. See applicants in Pending Applicants list
5. Click resume to review
6. Mark as: Interview (schedule date) / Passed / Reject
7. Interview: Auto-creates Google Calendar event, sends invite
8. Passed: Auto-sends congratulations email
9. Track last login of applicants to monitor engagement

**Admin:**
1. Verify business permit (📄 Review button)
2. Approve companies & applicants
3. Monitor last login to identify inactive accounts
4. Can deactivate accounts if needed

---

## 🔐 Email Credentials (Gmail)

Currently configured for: `robabarintos@gmail.com`
If changing email:
1. Generate Gmail App Password
2. Update in `CompanyController.php`:
   ```php
   $mail->Username = 'newemail@gmail.com';
   $mail->Password = 'app_password_here';
   ```

---

**Last Updated:** November 21, 2025
