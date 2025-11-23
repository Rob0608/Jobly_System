<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Verify reset code</title>
  <style>body{font-family:Poppins,Arial;margin:40px;background:#f8fafc} .card{max-width:520px;margin:0 auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 8px 25px rgba(2,6,23,.06)} .field{margin-bottom:12px} .label{font-size:13px;color:#334155;margin-bottom:6px;display:block} .input{width:100%;padding:10px;border:1px solid #e6eef8;border-radius:8px} .btn{background:#1d4ed8;color:#fff;padding:10px 14px;border:none;border-radius:8px;font-weight:600} .muted{color:#64748b;font-size:13px}</style>
</head>
<body>
  <div class="card">
    <h2>Enter verification code</h2>
    <?php if (isset($_SESSION['error'])): ?><div style="background:#fff1f2;color:#991b1b;padding:8px;border-radius:6px;margin-bottom:10px"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
    <p class="muted">A 4-digit verification code was sent to your email. Enter it below to proceed to reset your password.</p>

    <form action="<?= site_url('forgot/verify') ?>" method="POST">
      <div class="field">
        <label class="label">Email</label>
        <input class="input" type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
      </div>
      <div class="field">
        <label class="label">Verification code</label>
        <input class="input" type="text" name="code" required maxlength="6" placeholder="1234">
      </div>
      <div class="field">
        <button class="btn" type="submit">Verify code</button>
        <a href="<?= site_url('forgot') ?>" style="margin-left:10px; color:#64748b; text-decoration:none">Resend</a>
      </div>
    </form>
  </div>
</body>
</html>
