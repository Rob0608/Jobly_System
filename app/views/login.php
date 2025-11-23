<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --primary:#1d4ed8; /* corporate blue */
      --primary-600:#2563eb;
      --bg:#f3f4f6;
      --text:#0f172a;
      --muted:#64748b;
      --card:#ffffff;
      --border:#e2e8f0;
    }
    *{box-sizing:border-box; font-family:"Poppins",sans-serif}
    body{
      margin:0;
      /* layered soft corporate background that matches dashboard theme */
      background-image:
        radial-gradient( circle at 18% 12%, rgba(29,78,216,.10) 0, rgba(29,78,216,.10) 220px, transparent 221px),
        radial-gradient( circle at 86% 28%, rgba(37,99,235,.10) 0, rgba(37,99,235,.10) 180px, transparent 181px),
        radial-gradient( circle at 12% 82%, rgba(14,165,233,.12) 0, rgba(14,165,233,.12) 160px, transparent 161px),
        linear-gradient(135deg,#f8fafc 0%, #eef2ff 100%);
      background-attachment: fixed;
    }
    .wrap{min-height:100vh; display:flex; align-items:center; justify-content:center; padding:28px}
    .shell{width:100%; max-width:980px; display:grid; grid-template-columns:1.15fr 0.85fr; border-radius:18px; overflow:hidden; box-shadow:0 20px 45px rgba(2,6,23,.08)}
    .left{background:radial-gradient(1400px 400px at -10% -10%, #93c5fd 0%, transparent 50%), linear-gradient(135deg,#0b1220 0%, #0f172a 40%, #111827 100%); color:#fff; padding:46px}
    .badge{display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.06); padding:8px 12px; border-radius:999px; font-size:12px}
    .brand{margin-top:18px; font-weight:600; font-size:28px}
    .tag{margin-top:8px; color:#cbd5e1; font-size:13px; line-height:1.6}
    .right{background:var(--card); padding:38px 40px}
    .title{margin:0 0 6px; font-size:22px; color:var(--text)}
    .subtitle{margin:0 0 18px; font-size:13px; color:var(--muted)}
    .alert{margin-bottom:10px; padding:10px 12px; border-radius:10px; font-size:12px}
    .alert.error{background:#fef2f2; border:1px solid #fecaca; color:#991b1b}
    .alert.success{background:#ecfdf5; border:1px solid #bbf7d0; color:#065f46}
    .field{margin:10px 0}
    .label{display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:6px}
    .input{width:100%; padding:12px 12px; border:1px solid var(--border); border-radius:12px; background:#f8fafc; outline:0; transition:.2s; font-size:14px}
    .input:focus{border-color:var(--primary-600); box-shadow:0 0 0 4px rgba(37,99,235,.15)}
    .pwd-wrap{position:relative}
    .toggle{position:absolute; right:10px; top:50%; transform:translateY(-5%); background:transparent; border:none; color:#475569; cursor:pointer; font-size:12px}
    .actions{display:flex; align-items:center; justify-content:space-between; margin-top:8px}
    .btn{background:var(--primary); color:#fff; border:none; padding:12px 16px; border-radius:12px; font-weight:600; cursor:pointer; transition:.2s}
    .btn:hover{background:var(--primary-600)}
    .select{width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:#fff}
    .muted{color:var(--muted); font-size:12px}
    .register{margin-top:16px}
    @media(max-width:920px){ .shell{grid-template-columns:1fr} .left{display:none} }
  </style>
  </head>
<body>
  <div class="wrap">
    <div class="shell">
      <aside class="left">
        <div class="badge">Welcome</div>
        <div class="brand">JOBLY</div>
        <p class="tag">Sign in to manage your company profile, post hiring, or apply to your dream job — all in a clean, modern interface.</p>
      </aside>
      <main class="right">
        <h1 class="title">Sign in</h1>
        <p class="subtitle">Use your registered email and password.</p>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="POST" novalidate>
          <div class="field">
            <label class="label">Email</label>
            <input class="input" type="text" name="username" placeholder="example@gmail.com" required>
          </div>
          <div class="field pwd-wrap">
            <label class="label">Password</label>
            <input class="input" id="pwd" type="password" name="password" placeholder="••••••••" required minlength="8">
            <button type="button" class="toggle" id="togglePwd" aria-label="Show password">Show</button>
          </div>
          <div class="actions">
            <span class="muted">Forgot password?</span>
            <button class="btn" type="submit">Login</button>
          </div>
        </form>

        <div style="margin:14px 0; display:flex; align-items:center; gap:10px">
          <div style="height:1px; background:#e5e7eb; flex:1"></div>
          <span class="muted" style="white-space:nowrap">or</span>
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
            Continue with Google (Applicant)
          </a>
        </div>

        <div class="register">
          <p class="muted" style="margin:0 0 8px">Don't have an account? Register:</p>
          <div style="display:flex; gap:8px">
            <select id="registerSelect" class="select" required>
              <option value="" disabled selected>Select role…</option>
              <option value="<?= site_url('company/register') ?>">Employer</option>
              <option value="<?= site_url('applicant/register') ?>">Applicant</option>
            </select>
            <button id="goRegister" class="btn" disabled style="min-width:110px">Continue</button>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    const registerSelect = document.getElementById('registerSelect');
    const goRegister = document.getElementById('goRegister');
    registerSelect.addEventListener('change', () => { goRegister.disabled = !registerSelect.value; });
    goRegister.addEventListener('click', () => { const url = registerSelect.value; if (url) window.location.href = url; });

    // Password show/hide toggle (robust)
    const pwd = document.getElementById('pwd');
    const toggle = document.getElementById('togglePwd');
    toggle.addEventListener('click', () => {
      const show = pwd.type === 'password';
      pwd.type = show ? 'text' : 'password';
      toggle.textContent = show ? 'Hide' : 'Show';
      toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  </script>

</body>
</html>
