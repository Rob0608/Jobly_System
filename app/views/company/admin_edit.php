<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$type = isset($type) ? $type : '';
$record = isset($record) ? $record : [];
$status = $record['status'] ?? '';
$id = $record['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Edit</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{ --bg:#f3f4f6; --card:#fff; --border:#e5e7eb; --text:#0f172a; --muted:#64748b; --primary:#1d4ed8; }
        *{box-sizing:border-box; font-family:"Poppins",Arial,Helvetica,sans-serif}
        body{margin:0;background:var(--bg);color:var(--text)}
        .wrap{max-width:860px;margin:32px auto;padding:0 16px}
        .panel{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px}
        .title{margin:0 0 12px;font-size:22px;font-weight:600}
        .row{display:grid;grid-template-columns:160px 1fr;gap:10px;margin:6px 0}
        .muted{color:var(--muted)}
        .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:1px solid var(--border);background:#fff;cursor:pointer}
        .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
        .btn-danger{background:#b91c1c;border-color:#b91c1c;color:#fff}
        .btn-warning{background:#f59e0b;border-color:#f59e0b;color:#111827}
        .actions{margin-top:16px;display:flex;gap:10px}
        a{color:var(--primary);text-decoration:none}
        .flash{margin-bottom:12px;padding:10px;border-radius:8px}
        .flash-success{background:#ecfdf5;border:1px solid #bbf7d0;color:#065f46}
        .flash-error{background:#fef2f2;border:1px solid #fecaca;color:#7f1d1d}
    </style>
</head>
<body>
<div class="wrap">
    <div style="margin-bottom:12px"><a href="<?php echo isset($this) ? site_url('company/dashboard') . '?tab=home' : '/company/dashboard?tab=home'; ?>">← Back to Home</a></div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="flash flash-success"><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="flash flash-error"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="panel">
        <h1 class="title"><?php echo ucfirst(h($type)); ?> Account</h1>
        <div class="row"><div class="muted">ID</div><div><?php echo h($id); ?></div></div>
        <?php if($type === 'applicant'): ?>
            <div class="row"><div class="muted">Name</div><div><?php echo h(($record['first_name'] ?? '') . ' ' . ($record['last_name'] ?? '')); ?></div></div>
            <div class="row"><div class="muted">Email</div><div><?php echo h($record['email'] ?? ''); ?></div></div>
        <?php else: ?>
            <div class="row"><div class="muted">Company</div><div><?php echo h($record['company_name'] ?? ''); ?></div></div>
            <div class="row"><div class="muted">Email</div><div><?php echo h($record['email'] ?? ''); ?></div></div>
        <?php endif; ?>
        <div class="row"><div class="muted">Status</div><div><strong><?php echo h($status ?: ''); ?></strong></div></div>

        <div class="actions">
            <?php if($status === 'deactivated'): ?>
                <form method="post" action="<?php echo isset($this) ? site_url('admin/activate') : '/admin/activate'; ?>">
                    <input type="hidden" name="type" value="<?php echo h($type); ?>">
                    <input type="hidden" name="id" value="<?php echo h($id); ?>">
                    <button type="submit" class="btn btn-primary">Activate</button>
                </form>
            <?php else: ?>
                <form method="post" action="<?php echo isset($this) ? site_url('admin/deactivate') : '/admin/deactivate'; ?>" onsubmit="return confirm('Deactivate this account?');">
                    <input type="hidden" name="type" value="<?php echo h($type); ?>">
                    <input type="hidden" name="id" value="<?php echo h($id); ?>">
                    <button type="submit" class="btn btn-danger">Deactivate</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
