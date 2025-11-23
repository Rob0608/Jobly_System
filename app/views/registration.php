<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Choose Registration Type</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .box {
      background: #fff;
      padding: 40px 60px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    h2 {
      margin-bottom: 25px;
      color: #1e293b;
    }
    button {
      display: block;
      width: 100%;
      margin: 15px 0;
      padding: 15px;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s ease;
      color: #fff;
      font-weight: 600;
    }
    .employer {
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }
    .applicant {
      background: linear-gradient(135deg, #16a34a, #15803d);
    }
    button:hover {
      transform: translateY(-2px);
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="box">
    <h2>Choose Registration Type</h2>
    <button class="employer" onclick="window.location.href='<?= site_url('/company/register') ?>'">Register as Employer</button>
    <button class="applicant" onclick="window.location.href='<?= site_url('/applicant/register') ?>'">Register as Applicant</button>
  </div>
</body>
</html>
