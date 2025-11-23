<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verification Successful</title>
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
  width: 380px;
}
h2 {
  color: #28a745;
  margin-bottom: 10px;
}
p {
  color: #555;
  font-size: 15px;
  margin-bottom: 20px;
}
button {
  background: #007BFF;
  color: white;
  padding: 12px 28px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}
button:hover {
  background: #0056b3;
}
.fade-in {
  animation: fadeIn 1s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
<div class="container fade-in">
  <h2>✅ Email Verified!</h2>
  <p>Your applicant account has been successfully verified.</p>
  <p>You can now log in and continue.</p>
  <form action="<?= site_url('applicant/login') ?>">
    <button type="submit">Go to Login</button>
  </form>
</div>

<script>
// Optional auto-redirect after 5 seconds
setTimeout(() => {
  window.location.href = "<?= site_url('applicant/login') ?>";
}, 5000);
</script>
</body>
</html>
