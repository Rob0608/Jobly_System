<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Your Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{ --primary:#1d4ed8; --primary-600:#2563eb; --bg:#f3f4f6; --text:#0f172a; --muted:#64748b; --card:#ffffff; --border:#e2e8f0; }
    *{box-sizing:border-box; font-family:"Poppins",sans-serif}
    body{margin:0; background:#f3f4f6}
    .wrap{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:28px}
    .card{width:100%; max-width:720px; background:var(--card); border-radius:18px; box-shadow:0 16px 36px rgba(2,6,23,.08); padding:28px 28px 22px}
    h1{margin:0 0 6px; font-size:22px; color:var(--text)}
    p{margin:0 0 18px; font-size:13px; color:var(--muted)}
    form{display:grid; grid-template-columns:1fr 1fr; gap:18px 22px}
    .full{grid-column:span 2}
    label{display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px}
    .input{width:100%; padding:12px 12px; border:1px solid var(--border); border-radius:12px; background:#f8fafc; outline:0; transition:.2s; font-size:14px}
    .input:focus{border-color:var(--primary-600); box-shadow:0 0 0 4px rgba(37,99,235,.15)}
    .btn{grid-column:span 2; background:var(--primary); color:#fff; border:none; padding:12px 16px; border-radius:12px; font-weight:600; cursor:pointer; transition:.2s}
    .btn:hover{background:var(--primary-600)}
    .alert{margin:10px 0 0; padding:10px 12px; border-radius:10px; font-size:12px}
    .alert.error{background:#fef2f2; border:1px solid #fecaca; color:#991b1b}
    .muted{color:var(--muted); font-size:12px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h1>Complete your applicant profile</h1>
      <p>We pulled your email and name from Google. Please confirm your name and add your birthdate to continue.</p>

      <?php if (!isset($_SESSION)) session_start(); ?>
      <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <?php $maxBirthdate = date('Y-m-d', strtotime('-18 years')); ?>
      <?php $minBirthdate = date('Y-m-d', strtotime('-60 years')); ?>
      <form action="<?= site_url('auth/google/complete') ?>" method="POST" id="googleForm">
        <div>
          <label>First Name</label>
          <input class="input" type="text" name="first_name" value="<?= htmlspecialchars($given_name ?? '') ?>" required>
        </div>
        <div>
          <label>Middle Name</label>
          <input class="input" type="text" name="middle_name" value="">
        </div>
        <div>
          <label>Last Name</label>
          <input class="input" type="text" name="last_name" value="<?= htmlspecialchars($family_name ?? '') ?>" required>
        </div>
        <div>
          <label>Birthdate</label>
          <input class="input" type="date" id="birthdateGoogle" name="birthdate" required max="<?= $maxBirthdate; ?>" min="<?= $minBirthdate; ?>">
          <div id="ageHintGoogle" style="display:none;margin-top:6px;font-size:12px;color:#dc2626;font-weight:500;">You must be between 18 and 60 years old.</div>
        </div>

        <div class="full">
          <label>Email</label>
          <input class="input" type="email" value="<?= htmlspecialchars($email ?? '') ?>" disabled>
          <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
        </div>

        <button class="btn" type="submit">Finish and continue</button>
        <p class="muted full">By continuing, you agree to our Terms and acknowledge our Privacy Policy.</p>
      </form>
    </div>
  </div>

  <script>
    const form = document.getElementById('googleForm');
    const birthdate = document.getElementById('birthdateGoogle');
    const ageHint = document.getElementById('ageHintGoogle');

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
      ageHint.style.display = show ? 'block' : 'none';
    }

    birthdate.addEventListener('change', updateAgeHint);
    birthdate.addEventListener('input', updateAgeHint);
    updateAgeHint();

    form.addEventListener('submit', (e) => {
      if (!isAdultAndWithinRange(birthdate.value)) {
        e.preventDefault();
        ageHint.style.display = 'block';
        birthdate.focus();
      }
    });
  </script>
</body>
</html>
