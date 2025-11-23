<?php
// Employer Dashboard view
// Tabs: Home (pending applicants), Settings (company), Post Job, Logout

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'home';

// Fallback sample data


function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function display_name($r){ return h(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: ($r['email'] ?? '')); }

// Determine if company is approved by admin (use status field set by approval routine)
$employerApproved = isset($company['status']) && $company['status'] === 'approved';
?>
<!DOCTYPE html>
<html lang="en">
  <title>Employer Dashboard</title>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employer Dashboard - Job Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg-main:#f8fafc;
      --bg-card:#ffffff;
      --text-primary:#0f172a;
      --text-secondary:#475569;
      --text-muted:#94a3b8;
      --primary:#3b82f6;
      --primary-hover:#2563eb;
      --primary-light:#dbeafe;
      --border:#e2e8f0;
      --border-hover:#cbd5e1;
      --success:#10b981;
      --danger:#ef4444;
      --warning:#f59e0b;
      --sidebar-bg:#1e293b;
      --sidebar-text:#cbd5e1;
      --sidebar-hover:#334155;
      --shadow-sm:0 1px 2px 0 rgba(0,0,0,.05);
      --shadow-md:0 4px 6px -1px rgba(0,0,0,.1);
      --shadow-lg:0 10px 15px -3px rgba(0,0,0,.1);
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{
      font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
      background:var(--bg-main);
      color:var(--text-primary);
      font-size:14px;
      line-height:1.6;
    }
    .layout{display:flex;min-height:100vh}
    
    /* Sidebar */
    .sidebar{
      width:260px;
      background:var(--sidebar-bg);
      color:var(--sidebar-text);
      display:flex;
      flex-direction:column;
      padding:24px 16px;
      box-shadow:var(--shadow-lg);
    }
    .brand{
      color:#fff;
      font-weight:700;
      font-size:18px;
      margin-bottom:32px;
      padding:0 12px;
      letter-spacing:-0.02em;
    }
    .brand span{color:var(--primary);font-size:20px}
    .nav-list{display:flex;flex-direction:column;gap:4px;margin-bottom:20px}
    .nav-link{
      display:flex;
      align-items:center;
      gap:12px;
      color:var(--sidebar-text);
      text-decoration:none;
      padding:12px 14px;
      border-radius:10px;
      transition:all .2s;
      font-weight:500;
      font-size:14px;
    }
    .nav-link:hover{background:var(--sidebar-hover);color:#fff}
    .nav-link.active{background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(59,130,246,.3)}
    .logout{margin-top:auto}
    
    /* Content Area */
    .content{flex:1;padding:32px 40px;overflow-y:auto}
    h1{
      font-size:28px;
      font-weight:700;
      margin-bottom:8px;
      color:var(--text-primary);
      letter-spacing:-0.02em;
    }
    .subtitle{color:var(--text-secondary);font-size:15px;margin-bottom:28px}
    .container{max-width:1240px}
    
    /* Panel/Card */
    .panel{
      background:var(--bg-card);
      border:1px solid var(--border);
      border-radius:12px;
      padding:24px;
      margin-bottom:24px;
      box-shadow:var(--shadow-sm);
    }
    .panel h2{font-size:20px;font-weight:600;margin-bottom:20px;color:var(--text-primary)}
    .panel h3{font-size:18px;font-weight:600;margin-bottom:16px;color:var(--text-primary)}
    
    /* Tables */
    table{width:100%;border-collapse:separate;border-spacing:0}
    th,td{
      padding:14px 16px;
      text-align:left;
      font-size:13px;
      border-bottom:1px solid var(--border);
    }
    th{
      background:#f8fafc;
      font-weight:600;
      color:var(--text-secondary);
      text-transform:uppercase;
      font-size:11px;
      letter-spacing:0.05em;
      position:sticky;
      top:0;
    }
    tr:last-child td{border-bottom:none}
    tbody tr{transition:background .15s}
    tbody tr:hover{background:#f8fafc}
    
    /* Table Actions/Links */
    td.actions a,
    table a{
      color:var(--primary);
      text-decoration:none;
      font-weight:500;
      transition:color .2s;
    }
    td.actions a:hover,
    table a:hover{
      color:var(--primary-hover);
      text-decoration:underline;
    }
    
    /* Alert/Note */
    .note{
      padding:14px 16px;
      border-radius:10px;
      background:#fef3c7;
      border:1px solid,#fde047;
      color:#713f12;
      font-size:13px;
      margin-bottom:20px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .note::before{content:'⚠️';font-size:18px}
    
    /* Settings Navigation */
    .settings-nav{
      background:#f8fafc;
      border:1px solid var(--border);
      padding:12px 16px;
      border-radius:10px;
      cursor:pointer;
      width:100%;
      text-align:left;
      font-weight:500;
      font-size:14px;
      color:var(--text-primary);
      transition:all .2s;
      margin-bottom:4px;
    }
    .settings-nav:hover{background:#f1f5f9;border-color:var(--border-hover)}
    .settings-nav.active{
      background:var(--primary);
      color:#fff;
      border-color:var(--primary);
      box-shadow:0 2px 8px rgba(59,130,246,.2);
    }
    .settings-panel{padding:8px 0}
    
    /* Forms - Corporate Styling */
    .corp-form{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
      gap:20px;
      margin-top:16px;
    }
    .corp-form .full{grid-column:1/-1}
    .form-group{margin-bottom:6px}
    .form-group label{
      display:block;
      font-size:13px;
      font-weight:600;
      color:var(--text-primary);
      margin-bottom:8px;
    }
    .form-group input[type=text],
    .form-group input[type=email],
    .form-group input[type=password],
    .form-group input[type=url],
    .form-group textarea{
      width:100%;
      padding:11px 14px;
      font-size:14px;
      line-height:1.5;
      border:1.5px solid var(--border);
      border-radius:8px;
      background:#fff;
      transition:all .2s;
      font-family:inherit;
      color:var(--text-primary);
    }
    .form-group input:focus,
    .form-group textarea:focus{
      outline:none;
      border-color:var(--primary);
      box-shadow:0 0 0 3px var(--primary-light);
    }
    .form-group input::placeholder,
    .form-group textarea::placeholder{
      color:var(--text-muted);
    }
    .form-group textarea{resize:vertical;min-height:90px}
    
    /* Logo Upload */
    .logo-box{
      width:96px;
      height:96px;
      border-radius:12px;
      border:2px dashed var(--border);
      display:flex;
      align-items:center;
      justify-content:center;
      background:#f8fafc;
      position:relative;
      overflow:hidden;
      cursor:pointer;
      transition:all .2s;
    }
    .logo-box:hover{border-color:var(--primary);background:#f0f9ff}
    .logo-box img{width:100%;height:100%;object-fit:cover;border-radius:10px}
    
    /* Buttons */
    .btn-primary{
      background:var(--primary);
      color:#fff;
      border:none;
      padding:11px 24px;
      border-radius:8px;
      font-size:14px;
      font-weight:600;
      cursor:pointer;
      transition:all .2s;
      box-shadow:var(--shadow-sm);
    }
    .btn-primary:hover{
      background:var(--primary-hover);
      box-shadow:var(--shadow-md);
      transform:translateY(-1px);
    }
    .btn-primary:active{transform:translateY(0)}
    
    .btn-danger{
      background:var(--danger);
      color:#fff;
      border:none;
      padding:8px 16px;
      border-radius:8px;
      font-size:13px;
      font-weight:500;
      cursor:pointer;
      transition:all .2s;
    }
    .btn-danger:hover{filter:brightness(0.92)}
    
    .btn-secondary{
      background:#f1f5f9;
      color:var(--text-primary);
      border:1px solid var(--border);
      padding:10px 20px;
      border-radius:8px;
      font-size:14px;
      font-weight:500;
      cursor:pointer;
      transition:all .2s;
    }
    .btn-secondary:hover{background:#e2e8f0}
    
    /* Status Form */
    .status-form{display:inline-flex;gap:8px;align-items:center}
    .status-form button{
      background:var(--primary);
      color:#fff;
      border:none;
      padding:7px 14px;
      border-radius:7px;
      font-size:12px;
      font-weight:500;
      cursor:pointer;
      transition:all .2s;
    }
    .status-form button:hover{background:var(--primary-hover)}
    .status-form select,
    .status-form input[type=datetime-local]{
      padding:7px 10px;
      border:1.5px solid var(--border);
      border-radius:7px;
      font-size:13px;
      background:#fff;
      font-family:inherit;
    }
    .status-form select:focus,
    .status-form input:focus{
      outline:none;
      border-color:var(--primary);
    }
    
    /* Inline hints */
    .inline-hint{
      font-size:12px;
      color:var(--text-muted);
      margin-top:6px;
      line-height:1.4;
    }
    
    /* Utility */
    .muted{color:var(--text-muted)}
    .text-sm{font-size:13px}
    .mb-0{margin-bottom:0}
    
    /* Status Badges */
    .badge{
      display:inline-block;
      padding:4px 10px;
      border-radius:12px;
      font-size:11px;
      font-weight:600;
      text-transform:uppercase;
      letter-spacing:0.03em;
    }
    .badge-pending{background:#fef3c7;color:#92400e}
    .badge-interview{background:#dbeafe;color:#1e40af}
    .badge-passed{background:#d1fae5;color:#065f46}
    .badge-rejected{background:#fee2e2;color:#991b1b}
    
    @media (max-width:980px){
      .sidebar{width:220px}
      .content{padding:24px 20px}
    }
  </style>
  <script src="https://apis.google.com/js/api.js"></script>
  <script>
    function setActiveLink(){
      const params = new URLSearchParams(location.search);
      const tab = params.get('tab') || 'home';
      document.querySelectorAll('.nav-link').forEach(a=>{
        a.classList.toggle('active', a.dataset.tab === tab);
      });
    }
    document.addEventListener('DOMContentLoaded', setActiveLink);

    // Google Calendar Integration
    const CLIENT_ID = '1064821785950-f68i3up7vmq0m73ondq0v0r7n3q9rk8b.apps.googleusercontent.com';
    const API_KEY = 'AIzaSyBy4IeCfJCcZaSNdc4WCgpsTGikhTkSZAE';
    const DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];
    const SCOPES = "https://www.googleapis.com/auth/calendar.events";

    let tokenClient;
    // Google Calendar Integration - Simplified for email-based notifications
    let gapiInited = false;
    let gisInited = false;

    function gapiLoaded() {
      gapiInited = true;
      console.log('Google Calendar API libraries loaded');
    }

    function gisLoaded() {
      gisInited = true;
      console.log('Google Identity Services loaded');
    }

    function handleAuthClick(applicantName, applicantEmail, position, scheduleDate, form) {
      // Simplified: Just show confirmation and submit form
      // Email notification will be sent by backend
      const dateObj = new Date(scheduleDate);
      const formattedDate = dateObj.toLocaleString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit',
        timeZone: 'Asia/Manila'
      });
      
      const msg = `Interview scheduled:\n\nApplicant: ${applicantName}\nEmail: ${applicantEmail}\nPosition: ${position}\nDate & Time: ${formattedDate} (Manila Time)\n\nNotification email will be sent to the applicant.`;
      alert(msg);
      
      // Submit form to save the interview
      if (form) {
        form.submit();
      }
    }

    // Set minimum date to today
    function setMinDate() {
      const now = new Date();
      // Format: YYYY-MM-DDTHH:MM
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
      
      document.querySelectorAll('.schedule-date').forEach(input => {
        input.setAttribute('min', minDateTime);
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      setMinDate();
    });
  </script>
  <script src="https://apis.google.com/js/api.js"></script>
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">Job Portal</div>
    <nav class="nav-list">
      <a class="nav-link" data-tab="home" href="?tab=home">Dashboard</a>
      <a class="nav-link" data-tab="settings" href="?tab=settings">Settings</a>
      <a class="nav-link" data-tab="post" href="?tab=post">Post Job</a>
      <a class="nav-link" data-tab="reviews" href="?tab=reviews">Reviews</a>
    </nav>
    <button id="logoutBtn" class="nav-link logout" style="text-align:left; background:transparent; border:none; cursor:pointer;">Logout</button>
  </aside>
  <main class="content">
    <div class="container">
      <div style="margin-bottom:32px">
        <h1>Employer Dashboard</h1>
        <p class="subtitle">Manage your job postings and applicant pipeline</p>
      </div>
      <script>setActiveLink();</script>

    <?php if($tab==='home'): ?>
    <?php if (!$employerApproved): ?>
      <div class="panel note">Your account is not yet approved by admin. You cannot post jobs until approved.</div>
    <?php endif; ?>

    <div class="panel">
      <h2>Pending Applicants</h2>
      <table>
        <thead><tr><th>Name</th><th>Position</th><th>Action</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($pendingApplications as $p): ?>
          <tr>
            <td><?php echo display_name($p); ?></td>
            <td><?php echo h($p['job_title'] ?? ($p['position'] ?? '')); ?></td>
            <td class="actions">
              <a href="#">View</a>
              &nbsp;|&nbsp;
              <?php $resumePath = $p['resume'] ?? ''; $resumeUrl = (strpos($resumePath,'uploads/')===0 ? base_url().$resumePath : $resumePath); ?>
              <a href="<?php echo h($resumeUrl ?: '#'); ?>" target="_blank">Resume</a>
            </td>
            <td>
              <form method="post" action="<?php echo isset($this) ? site_url('company/update_application') : '/company/update_application'; ?>" class="status-form" onsubmit="return handleScheduleSubmit(this, event)">
                <input type="hidden" name="id" value="<?php echo h($p['id']); ?>">
                <input type="hidden" name="applicant_name" value="<?php echo display_name($p); ?>">
                <input type="hidden" name="applicant_email" value="<?php echo h($p['applicant_email'] ?? $p['email'] ?? ''); ?>">
                <input type="hidden" name="position" value="<?php echo h($p['job_title'] ?? ($p['position'] ?? '')); ?>">
                <select name="status" class="status-select">
                  <option value="pending" <?php if(($p['status'] ?? '')==='pending') echo 'selected'; ?>>Pending</option>
                  <option value="interview">Interview</option>
                  <option value="reject">Reject</option>
                </select>
                <input type="datetime-local" name="schedule_date" class="schedule-date" style="display:none;margin-left:8px" required>
                <button type="submit">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <h2>Interview Schedule</h2>
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Position</th><th>Date</th><th>Passed?</th></tr></thead>
        <tbody>
        <?php $hasInterviewRows = false; ?>
        <?php foreach($interviews as $i): ?>
          <?php if(empty($i['schedule_date'])) continue; // hide entries without a scheduled date ?>
          <?php $hasInterviewRows = true; ?>
          <tr>
            <td><?php echo display_name($i); ?></td>
            <td><?php echo h($i['email'] ?? ''); ?></td>
            <td><?php echo h($i['job_title'] ?? ($i['position'] ?? '')); ?></td>
            <td><?php echo h($i['schedule_date']); ?></td>
            <td>
              <form method="post" action="<?php echo isset($this) ? site_url('company/update_application') : '/company/update_application'; ?>" style="display:inline" class="interview-result-form">
                <input type="hidden" name="id" value="<?php echo h($i['id']); ?>">
                <input type="hidden" name="applicant_email" value="<?php echo h($i['email'] ?? ''); ?>">
                <input type="hidden" name="position" value="<?php echo h($i['job_title'] ?? ($i['position'] ?? '')); ?>">
                <input type="hidden" name="schedule_date" value="<?php echo h($i['schedule_date'] ?? ''); ?>">
                <select name="status" class="interview-result-select">
                  <option value="interview" <?php if(($i['status'] ?? '')==='interview') echo 'selected'; ?>>Interview</option>
                  <option value="passed">Passed</option>
                  <option value="reject">Rejected</option>
                </select>
                <button type="submit">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$hasInterviewRows): ?>
          <tr><td colspan="5" class="muted">No interview schedules yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php elseif($tab==='settings'): ?>
    <div class="panel">
      <div style="display:flex;gap:12px">
        <nav style="min-width:180px;border-right:1px solid #e9e9e9;padding-right:12px">
          <ul style="list-style:none;padding:0;margin:0">
            <li><button type="button" class="settings-nav" data-target="profile">Company Details</button></li>
            <li style="margin-top:8px"><button type="button" class="settings-nav" data-target="password">Change Password</button></li>
          </ul>
        </nav>

        <section style="flex:1">
          <div id="settings-profile" class="settings-panel">
            <h2>Company Details</h2>
            <?php $incomplete = empty($company['website']) || empty($company['story']) || empty($company['mission']) || empty($company['vision']) || empty($company['avatar']); ?>
            <?php if($incomplete): ?>
              <div class="note">Complete company setup: please add logo, story, mission, vision and website.</div>
            <?php endif; ?>

            <form method="post" action="<?php echo isset($this) ? site_url('company/update_profile') : '/company/update_profile'; ?>" enctype="multipart/form-data" style="margin-top:10px">
              <div class="corp-form">
                <div class="form-group full" style="display:flex;flex-direction:column;align-items:flex-start;gap:8px">
                  <label style="margin:0">Current Logo</label>
                  <div class="logo-box">
                    <?php if(!empty($company['avatar'])): ?>
                      <img src="<?php echo base_url() . 'uploads/logos/' . rawurlencode($company['avatar']); ?>" alt="Logo">
                    <?php else: ?>
                      <span style="font-size:11px;color:var(--muted);text-align:center;padding:4px">No Logo</span>
                    <?php endif; ?>
                  </div>
                  <div style="margin-top:4px">
                    <input type="file" name="avatar" accept="image/*" style="font-size:12px">
                    <div class="inline-hint">Recommended 1:1 ratio PNG/JPG, max 2MB.</div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Company Name</label>
                  <input type="text" name="company_name" value="<?php echo h($company['company_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                  <label>Website</label>
                  <input type="text" name="website" placeholder="https://example.com" value="<?php echo h($company['website'] ?? ''); ?>">
                </div>
                <div class="form-group full">
                  <label>Story</label>
                  <textarea name="story" rows="5" placeholder="Brief background, when founded, key achievements."><?php echo h($company['story'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                  <label>Mission</label>
                  <textarea name="mission" rows="4" placeholder="What is your purpose?"><?php echo h($company['mission'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                  <label>Vision</label>
                  <textarea name="vision" rows="4" placeholder="Where do you aspire to be?"><?php echo h($company['vision'] ?? ''); ?></textarea>
                </div>
              </div>
              <div style="margin-top:10px;text-align:right">
                <button type="submit" class="btn-primary">Save Company Profile</button>
              </div>
            </form>
          </div>

          <div id="settings-password" class="settings-panel" style="display:none">
            <h3>Change Password</h3>
            <form method="post" action="<?php echo isset($this) ? site_url('company/change_password') : '/company/change_password'; ?>" style="max-width:420px" class="corp-form" id="empPwdForm">
              <div class="form-group full">
                <label>Current Password</label>
                <input type="password" name="current_password" id="empCurrentPassword" required>
              </div>
              <div class="form-group full">
                <label>New Password</label>
                <input type="password" name="new_password" id="empNewPassword" required>
              </div>
              <div class="form-group full">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" id="empConfirmPassword" required>
              </div>
              <div id="empPwdHint" style="grid-column:1/-1;display:none;font-size:12px;color:#dc2626;margin-bottom:8px;">Password must be at least 8 characters.</div>
              <div class="form-group full" style="text-align:right;margin-top:4px">
                <button type="submit" class="btn-primary">Change Password</button>
              </div>
            </form>
          </div>

        </section>
      </div>
    </div>

  <?php elseif($tab==='post'): ?>
    <div class="panel">
      <h2>Post Job Hiring</h2>
      <?php if (!$employerApproved): ?>
        <div class="note">Your account must be approved by admin before you can post jobs.</div>
      <?php endif; ?>

      <form method="post" action="<?php echo isset($this) ? site_url('company/post_job') : '/company/post_job'; ?>" class="corp-form" style="max-width:900px">
        <div class="form-group">
          <label>Position</label>
          <input type="text" name="position" placeholder="e.g. Software Engineer, Marketing Manager" required <?php if(!$employerApproved) echo 'disabled'; ?>>
        </div>
        <div class="form-group">
          <label>Requirements</label>
          <input type="text" name="requirements" placeholder="e.g. Bachelor's degree, 2+ years experience" <?php if(!$employerApproved) echo 'disabled'; ?>>
        </div>
        <div class="form-group full">
          <label>Job Description</label>
          <textarea name="description" placeholder="Describe the role, responsibilities, and qualifications..." <?php if(!$employerApproved) echo 'disabled'; ?>></textarea>
        </div>
        <div class="form-group full" style="text-align:right">
          <button type="submit" class="btn-primary" <?php if(!$employerApproved) echo 'disabled'; ?>>Post Job</button>
        </div>
      </form>

      <h3 style="margin-top:28px;margin-bottom:16px">Posted Jobs</h3>
      <table>
        <thead><tr><th>Position</th><th>Description</th><th>Requirements</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($jobs as $j): ?>
          <tr>
            <td style="min-width:180px">
              <form method="post" action="<?php echo isset($this) ? site_url('company/edit_job') : '/company/edit_job'; ?>" class="job-edit-form">
                <input type="hidden" name="id" value="<?php echo h($j['id']); ?>">
                <input type="text" name="position" value="<?php echo h($j['position']); ?>" <?php if(!$employerApproved) echo 'disabled'; ?> style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:13px">
            </td>
            <td>
                <textarea name="description" rows="2" style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:13px;resize:vertical" <?php if(!$employerApproved) echo 'disabled'; ?>><?php echo h($j['description']); ?></textarea>
            </td>
            <td style="min-width:220px">
                <input type="text" name="requirements" value="<?php echo h($j['requirements']); ?>" <?php if(!$employerApproved) echo 'disabled'; ?> style="width:100%;padding:8px 10px;border:1.5px solid var(--border);border-radius:6px;font-size:13px">
            </td>
            <td style="white-space:nowrap">
                <button type="submit" class="btn-secondary" style="padding:7px 14px;font-size:12px;margin-right:6px" <?php if(!$employerApproved) echo 'disabled'; ?>>Update</button>
              </form>
              <form method="post" action="<?php echo isset($this) ? site_url('company/delete_job') : '/company/delete_job'; ?>" style="display:inline" class="confirm-delete">
                <input type="hidden" name="id" value="<?php echo h($j['id']); ?>">
                <button type="submit" class="btn-danger" <?php if(!$employerApproved) echo 'disabled'; ?>>Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(empty($jobs)): ?>
          <tr><td colspan="4" class="muted" style="text-align:center;padding:28px">No jobs posted yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php elseif($tab==='reviews'): ?>
    <div class="panel">
      <h2>Reviews & Ratings</h2>
      <p class="subtitle" style="margin-bottom:24px">Share your experience with our job portal</p>

      <?php
      $this->call->model('ReviewModel');
      $reviewModel = new ReviewModel();
      $hasReviewed = $reviewModel->hasUserReviewed($_SESSION['company_id'] ?? 0, 'employer');
      $allReviews = $reviewModel->getAllReviews();
      $stats = $reviewModel->getAverageRating();
      $distribution = $reviewModel->getRatingDistribution();
      ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px">
        <!-- Submit Review Card -->
        <div class="panel">
          <h3>Submit Your Review</h3>
          <?php if ($hasReviewed): ?>
            <div style="padding:20px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#065f46;text-align:center">
              <p style="margin:0;font-size:14px">✓ Thank you! You have already submitted a review.</p>
            </div>
          <?php else: ?>
            <form method="post" action="<?php echo isset($this) ? site_url('review/submit') : '/review/submit'; ?>">
              <div style="margin-bottom:20px">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:10px">Rating *</label>
                <div class="star-rating" style="display:flex;gap:8px;font-size:32px;cursor:pointer">
                  <span class="star" data-value="1">☆</span>
                  <span class="star" data-value="2">☆</span>
                  <span class="star" data-value="3">☆</span>
                  <span class="star" data-value="4">☆</span>
                  <span class="star" data-value="5">☆</span>
                </div>
                <input type="hidden" name="rating" id="ratingValue" required>
              </div>
              <div style="margin-bottom:20px">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px">Comment (Optional)</label>
                <textarea name="comment" rows="4" placeholder="Share your experience..." style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;resize:vertical"></textarea>
              </div>
              <button type="submit" class="btn-primary">Submit Review</button>
            </form>
          <?php endif; ?>
        </div>

        <!-- Rating Statistics Card -->
        <div class="panel">
          <h3>Overall Rating</h3>
          <div style="text-align:center;margin-bottom:24px">
            <div style="font-size:48px;font-weight:700;color:var(--primary)">
              <?= number_format($stats['avg_rating'] ?? 0, 1) ?>
            </div>
            <div style="font-size:24px;color:#fbbf24;margin:8px 0">
              <?php
              $avgRating = round($stats['avg_rating'] ?? 0);
              for ($i = 1; $i <= 5; $i++) {
                echo $i <= $avgRating ? '★' : '☆';
              }
              ?>
            </div>
            <div style="font-size:13px;color:var(--text-muted)">
              Based on <?= number_format($stats['total_reviews'] ?? 0) ?> reviews
            </div>
          </div>
          <div style="font-size:13px">
            <?php
            $distMap = [];
            foreach ($distribution as $d) {
              $distMap[$d['rating']] = $d['count'];
            }
            for ($i = 5; $i >= 1; $i--):
              $count = $distMap[$i] ?? 0;
              $percentage = ($stats['total_reviews'] ?? 0) > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
            ?>
              <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                <span style="width:60px;font-weight:500"><?= $i ?> stars</span>
                <div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden">
                  <div style="width:<?= $percentage ?>%;height:100%;background:#fbbf24"></div>
                </div>
                <span style="width:40px;text-align:right;color:var(--text-muted)"><?= $count ?></span>
              </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>

      <!-- All Reviews List -->
      <div class="panel">
        <h3>User Reviews</h3>
        <?php if (empty($allReviews)): ?>
          <p style="text-align:center;color:var(--text-muted);padding:40px 0">No reviews yet. Be the first to share your experience!</p>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:16px">
            <?php foreach ($allReviews as $review): ?>
              <div style="padding:16px;background:#f8fafc;border:1px solid var(--border);border-radius:10px">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
                  <div>
                    <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($review['user_name']) ?></div>
                    <div style="font-size:12px;color:var(--text-muted)"><?= date('M d, Y', strtotime($review['created_at'])) ?></div>
                  </div>
                  <div style="color:#fbbf24;font-size:16px">
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                      echo $i <= $review['rating'] ? '★' : '☆';
                    }
                    ?>
                  </div>
                </div>
                <?php if (!empty($review['comment'])): ?>
                  <p style="margin:0;font-size:14px;color:var(--text-secondary);line-height:1.6"><?= htmlspecialchars($review['comment']) ?></p>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>

    </div>
  </main>
</div>

<script>
// logout button -> call company logout or auth logout
document.getElementById('logoutBtn').addEventListener('click', function(e){
  e.preventDefault();
  fetch('<?php echo isset($this) ? site_url('company/logout') : '/company/logout'; ?>', {method:'GET', credentials:'same-origin'})
    .catch(function(){})
    .finally(function(){ window.location = '<?php echo isset($this) ? site_url('login') : '/login'; ?>'; });
});

// Star rating system
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('ratingValue');
if(stars.length > 0 && ratingInput){
  let selectedRating = 0;
  
  stars.forEach(star => {
    star.addEventListener('click', function(){
      selectedRating = parseInt(this.getAttribute('data-value'));
      ratingInput.value = selectedRating;
      updateStars(selectedRating);
    });
    
    star.addEventListener('mouseenter', function(){
      const hoverValue = parseInt(this.getAttribute('data-value'));
      updateStars(hoverValue);
    });
  });
  
  document.querySelector('.star-rating').addEventListener('mouseleave', function(){
    updateStars(selectedRating);
  });
  
  function updateStars(rating){
    stars.forEach(star => {
      const starValue = parseInt(star.getAttribute('data-value'));
      if(starValue <= rating){
        star.textContent = '★';
        star.style.color = '#fbbf24';
      } else {
        star.textContent = '☆';
        star.style.color = '#d1d5db';
      }
    });
  }
}

// Show schedule date input when Interview selected
document.querySelectorAll('.status-select').forEach(function(sel){
  sel.addEventListener('change', function(e){
    var form = sel.closest('.status-form');
    var date = form.querySelector('.schedule-date');
    if(sel.value === 'interview') {
      date.style.display = 'inline-block';
      date.required = true;
    } else {
      date.style.display = 'none';
      date.required = false;
    }
  });
  var ev = new Event('change'); sel.dispatchEvent(ev);
});

// Handle schedule submission with interview notification
function handleScheduleSubmit(form, event) {
  const status = form.querySelector('[name="status"]').value;
  
  if (status === 'interview') {
    const scheduleDate = form.querySelector('[name="schedule_date"]').value;
    const applicantName = form.querySelector('[name="applicant_name"]').value;
    const applicantEmail = form.querySelector('[name="applicant_email"]').value;
    const position = form.querySelector('[name="position"]').value;
    
    if (!scheduleDate) {
      alert('Please select interview date and time');
      event.preventDefault();
      return false;
    }
    
    // Check if date is not in the past
    const selectedDate = new Date(scheduleDate);
    const now = new Date();
    if (selectedDate < now) {
      alert('Cannot schedule interview in the past. Please select a future date and time.');
      event.preventDefault();
      return false;
    }
    
    // Check if weekday (Monday-Friday only)
    const dayOfWeek = selectedDate.getDay();
    if (dayOfWeek === 0 || dayOfWeek === 6) {
      alert('Interview schedule must be on weekdays only (Monday to Friday).');
      event.preventDefault();
      return false;
    }
    
    // Check if time is between 8am-5pm
    const hours = selectedDate.getHours();
    if (hours < 8 || hours >= 17) {
      alert('Interview schedule must be between 8:00 AM and 5:00 PM only.');
      event.preventDefault();
      return false;
    }
    
    // Show confirmation and submit
    event.preventDefault();
    handleAuthClick(applicantName, applicantEmail, position, selectedDate.toISOString(), form);
    return false;
  }
  
  return true;
}

// Confirm delete
document.querySelectorAll('.confirm-delete').forEach(function(f){
  f.addEventListener('submit', function(e){ if(!confirm('Delete this job posting?')) e.preventDefault(); });
});

// Handle Interview Result form submission (Passed/Rejected)
document.querySelectorAll('.interview-result-form').forEach(function(f){
  f.addEventListener('submit', function(e){
    const status = f.querySelector('[name="status"]').value;
    const confirmMsg = status === 'passed' ? 'Mark as Passed?' : (status === 'reject' ? 'Mark as Rejected?' : 'Update status?');
    if(!confirm(confirmMsg)) {
      e.preventDefault();
    }
  });
});

// Settings internal nav toggles
document.querySelectorAll('.settings-nav').forEach(function(b){
  b.addEventListener('click', function(){
    document.querySelectorAll('.settings-nav').forEach(function(x){ x.classList.remove('active'); });
    b.classList.add('active');
    var t = b.getAttribute('data-target');
    document.querySelectorAll('.settings-panel').forEach(function(p){ p.style.display='none'; });
    var el = document.getElementById('settings-' + t);
    if(el) el.style.display = 'block';
  });
});

// Password change form validation
const empPwdForm = document.getElementById('empPwdForm');
if(empPwdForm){
  empPwdForm.addEventListener('submit', function(e){
    e.preventDefault();
    const cur = document.getElementById('empCurrentPassword')?.value || ''; 
    const np = document.getElementById('empNewPassword')?.value || ''; 
    const cp = document.getElementById('empConfirmPassword')?.value || '';
    
    // Validation 1: Check all fields filled
    if(!cur.trim()){ alert('Please enter your current password.'); return; }
    if(!np.trim()){ alert('Please enter a new password.'); return; }
    if(!cp.trim()){ alert('Please confirm your new password.'); return; }
    
    // Validation 2: New password minimum length
    if(np.length < 8){ alert('New password must be at least 8 characters.'); return; }
    
    // Validation 3: New password matches confirm
    if(np !== cp){ alert('New password and confirm password do not match.'); return; }
    
    // Validation 4: New password cannot be same as current
    if(np === cur){ alert('New password cannot be the same as your current password.'); return; }
    
    // All validations passed - submit form
    empPwdForm.submit();
  });
}

// Initialize settings subtab based on URL param 'sub' or default to profile
(function(){
  var params = new URLSearchParams(window.location.search);
  var sub = params.get('sub') || 'profile';
  var btn = document.querySelector('.settings-nav[data-target="'+sub+'"]');
  if(btn) { 
    btn.click(); 
  } else { 
    var profileBtn = document.querySelector('.settings-nav[data-target="profile"]');
    if(profileBtn) profileBtn.click();
  }
})();

// Keep sidebar nav in sync with tab param
setActiveLink();

</script>
</body>
</html>
