# Google Calendar API Setup Guide

## Steps to Enable Google Calendar Integration

### 1. Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click "Select a project" → "New Project"
3. Name it (e.g., "Job Portal Calendar")
4. Click "Create"

### 2. Enable Google Calendar API
1. In your project, go to **APIs & Services** → **Library**
2. Search for "Google Calendar API"
3. Click on it and press **Enable**

### 3. Create API Key
1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **API Key**
3. Copy the API Key
4. Click **Restrict Key** (recommended):
   - Under "API restrictions", select "Restrict key"
   - Choose "Google Calendar API"
   - Save

### 4. Create OAuth 2.0 Client ID
1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. If prompted, configure OAuth consent screen:
   - User Type: **External**
   - App name: "Job Portal"
   - User support email: your email
   - Developer contact: your email
   - Click **Save and Continue**
   - Scopes: Skip for now
   - Test users: Add your Gmail address
   - Click **Save and Continue**
4. Back to Create OAuth client ID:
   - Application type: **Web application**
   - Name: "Job Portal Web Client"
   - Authorized JavaScript origins:
     - `http://localhost:3000`
     - `http://localhost:4000`
   - Authorized redirect URIs:
     - `http://localhost:3000/LavaLust-Final/index.php/company/employer`
     - `http://localhost:4000/LavaLust-Final/index.php/company/employer`
   - Click **Create**
5. Copy the **Client ID** (format: `xxxxx.apps.googleusercontent.com`)

### 5. Update the Code
Open `app/views/company/EmployerDashboard.php` and replace:

```javascript
const CLIENT_ID = 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com';
const API_KEY = 'YOUR_GOOGLE_API_KEY';
```

With your actual credentials:

```javascript
const CLIENT_ID = '123456789-abcdefg.apps.googleusercontent.com'; // Your Client ID
const API_KEY = 'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXX'; // Your API Key
```

### 6. Test the Integration
1. Login as employer
2. Go to Home tab → Pending Applicants
3. Select "Interview" status
4. Pick a future date/time
5. Click "Update"
6. First time: Google will ask for permission to access your calendar
7. Click "Allow"
8. Calendar event will be created and invitation sent to applicant's email

## Features Implemented

✅ **Past Date Prevention**: Cannot select dates/times before current moment
✅ **Google Calendar Event**: Auto-creates calendar event with applicant details
✅ **Email Invitation**: Sends calendar invite to applicant's email
✅ **Event Details**: 
   - Title: "Interview: [Name] - [Position]"
   - Duration: 1 hour
   - Timezone: Asia/Manila
   - Reminders: Email (24h before) + Popup (30min before)

## Troubleshooting

**Error: "Invalid Client"**
- Make sure authorized JavaScript origins match your URL exactly
- Check CLIENT_ID is copied correctly

**Error: "Access Blocked"**
- Add your Gmail to test users in OAuth consent screen
- If app is in testing mode, only test users can authorize

**No popup appears**
- Check browser is not blocking popups
- Open browser console (F12) for errors

**Event not created**
- Check API_KEY is correct and not restricted to wrong API
- Verify Google Calendar API is enabled in your project
