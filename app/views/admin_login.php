<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Portal</title>
    <style>
      :root{--bg:#0f1724;--card:#ffffff;--muted:#94a3b8;--primary:#1d4ed8;--primary-600:#2563eb;--accent:#e6f6fb}
      html,body{height:100%}
      body{
        margin:0;font-family: "Poppins", Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        /* Soft corporate background similar to user login */
        background-image:
          radial-gradient( circle at 18% 12%, rgba(29,78,216,.10) 0, rgba(29,78,216,.10) 240px, transparent 241px),
          radial-gradient( circle at 86% 28%, rgba(37,99,235,.10) 0, rgba(37,99,235,.10) 200px, transparent 201px),
          radial-gradient( circle at 12% 82%, rgba(14,165,233,.10) 0, rgba(14,165,233,.10) 170px, transparent 171px),
          linear-gradient(135deg,#0b1220 0%, #0f172a 45%, #0f1724 100%);
        background-attachment: fixed;
        display:flex;align-items:center;justify-content:center;padding:24px;
        color:#0f1724;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale
      }
      .card{width:420px;max-width:96vw;background:var(--card);border-radius:12px;padding:36px 32px;box-shadow:0 10px 30px rgba(2,6,23,.6);position:relative}
      .logo-circle{width:72px;height:72px;border-radius:999px;background:linear-gradient(180deg,var(--primary),var(--primary-600));display:flex;align-items:center;justify-content:center;margin:0 auto 14px; box-shadow:0 8px 18px rgba(37,99,235,.35)}
      .logo-circle svg{width:34px;height:34px;fill:#fff}
      h1{font-size:20px;margin:0;text-align:center}
      p.lead{margin:6px 0 20px;text-align:center;color:var(--muted);font-size:13px}
      form{display:block}
      .field{margin-bottom:14px}
      label{display:block;font-size:13px;margin-bottom:8px;color:#334155}
      input[type="email"],input[type="password"]{
        width:100%;padding:12px 14px;border-radius:8px;border:1px solid #e6eef6;background:#f8fbfd;color:#0f1724;font-size:14px;box-sizing:border-box
      }
      .input-row{display:flex;gap:8px}
      .btn{display:inline-flex;align-items:center;gap:10px;justify-content:center;width:100%;padding:12px;border-radius:10px;background:var(--primary);border:none;color:#fff;font-weight:600;cursor:pointer;font-size:15px;transition:.2s}
      .btn:hover{background:var(--primary-600)}
      .btn:active{transform:translateY(1px)}
      .info-box{margin-top:18px;padding:12px;border-radius:8px;background:var(--accent);color:#064e3b;font-size:13px;border:1px solid rgba(2,132,199,.08)}
      .error{background:#fff4f4;color:#7f1d1d;padding:10px;border-radius:8px;border:1px solid #fecaca;margin-bottom:12px;font-size:13px}
      .meta{margin-top:18px;text-align:center;color:var(--muted);font-size:12px}
      .password-row{position:relative}
      .toggle-pass{position:absolute;right:10px;top:34px;background:none;border:none;cursor:pointer;color:var(--primary);font-weight:600}
      @media (max-width:480px){.card{padding:24px}}
    </style>
  </head>
  <body>
    <div class="card" role="main" aria-labelledby="login-title">
      <div class="logo-circle" aria-hidden="true">
        <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M12 2a1 1 0 0 0-.832.445L6.5 10.5V17a3 3 0 0 0 3 3h5a3 3 0 0 0 3-3v-6.5l-4.668-8.055A1 1 0 0 0 12 2z"></path></svg>
      </div>

      <h1 id="login-title">Admin Portal</h1>
      <p class="lead">Sign in to access admin panel</p>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="error" role="alert"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form action="<?= site_url('admin/login') ?>" method="POST" novalidate>
        <div class="field">
          <label for="username">Admin Username</label>
          <input id="username" name="username" type="email" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required autocomplete="username">
        </div>

        <div class="field password-row">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password">
          <button type="button" class="toggle-pass" aria-label="Toggle password visibility" onclick="togglePassword()">Show</button>
        </div>

        <div class="field">
          <button class="btn" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path><polyline points="17 8 21 12 17 16"></polyline></svg>
            Sign In as Admin
          </button>
        </div>
      </form>

      <div class="info-box" role="note">
        <strong>For Administrators Only</strong>
        <div style="margin-top:6px;color:#0f1724;opacity:.85">This is a restricted area. Unauthorized access attempts will be logged.</div>
      </div>

      <div class="meta">&copy; <?= date('Y') ?> Your Platform. All rights reserved.</div>
    </div>

    <script>
      function togglePassword(){
        var p = document.getElementById('password');
        var btn = document.querySelector('.toggle-pass');
        if(p.type === 'password'){ p.type = 'text'; btn.textContent = 'Hide'; }
        else { p.type = 'password'; btn.textContent = 'Show'; }
      }

      // Basic client-side validation to improve UX (server still authoritative)
      (function(){
        var form = document.querySelector('form');
        form.addEventListener('submit', function(e){
          var u = document.getElementById('username');
          var p = document.getElementById('password');
          if(!u.value.trim() || !p.value.trim()){
            e.preventDefault();
            alert('Please enter your admin email and password.');
            if(!u.value.trim()) u.focus(); else p.focus();
          }
        });
      })();
    </script>
  </body>
</html>
