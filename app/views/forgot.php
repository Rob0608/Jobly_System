<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Forgot Password</title>
  <style>body{font-family:Poppins,Arial;margin:40px;background:#f8fafc} .card{max-width:520px;margin:0 auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 8px 25px rgba(2,6,23,.06)} .field{margin-bottom:12px} .label{font-size:13px;color:#334155;margin-bottom:6px;display:block} .input{width:100%;padding:10px;border:1px solid #e6eef8;border-radius:8px} .btn{background:#1d4ed8;color:#fff;padding:10px 14px;border:none;border-radius:8px;font-weight:600} .muted{color:#64748b;font-size:13px}</style>
</head>
<body>
  <div class="card">
    <h2>Forgot password</h2>
    <?php if (isset($_SESSION['error'])): ?><div style="background:#fff1f2;color:#991b1b;padding:8px;border-radius:6px;margin-bottom:10px"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?><div style="background:#ecfdf5;color:#065f46;padding:8px;border-radius:6px;margin-bottom:10px"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>

    <p class="muted">Enter the email you used to register as an applicant or employer. We'll send a verification code to that address.</p>

    <form action="<?= site_url('forgot/send') ?>" method="POST">
      <div class="field">
        <label class="label">Email address</label>
        <input class="input" type="email" name="email" required placeholder="you@example.com">
      </div>
      <div class="field">
        <button class="btn" type="submit">Send verification code</button>
        <a href="<?= site_url('login') ?>" style="margin-left:10px; color:#64748b; text-decoration:none">Back to login</a>
      </div>
    </form>
  </div>
</body>
</html>
