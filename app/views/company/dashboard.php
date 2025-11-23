<?php
// Admin Dashboard view
// Shows Applicants and Employers lists, pending applications, manage account, and logout

// Fallback sample data when the controller doesn't supply data
$applicants = isset($applicants) ? $applicants : [
	['id'=>1,'name'=>'Alice Smith','email'=>'alice@example.com'],
	['id'=>2,'name'=>'Bob Johnson','email'=>'bob@example.com'],
];
$employers = isset($employers) ? $employers : [
	['id'=>1,'name'=>'Acme Corp','email'=>'hr@acme.example'],
	['id'=>2,'name'=>'Globex Ltd','email'=>'contact@globex.example'],
];
$pendingApplicants = isset($pendingApplicants) ? $pendingApplicants : [
	['id'=>3,'name'=>'Charlie Pending','email'=>'charlie.p@example.com'],
];
$pendingEmployers = isset($pendingEmployers) ? $pendingEmployers : [
	['id'=>3,'name'=>'NewCo Pending','email'=>'apply@newco.example'],
];

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'home';

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function display_name($r){
	if (isset($r['first_name']) || isset($r['last_name'])){
		$fn = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
		return h($fn ?: ($r['email'] ?? ''));
	}
	if (isset($r['company_name'])) return h($r['company_name']);
	if (isset($r['name'])) return h($r['name']);
	return h($r['email'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
		<meta charset="utf-8">
		<title>Admin Dashboard</title>
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
						--sidebar:#0b1220;
				}
				*{box-sizing:border-box; font-family:"Poppins", Arial, Helvetica, sans-serif;}
				body{margin:0; background:var(--bg); color:var(--text);}        
				.layout{display:flex; min-height:100vh;}
				.sidebar{width:240px; background:var(--sidebar); color:#e2e8f0; display:flex; flex-direction:column; padding:22px 16px;}
				.brand{font-weight:600; font-size:18px; color:#fff; margin-bottom:16px;}
				.nav-list{display:flex; flex-direction:column; gap:8px; margin-top:8px;}
				.nav-link{display:block; padding:10px 12px; color:#cbd5e1; text-decoration:none; border-radius:8px; transition:.2s;}
				.nav-link:hover{background:rgba(255,255,255,.06); color:#fff;}
				.nav-link.active{background:var(--primary-600); color:#fff;}
				.logout{margin-top:auto;}

				.content{flex:1; padding:28px 36px;}
				h1{margin:0 0 18px; font-size:24px; font-weight:600;}
				.container{max-width:1200px;}
				.panel{background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px;}
				.two-col{display:grid; grid-template-columns:1fr 1fr; gap:20px}
				table{width:100%; border-collapse:collapse; margin-top:10px}
				th,td{border:1px solid var(--border); padding:10px; text-align:left; font-size:13px}
				th{background:#f8fafc}
				.actions button, .actions a{margin-right:6px}
				form.inline{display:inline}
				.success{color:#16a34a}
				.danger{color:#b91c1c}
				.alert{margin-bottom:16px; padding:10px 12px; border-radius:10px;}
				.alert-success{background:#ecfdf5; border:1px solid #bbf7d0; color:#166534}
				.alert-error{background:#fef2f2; border:1px solid #fecaca; color:#7f1d1d}
				@media (max-width: 980px){ .two-col{grid-template-columns:1fr;} .sidebar{width:200px;} }
		</style>
		<script>
			// Make active state persistent based on tab param
			function setActiveLink(){
				const params = new URLSearchParams(location.search);
				const tab = params.get('tab') || 'home';
				document.querySelectorAll('.nav-link').forEach(a=>{
					a.classList.toggle('active', a.dataset.tab === tab);
				});
			}
			document.addEventListener('DOMContentLoaded', setActiveLink);
		</script>
</head>
<body>
<div class="layout">
	<aside class="sidebar">
		<div class="brand">Admin Console</div>
		<nav class="nav-list">
			<a class="nav-link" data-tab="home" href="?tab=home">Home</a>
			<a class="nav-link" data-tab="pending" href="?tab=pending">Pending Application</a>
			<a class="nav-link" data-tab="deactivated" href="?tab=deactivated">Deactivated Accounts</a>
			<a class="nav-link" data-tab="reviews" href="?tab=reviews">Reviews</a>
			<a class="nav-link" data-tab="manage" href="?tab=manage">Manage Account</a>
		</nav>
		<button id="logoutBtn" class="nav-link logout" style="text-align:left; background:transparent; border:none; cursor:pointer;">Logout</button>
	</aside>
	<main class="content">
		<div class="container">
			<h1>Admin Dashboard</h1>

	<?php if (isset($_SESSION['success'])): ?>
		<div class="panel success" style="margin-bottom:16px"><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></div>
	<?php endif; ?>
	<?php if (isset($_SESSION['error'])): ?>
		<div class="panel" style="margin-bottom:16px; color:#7f1d1d; background:#fff4f4; border-color:#fecaca"><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></div>
	<?php endif; ?>

	<script>setActiveLink();</script>

	<?php if($tab === 'home'): ?>
		<div class="two-col">
			<div class="panel">
				<h2>Applicants</h2>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Last Login</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php foreach($applicants as $a): ?>
						<tr>
							<td><?php echo display_name($a); ?></td>
							<td><?php echo h($a['email']); ?></td>
							<td><?php echo !empty($a['last_login']) ? h($a['last_login']) : '<span style="color:var(--muted)">Never</span>'; ?></td>
							<td class="actions">
								<a href="<?php echo isset($this) ? site_url('admin/edit') : '/admin/edit'; ?>?type=applicant&id=<?php echo urlencode($a['id']); ?>">Edit</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="panel">
				<h2>Employers</h2>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Last Login</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php foreach($employers as $e): ?>
						<tr>
							<td><?php echo display_name($e); ?></td>
							<td><?php echo h($e['email']); ?></td>
							<td><?php echo !empty($e['last_login']) ? h($e['last_login']) : '<span style="color:var(--muted)">Never</span>'; ?></td>
							<td class="actions">
								<a href="<?php echo isset($this) ? site_url('admin/edit') : '/admin/edit'; ?>?type=employer&id=<?php echo urlencode($e['id']); ?>">Edit</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

	<?php elseif($tab === 'pending'): ?>
		<div class="two-col">
			<div class="panel">
				<h2>Pending Applicants</h2>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php foreach($pendingApplicants as $p): ?>
						<tr>
							<td><?php echo display_name($p); ?></td>
							<td><?php echo h($p['email']); ?></td>
							<td class="actions">
								<form class="inline approve-form" method="post" action="<?php echo isset($this) ? site_url('admin/approve') : '/admin/approve'; ?>">
									<input type="hidden" name="type" value="applicant">
									<input type="hidden" name="id" value="<?php echo h($p['id']); ?>">
									<button type="submit">Approve</button>
								</form>
								<form class="inline confirm-delete" method="post" action="<?php echo isset($this) ? site_url('admin/deletePending') : '/admin/deletePending'; ?>">
									<input type="hidden" name="type" value="applicant">
									<input type="hidden" name="id" value="<?php echo h($p['id']); ?>">
									<button type="submit" class="danger">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="panel">
				<h2>Pending Employers</h2>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php foreach($pendingEmployers as $p): ?>
						<tr>
							<td><?php echo display_name($p); ?></td>
							<td><?php echo h($p['email']); ?></td>
							<td class="actions">
								<?php if (!empty($p['business_permit'])): ?>
									<a href="<?php echo h(base_url() . $p['business_permit']); ?>" target="_blank" title="View Business Permit">📄 Review</a>
									&nbsp;|&nbsp;
								<?php endif; ?>
								<form class="inline approve-form" method="post" action="<?php echo isset($this) ? site_url('admin/approve') : '/admin/approve'; ?>">
									<input type="hidden" name="type" value="employer">
									<input type="hidden" name="id" value="<?php echo h($p['id']); ?>">
									<button type="submit">Approve</button>
								</form>
								<form class="inline confirm-delete" method="post" action="<?php echo isset($this) ? site_url('admin/deletePending') : '/admin/deletePending'; ?>">
									<input type="hidden" name="type" value="employer">
									<input type="hidden" name="id" value="<?php echo h($p['id']); ?>">
									<button type="submit" class="danger">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

	<?php elseif($tab === 'deactivated'): ?>
		<div class="two-col">
			<div class="panel">
				<h2>Deactivated Applicants</h2>
				<p style="font-size:13px;color:var(--muted);margin-bottom:12px">When these users login again, they will be automatically reactivated without admin approval.</p>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Deactivated On</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php 
					$deactivatedApplicants = isset($deactivatedApplicants) ? $deactivatedApplicants : [];
					foreach($deactivatedApplicants as $a): ?>
						<tr>
							<td><?php echo display_name($a); ?></td>
							<td><?php echo h($a['email']); ?></td>
							<td><?php echo !empty($a['updated_at']) ? h($a['updated_at']) : '<span style="color:var(--muted)">N/A</span>'; ?></td>
							<td class="actions">
								<form class="inline confirm-delete" method="post" action="<?php echo isset($this) ? site_url('admin/deletePermanent') : '/admin/deletePermanent'; ?>">
									<input type="hidden" name="type" value="applicant">
									<input type="hidden" name="id" value="<?php echo h($a['id']); ?>">
									<button type="submit" class="danger">Delete Permanently</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if(empty($deactivatedApplicants)): ?>
						<tr><td colspan="4" style="text-align:center;color:var(--muted)">No deactivated applicants</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="panel">
				<h2>Deactivated Employers</h2>
				<p style="font-size:13px;color:var(--muted);margin-bottom:12px">When these users login again, they will be automatically reactivated without admin approval.</p>
				<table>
					<thead>
						<tr><th>Name</th><th>Email</th><th>Deactivated On</th><th>Action</th></tr>
					</thead>
					<tbody>
					<?php 
					$deactivatedEmployers = isset($deactivatedEmployers) ? $deactivatedEmployers : [];
					foreach($deactivatedEmployers as $e): ?>
						<tr>
							<td><?php echo display_name($e); ?></td>
							<td><?php echo h($e['email']); ?></td>
							<td><?php echo !empty($e['updated_at']) ? h($e['updated_at']) : '<span style="color:var(--muted)">N/A</span>'; ?></td>
							<td class="actions">
								<form class="inline confirm-delete" method="post" action="<?php echo isset($this) ? site_url('admin/deletePermanent') : '/admin/deletePermanent'; ?>">
									<input type="hidden" name="type" value="employer">
									<input type="hidden" name="id" value="<?php echo h($e['id']); ?>">
									<button type="submit" class="danger">Delete Permanently</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if(empty($deactivatedEmployers)): ?>
						<tr><td colspan="4" style="text-align:center;color:var(--muted)">No deactivated employers</td></tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>

	<?php elseif($tab === 'reviews'): ?>
		<div class="panel">
			<h2>User Reviews & Ratings</h2>

			<!-- Rating Statistics -->
			<div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;margin-bottom:32px">
				<div style="background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:24px;text-align:center">
					<div style="font-size:48px;font-weight:700;color:var(--primary)">
						<?= number_format($reviewStats['avg_rating'] ?? 0, 1) ?>
					</div>
					<div style="font-size:24px;color:#fbbf24;margin:8px 0">
						<?php
						$avgRating = round($reviewStats['avg_rating'] ?? 0);
						for ($i = 1; $i <= 5; $i++) {
							echo $i <= $avgRating ? '★' : '☆';
						}
						?>
					</div>
					<div style="font-size:13px;color:var(--muted)">
						Based on <?= number_format($reviewStats['total_reviews'] ?? 0) ?> reviews
					</div>
				</div>

				<div style="background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:24px">
					<h3 style="margin:0 0 16px;font-size:16px;font-weight:600">Rating Distribution</h3>
					<?php
					$distMap = [];
					foreach ($reviewDistribution as $d) {
						$distMap[$d['rating']] = $d['count'];
					}
					for ($i = 5; $i >= 1; $i--):
						$count = $distMap[$i] ?? 0;
						$percentage = ($reviewStats['total_reviews'] ?? 0) > 0 ? ($count / $reviewStats['total_reviews']) * 100 : 0;
					?>
						<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
							<span style="width:60px;font-weight:500;font-size:13px"><?= $i ?> stars</span>
							<div style="flex:1;height:10px;background:#e5e7eb;border-radius:5px;overflow:hidden">
								<div style="width:<?= $percentage ?>%;height:100%;background:#fbbf24;transition:width 0.3s"></div>
							</div>
							<span style="width:60px;text-align:right;color:var(--muted);font-size:13px"><?= $count ?> reviews</span>
						</div>
					<?php endfor; ?>
				</div>
			</div>

			<!-- All Reviews List -->
			<h3 style="margin-bottom:16px">All Reviews</h3>
			<table>
				<thead>
					<tr>
						<th>User</th>
						<th>Type</th>
						<th>Rating</th>
						<th>Comment</th>
						<th>Date</th>
					</tr>
				</thead>
				<tbody>
				<?php if (empty($allReviews)): ?>
					<tr><td colspan="5" style="text-align:center;color:var(--muted)">No reviews yet.</td></tr>
				<?php else: ?>
					<?php foreach ($allReviews as $review): ?>
						<tr>
							<td><?= htmlspecialchars($review['user_name']) ?></td>
							<td><span style="text-transform:capitalize"><?= htmlspecialchars($review['user_type']) ?></span></td>
							<td>
								<span style="color:#fbbf24;font-size:14px">
									<?php
									for ($i = 1; $i <= 5; $i++) {
										echo $i <= $review['rating'] ? '★' : '☆';
									}
									?>
								</span>
								<span style="margin-left:4px;color:var(--muted)">(<?= $review['rating'] ?>)</span>
							</td>
							<td style="max-width:400px">
								<?php if (!empty($review['comment'])): ?>
									<?= htmlspecialchars($review['comment']) ?>
								<?php else: ?>
									<span style="color:var(--muted);font-style:italic">No comment</span>
								<?php endif; ?>
							</td>
							<td><?= date('M d, Y g:i A', strtotime($review['created_at'])) ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

	<?php elseif($tab === 'manage'): ?>
		<div class="panel" style="max-width:480px">
			<h2>Manage Account</h2>
			<p>Admin account settings</p>
			<p style="color:#6b7280;font-size:14px">Logged in as: <strong><?= $_SESSION['username'] ?? 'admin' ?></strong></p>
		</div>

	<?php else: ?>
		<p>Unknown tab.</p>
	<?php endif; ?>

		</div>
	</main>
</div>
<script>
document.getElementById('logoutBtn').addEventListener('click', function(e){
	e.preventDefault();
	// Call logout endpoint then redirect to admin login form using framework routes
	fetch('<?php echo isset($this) ? site_url('admin/logout') : '/admin/logout'; ?>', {method:'GET', credentials:'same-origin'})
		.catch(function(){ /* ignore errors */ })
		.finally(function(){ window.location = '<?php echo isset($this) ? site_url('admin') : '/admin'; ?>'; });
});

// Confirm before deleting pending records
document.querySelectorAll('.confirm-delete').forEach(function(form){
    form.addEventListener('submit', function(e){
        var ok = confirm('Are you sure you want to permanently delete this pending record?');
        if(!ok) e.preventDefault();
    });
});

// Intercept approve submits and redirect to Home on success
document.querySelectorAll('.approve-form').forEach(function(form){
	form.addEventListener('submit', function(e){
		e.preventDefault();
		var submitBtn = form.querySelector('button[type="submit"]');
		if(submitBtn){ submitBtn.disabled = true; submitBtn.textContent = 'Approving...'; }
		var fd = new FormData(form);
		// Treat server redirects (302 to dashboard) as success; just navigate to Home afterwards
		fetch(form.action, { method:'POST', body: fd, credentials:'same-origin' })
			.then(function(){ window.location = '?tab=home'; })
			.catch(function(){ alert('Approval failed. Please try again.'); })
			.finally(function(){ if(submitBtn){ submitBtn.disabled = false; submitBtn.textContent = 'Approve'; } });
	});
});
</script>
</body>
</html>
