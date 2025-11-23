# Email Configuration Troubleshooting

## Current Error
**SMTP Error: Could not authenticate**

This means the Gmail app password is incorrect or has changed.

---

## Solution: Generate New Gmail App Password

### Step 1: Enable 2-Step Verification (if not already done)
- Go to: https://myaccount.google.com
- Click "Security" in the left sidebar
- Scroll to "2-Step Verification"
- Click "Get Started" and follow the prompts

### Step 2: Create App Password
- Go to: https://myaccount.google.com/apppasswords
- Select "Mail" as the app
- Select "Windows Computer" as the device (or your OS)
- Click "Generate"
- Google will show a 16-character password like: `xxxx xxxx xxxx xxxx`

### Step 3: Update the Code
Copy the 16-character password (spaces included) and replace it in **app/controllers/CompanyController.php**:

Find all lines with:
```php
$mail->Password = 'gjpd ixfj zydj xaoc';
```

Replace with your new password (keeping the format with spaces):
```php
$mail->Password = 'your new 16 char password here';
```

There are 3 places to update:
1. Line ~165 (sendVerificationEmail function)
2. Line ~406 (sendInterviewNotificationEmail function)  
3. Line ~355 (sendPassedNotificationEmail function)

---

## Files That Need Updates

1. **app/controllers/CompanyController.php**
   - Line 165: sendVerificationEmail()
   - Line 355: sendPassedNotificationEmail()
   - Line 406: sendInterviewNotificationEmail()

---

## Testing

After updating the password, test by:
1. Login as employer
2. Try to schedule an interview
3. The applicant should receive an email

If still not working:
- Check Gmail spam folder
- Verify app password is exactly correct (copy-paste from Gmail)
- Make sure 2-Step Verification is enabled on the Gmail account

---

## Current Configuration
- Email: robabarintos@gmail.com
- SMTP Server: smtp.gmail.com
- Port: 587
- Security: STARTTLS

