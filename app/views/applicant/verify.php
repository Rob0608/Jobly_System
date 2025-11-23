<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applicant Verification</title>
<style>
body {
  font-family: "Poppins", sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: #f4f6f9;
}
.container {
  background: white;
  padding: 40px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  width: 360px;
}
h2 {
  margin-bottom: 10px;
}
p {
  color: #555;
  font-size: 14px;
  margin-bottom: 25px;
}
.code {
  width: 55px;
  height: 55px;
  font-size: 24px;
  text-align: center;
  margin: 6px;
  border: 2px solid #ccc;
  border-radius: 10px;
  transition: 0.2s;
}
.code:focus {
  border-color: #007bff;
  outline: none;
  box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
}
button {
  background: #007BFF;
  color: white;
  padding: 12px 30px;
  border: none;
  border-radius: 10px;
  margin-top: 20px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}
button:hover {
  background: #0056b3;
  transform: translateY(-2px);
}

/* Shake effect for wrong code */
.shake {
  animation: shake 0.3s;
}
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
}
</style>
</head>
<body>
<div class="container">
  <h2>🔐 Verify Your Email</h2>
  <p>We sent a 4-digit verification code to <strong><?= htmlspecialchars($email) ?></strong></p>

  <form method="POST" action="<?= site_url('applicant/verify_code') ?>" id="verifyForm">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <input type="text" maxlength="1" class="code" name="c1" required>
    <input type="text" maxlength="1" class="code" name="c2" required>
    <input type="text" maxlength="1" class="code" name="c3" required>
    <input type="text" maxlength="1" class="code" name="c4" required><br>
    <button type="submit">Verify</button>
  </form>
</div>

<script>
const inputs = document.querySelectorAll('.code');
const form = document.getElementById('verifyForm');

inputs.forEach((input, i) => {
  input.addEventListener('input', e => {
    e.target.value = e.target.value.replace(/[^0-9]/g, '');
    if (e.target.value && i < inputs.length - 1) inputs[i + 1].focus();
  });
  input.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !e.target.value && i > 0) inputs[i - 1].focus();
  });
});

window.onload = () => inputs[0].focus();

if (performance.navigation.type === 1) {
  form.reset();
  inputs[0].focus();
}

const params = new URLSearchParams(window.location.search);
if (params.get('error') === '1') {
  document.querySelector('.container').classList.add('shake');
  setTimeout(() => {
    document.querySelector('.container').classList.remove('shake');
  }, 500);
}
</script>
</body>
</html>
