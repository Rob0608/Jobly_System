<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applicant Registration</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body {
    font-family: "Poppins", sans-serif;
    background-color: #f3f4f6;
    margin: 0;
    padding: 0;
  }

  .container {
    max-width: 850px;
    margin: 50px auto;
    background: #fff;
    padding: 45px 55px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  }

  h2 {
    text-align: center;
    color: #1e293b;
    margin-bottom: 30px;
    font-weight: 600;
  }

  form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px 35px;
  }

  label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: #334155;
  }

  input, select, textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background-color: #f9fafb;
    transition: 0.3s ease;
    box-sizing: border-box;
  }

  input:focus, select:focus, textarea:focus {
    border-color: #2563eb;
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
  }

  .full { grid-column: span 2; }

  .gender-group {
    display: flex;
    gap: 15px;
    align-items: center;
  }

  button {
    grid-column: span 2;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    border: none;
    padding: 13px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s ease;
  }

  button:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: translateY(-2px);
  }

  /* Unified password wrapper (same approach as company form) */
  .password-wrapper { position:relative; width:100%; }
  .password-wrapper input { width:100%; padding:10px 14px; padding-right:44px; box-sizing:border-box; }
  .toggle-password { position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:16px; cursor:pointer; user-select:none; padding:4px; line-height:1; color:#374151; background:transparent; border-radius:6px; display:none; }
  .toggle-password:hover { color:#1d4ed8; }

  @media (max-width: 768px) {
    form { grid-template-columns: 1fr; }
    .full { grid-column: span 1; }
  }
</style>
</head>
<body>
  <div class="container">
    <h2>Applicant Registration</h2>
    <?php if (!isset($_SESSION)) session_start(); ?>
    <?php if (!empty($_SESSION['error_birthdate'])): ?>
      <div class="flash-error" style="background:#fee2e2;color:#b91c1c;padding:12px 18px;border:1px solid #fca5a5;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:18px;">⚠️</span>
        <span><?= htmlspecialchars($_SESSION['error_birthdate']); ?></span>
      </div>
      <?php unset($_SESSION['error_birthdate']); endif; ?>

<?php $maxBirthdate = date('Y-m-d', strtotime('-18 years')); ?>
<?php $minBirthdate = date('Y-m-d', strtotime('-60 years')); ?>
<form id="appRegForm" action="<?= site_url('/applicant/save') ?>" method="POST" enctype="multipart/form-data">
  
  <div><label>First Name:</label><input class="name-input" maxlength="64" type="text" name="first_name" required></div>
  <div><label>Middle Name:</label><input class="name-input" maxlength="64" type="text" name="middle_name"></div>
  <div><label>Last Name:</label><input class="name-input" maxlength="64" type="text" name="last_name" required></div>
  <div class="birthdate-wrapper" style="position:relative;">
    <label>Birthdate:</label>
    <input type="date" name="birthdate" id="birthdate" required max="<?= $maxBirthdate; ?>" min="<?= $minBirthdate; ?>">
    <div id="ageInline" style="display:none;margin-top:6px;font-size:12px;color:#dc2626;font-weight:500;">You must be between 18 and 60 years old.</div>
  </div>

  <div>
    <label>Gender:</label>
    <div class="gender-group">
      <label><input type="radio" name="gender" value="Male" required> Male</label>
      <label><input type="radio" name="gender" value="Female"> Female</label>
    </div>
  </div>

  <div>
    <label>Contact Number:</label>
    <div style="display:flex; gap:8px;">
  	  <span style="padding:10px 12px; background:#f1f5f9; border:1px solid #d1d5db; border-radius:10px;">+63</span>
       <input class="phone-input" inputmode="numeric" maxlength="11" pattern="\d{10,11}" type="text" name="contact" placeholder="9123456789" required>
    </div>
  </div>

  <div><label>Email Address:</label><input type="email" name="email" required></div>
  <!-- Removed: Place of Birth, Barangay, Province, Municipality, Permanent Address, Resume upload per request -->

  <!-- Removed position applying for field per request -->

  <!-- Password fields unified with company styling -->
  <div>
    <label>Password:</label>
    <div class="password-wrapper">
      <input id="appPassword" type="password" name="password" minlength="8" pattern=".{8,}" title="Minimum 8 characters" required autocomplete="new-password">
      <span id="togglePassword" class="toggle-password">👁️</span>
    </div>
  </div>
  <div>
    <label>Confirm Password:</label>
    <div class="password-wrapper">
      <input id="appPasswordConfirm" type="password" name="password_confirm" minlength="8" pattern=".{8,}" title="Minimum 8 characters" required autocomplete="new-password" onpaste="return false;" oncopy="return false;">
      <span id="toggleConfirm" class="toggle-password">👁️</span>
    </div>
  </div>

  <div id="pwdHint" class="full" style="color:#dc2626;font-size:13px;display:none;">
    Password must be at least 8 characters.
  </div>

  <button type="submit">Register</button>
</form>

    <div style="margin:16px 0; display:flex; align-items:center; gap:10px">
      <div style="height:1px; background:#e5e7eb; flex:1"></div>
      <span style="color:#64748b; font-size:12px; white-space:nowrap">or</span>
      <div style="height:1px; background:#e5e7eb; flex:1"></div>
    </div>

    <div>
      <a href="<?= site_url('auth/google') ?>?role=applicant" style="display:flex; align-items:center; justify-content:center; gap:10px; width:100%; background:#ffffff; color:#111827; border:1px solid #e5e7eb; padding:12px 16px; border-radius:12px; font-weight:600; text-decoration:none;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true">
          <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12 s5.373-12,12-12c3.059,0,5.842,1.156,7.961,3.039l5.657-5.657C33.578,6.053,29.043,4,24,4C12.955,4,4,12.955,4,24 s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
          <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,16.108,18.961,13,24,13c3.059,0,5.842,1.156,7.961,3.039l5.657-5.657 C33.578,6.053,29.043,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
          <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.197l-6.197-5.238C29.211,35.091,26.715,36,24,36 c-5.202,0-9.619-3.317-11.283-7.946l-6.531,5.033C9.505,39.556,16.227,44,24,44z"/>
          <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.094,5.565 c0.001-0.001,0.002-0.001,0.003-0.002l6.197,5.238C36.916,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
        </svg>
        Sign up with Google (Applicant)
      </a>
    </div>

  </div>

<script>
  const form = document.getElementById('appRegForm');
  const birthdate = document.getElementById('birthdate');
  const pwd = document.getElementById('appPassword');
  const confirmPwd = document.getElementById('appPasswordConfirm');
  const togglePwd = document.getElementById('togglePassword');
  const toggleConfirm = document.getElementById('toggleConfirm');
  const hint = document.getElementById('pwdHint');
  const ageInline = document.getElementById('ageInline');
  // Removed bottom duplicate age hint

  // 🔹 Show icon only if field has text
  function updateToggleVisibility(inputEl, iconEl){
    iconEl.style.display = inputEl.value.length > 0 ? 'inline' : 'none';
  }

  function syncPasswordUI(){
    updateToggleVisibility(pwd, togglePwd);
    updateToggleVisibility(confirmPwd, toggleConfirm);
    updateHint();
  }

  // 🔹 Password toggle logic
  function setupPasswordToggle(iconEl, inputEl){
    iconEl.addEventListener('click', () => {
      const show = inputEl.type === 'password';
      inputEl.type = show ? 'text' : 'password';
      iconEl.textContent = show ? '🚫' : '👁️';
      iconEl.title = show ? 'Hide password' : 'Show password';
      syncPasswordUI(); // 🔥 keep icons updated correctly
    });
  }

  setupPasswordToggle(togglePwd, pwd);
  setupPasswordToggle(toggleConfirm, confirmPwd);

  // 🔹 Show warning if password too short
  function updateHint(){
    if (pwd.value.length === 0 || pwd.value.length >= 8){
      hint.style.display = 'none';
      return;
    }
    hint.style.display = 'block';
    hint.style.color = '#dc2626';
    hint.textContent = 'Password must be at least 8 characters.';
  }

  pwd.addEventListener('input', syncPasswordUI);
  confirmPwd.addEventListener('input', syncPasswordUI);
  syncPasswordUI();

  // 🔹 Disable copy/paste & right click on confirm password
  ['paste','copy','cut','contextmenu'].forEach(evt => {
    confirmPwd.addEventListener(evt, e => e.preventDefault());
  });
  confirmPwd.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && ['v','x'].includes(e.key.toLowerCase())) {
      e.preventDefault();
    }
  });

  // 🔹 Age validation
  function isAdultAndWithinRange(dateStr){
    if (!dateStr) return false;
    const dob = new Date(dateStr);
    if (isNaN(dob.getTime())) return false;
    const today = new Date();
    const min18Cutoff = new Date(today.getFullYear()-18, today.getMonth(), today.getDate());
    const max60Cutoff = new Date(today.getFullYear()-60, today.getMonth(), today.getDate());
    return dob <= min18Cutoff && dob >= max60Cutoff;
  }

  function updateAgeHint(){
    const show = !isAdultAndWithinRange(birthdate.value);
    ageInline.style.display = show ? 'block' : 'none';
  }

  birthdate.addEventListener('change', updateAgeHint);
  birthdate.addEventListener('input', updateAgeHint);
  updateAgeHint();

  // 🔹 Final submit validation
  form.addEventListener('submit', (e) => {
    if (!isAdultAndWithinRange(birthdate.value)) {
      e.preventDefault();
      ageInline.style.display = 'block';
      birthdate.focus();
      return;
    }

    if (pwd.value !== confirmPwd.value) {
      e.preventDefault();
      hint.style.display = 'block';
      hint.style.color = '#dc2626';
      hint.textContent = 'Passwords do not match.';
      confirmPwd.focus();
    }
  });

  // 🔹 Phone input: strip non-digits and prevent letters while typing
  document.querySelectorAll('.phone-input').forEach(function(el){
    el.addEventListener('input', function(e){
      // Remove any non-digit characters
      const cleaned = this.value.replace(/\D+/g, '');
      if (cleaned.length > 11) {
        this.value = cleaned.slice(0,11);
      } else {
        this.value = cleaned;
      }
    });
    // Prevent non-numeric keys (allow navigation keys)
    el.addEventListener('keydown', function(e){
      const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab'];
      if (allowed.includes(e.key)) return;
      if (/\d/.test(e.key)) return;
      // allow Ctrl/Cmd+A/C/V/X
      if (e.ctrlKey || e.metaKey) return;
      e.preventDefault();
    });
  });

  // 🔹 Name inputs: allow letters, spaces, hyphen, apostrophe and period only
  document.querySelectorAll('.name-input').forEach(function(el){
    el.addEventListener('input', function(){
      // Keep letters (unicode), spaces, hyphen, apostrophe and period
      const cleaned = this.value.replace(/[^\p{L}\s'\-\.]/gu, '');
      // Collapse multiple spaces
      this.value = cleaned.replace(/\s{2,}/g,' ');
    });
    el.addEventListener('keydown', function(e){
      const allowed = ['Backspace','ArrowLeft','ArrowRight','Delete','Tab'];
      if (allowed.includes(e.key)) return;
      if (/^[\p{L}\s'\-\.]$/u.test(e.key)) return;
      if (e.ctrlKey || e.metaKey) return;
      e.preventDefault();
    });
  });
</script>
</body>
</html>
