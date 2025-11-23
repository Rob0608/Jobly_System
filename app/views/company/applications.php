<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Applicant List</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f7f8fa;
      margin: 0;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #333;
      margin-bottom: 10px;
    }
    .top-bar {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 15px;
    }
    .back-btn {
      background: #3742fa;
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 6px;
      transition: background 0.3s;
      font-size: 14px;
      font-weight: 500;
    }
    .back-btn:hover {
      background: #2f35d6;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #3742fa;
      color: white;
    }
    tr:hover {
      background: #f1f1f1;
    }
  </style>
</head>
<body>

  <div class="top-bar">
    <!-- Change this link to your actual dashboard route -->
    <a href="/company/dashboard" class="back-btn">← Back to Dashboard</a>
  </div>

  <h1>Registered Applicants</h1>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Contact</th>
        <th>Gender</th>
        <th>City</th>
        <th>Municipality</th>
        <th>Registered On</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($applicants)): ?>
        <?php foreach ($applicants as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['id']) ?></td>
            <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['middle_name'] . ' ' . $a['last_name']) ?></td>
            <td><?= htmlspecialchars($a['email']) ?></td>
            <td><?= htmlspecialchars($a['contact']) ?></td>
            <td><?= htmlspecialchars($a['gender']) ?></td>
            <td><?= htmlspecialchars($a['city']) ?></td>
            <td><?= htmlspecialchars($a['municipality']) ?></td>
            <td><?= htmlspecialchars($a['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No applicants found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
