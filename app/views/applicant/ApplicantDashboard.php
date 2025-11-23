<?php
// Expected data passed from controller (documented for integration):
// $applicationsInterview = [ ['company'=>'Acme Inc','position'=>'Developer','date'=>'2025-11-01'], ... ];
// $applicationsReview    = [ ['id'=>1,'company'=>'Globex','position'=>'Analyst'], ... ];
// $applicationsRejected  = [ ['id'=>2,'company'=>'Umbrella','position'=>'Designer'], ... ];
// $companiesApproved     = [
//   [
//     'id'=>1,
//     'name'=>'Acme Inc',
//     'website'=>'https://acme.test',
//     'mission'=>'Innovate sustainably',
//     'vision'=>'Global impact',
//     'story'=>'Founded in 2020 ...',
//     'positions'=>[
//        ['id'=>10,'title'=>'Backend Developer','requirements'=>['PHP','MySQL','Git'], 'description'=>'Maintain APIs and optimize DB.'],
//        ['id'=>11,'title'=>'UI/UX Designer','requirements'=>['Figma','Prototyping'], 'description'=>'Design user-centric interfaces.']
//     ]
//   ]
// ];
// $applicant            = ['first_name'=>'Juan','middle_name'=>'','last_name'=>'Dela Cruz','email'=>'juan@test.com','gender'=>'Male','contact'=>'+639123456789','birthdate'=>'2000-01-10','status'=>'approved'];
// $profile              = ['street'=>'','barangay'=>'','municipality'=>'','province'=>'','resume'=>''];
// $isProfileComplete    = false; // controller should compute based on required fields
// $isApplicantApproved  = ($applicant['status'] ?? '') === 'approved';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Applicant Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
	:root{
		--bg-main:#f8fafc;
		--bg-card:#ffffff;
		--text-primary:#0f172a;
		--text-secondary:#475569;
		--text-muted:#94a3b8;
		--primary:#3b82f6;
		--primary-hover:#2563eb;
		--primary-light:#dbeafe;
		--border:#e2e8f0;
		--border-hover:#cbd5e1;
		--success:#10b981;
		--danger:#ef4444;
		--warning:#f59e0b;
		--sidebar-bg:#1e293b;
		--sidebar-text:#cbd5e1;
		--sidebar-hover:#334155;
		--shadow-sm:0 1px 2px 0 rgba(0,0,0,.05);
		--shadow-md:0 4px 6px -1px rgba(0,0,0,.1);
		--shadow-lg:0 10px 15px -3px rgba(0,0,0,.1);
	}
	*{box-sizing:border-box;margin:0;padding:0}
	body{
		font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
		background:var(--bg-main);
		color:var(--text-primary);
		font-size:14px;
		line-height:1.6;
	}
	.layout{display:flex;min-height:100vh}
	
	/* Sidebar */
	nav{
		width:260px;
		background:var(--sidebar-bg);
		color:var(--sidebar-text);
		display:flex;
		flex-direction:column;
		padding:24px 16px;
		box-shadow:var(--shadow-lg);
	}
	nav h2{
		color:#fff;
		font-weight:700;
		font-size:18px;
		margin-bottom:32px;
		padding:0 12px;
		letter-spacing:-0.02em;
	}
	.menu-btn{
		display:flex;
		align-items:center;
		gap:12px;
		color:var(--sidebar-text);
		text-decoration:none;
		padding:12px 14px;
		border-radius:10px;
		transition:all .2s;
		font-weight:500;
		font-size:14px;
		background:transparent;
		border:none;
		cursor:pointer;
		text-align:left;
		width:100%;
		margin-bottom:4px;
	}
	.menu-btn:hover{background:var(--sidebar-hover);color:#fff}
	.menu-btn.active{background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(59,130,246,.3)}
	.logout{margin-top:auto}
	
	/* Main Content */
	main{flex:1;padding:32px 40px;overflow-y:auto}
	h1{
		font-size:28px;
		font-weight:700;
		margin-bottom:8px;
		color:var(--text-primary);
		letter-spacing:-0.02em;
	}
	.subtitle{color:var(--text-secondary);font-size:15px;margin-bottom:28px}
	
	/* Alerts/Notes */
	.top-note{
		padding:14px 16px;
		border-radius:10px;
		background:#fef3c7;
		border:1px solid #fde047;
		color:#713f12;
		font-size:13px;
		margin-bottom:20px;
		display:flex;
		align-items:center;
		gap:10px;
	}
	.top-note strong{color:#92400e;font-weight:600}
	
	/* Sections */
	.section{display:none}
	.section.active{display:block}
	
	/* Tabs */
	.subtabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
	.subtab-btn{
		padding:10px 18px;
		background:#f1f5f9;
		border:1px solid var(--border);
		border-radius:8px;
		cursor:pointer;
		font-size:13px;
		font-weight:500;
		color:var(--text-primary);
		transition:all .2s;
	}
	.subtab-btn.active,.subtab-btn:hover{
		background:var(--primary);
		color:#fff;
		border-color:var(--primary);
		box-shadow:0 2px 6px rgba(59,130,246,.2);
	}
	
	/* Tables */
	table{
		width:100%;
		border-collapse:separate;
		border-spacing:0;
		background:var(--bg-card);
		border:1px solid var(--border);
		border-radius:12px;
		overflow:hidden;
		box-shadow:var(--shadow-sm);
	}
	th,td{
		padding:14px 16px;
		text-align:left;
		font-size:13px;
		border-bottom:1px solid var(--border);
	}
	th{
		background:#f8fafc;
		font-weight:600;
		color:var(--text-secondary);
		text-transform:uppercase;
		font-size:11px;
		letter-spacing:0.05em;
	}
	tbody tr{transition:background .15s}
	tbody tr:hover{background:#f8fafc}
	tr:last-child td{border-bottom:none}
	table a{color:var(--primary);text-decoration:none;font-weight:500;transition:color .2s}
	table a:hover{color:var(--primary-hover);text-decoration:underline}
	
	/* Cards */
	.card-grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
	.company-card{
		background:var(--bg-card);
		border:1px solid var(--border);
		border-radius:12px;
		padding:20px;
		display:flex;
		flex-direction:column;
		gap:12px;
		position:relative;
		transition:all .2s;
		box-shadow:var(--shadow-sm);
	}
	.company-card:hover{
		box-shadow:var(--shadow-md);
		transform:translateY(-2px);
		border-color:var(--border-hover);
	}
	.company-card h3{margin:0;font-size:17px;font-weight:600;color:var(--text-primary)}
	.company-card p{font-size:13px;color:var(--text-secondary);margin:0;line-height:1.5}
	
	/* Detail Pane */
	.detail-pane{
		display:none;
		margin-top:20px;
		padding:24px;
		background:var(--bg-card);
		border:1px solid var(--border);
		border-radius:12px;
		box-shadow:var(--shadow-sm);
	}
	.detail-pane.active{display:block}
	.detail-pane h2{font-size:22px;font-weight:600;margin-bottom:16px;color:var(--text-primary)}
	.detail-pane h3{font-size:18px;font-weight:600;margin:20px 0 12px;color:var(--text-primary)}
	.detail-pane p{font-size:14px;color:var(--text-secondary);line-height:1.6}
	
	/* Positions */
	.positions{margin:20px 0;display:flex;flex-direction:column;gap:12px}
	.position-item{
		border:1px solid var(--border);
		border-radius:10px;
		padding:16px;
		background:#f8fafc;
		transition:all .2s;
	}
	.position-item:hover{background:#f1f5f9;border-color:var(--border-hover)}
	.position-header{display:flex;justify-content:space-between;align-items:center}
	.position-header h4{font-size:15px;font-weight:600;margin:0;color:var(--text-primary)}
	.requirements,.job-desc{
		margin:12px 0 0;
		font-size:13px;
		color:var(--text-secondary);
		display:none;
		line-height:1.5;
	}
	.requirements.active,.job-desc.active{display:block}
	
	/* Buttons */
	.btn{
		background:var(--primary);
		color:#fff;
		border:none;
		padding:10px 20px;
		font-size:13px;
		border-radius:8px;
		cursor:pointer;
		font-weight:600;
		transition:all .2s;
		box-shadow:var(--shadow-sm);
	}
	.btn:hover{
		background:var(--primary-hover);
		box-shadow:var(--shadow-md);
		transform:translateY(-1px);
	}
	.btn:active{transform:translateY(0)}
	.btn.outline{
		background:#fff;
		color:var(--danger);
		border:1px solid var(--danger);
	}
	.btn.outline:hover{
		background:var(--danger);
		color:#fff;
	}
	.btn[disabled]{
		background:#cbd5e1;
		cursor:not-allowed;
		box-shadow:none;
	}
	.btn[disabled]:hover{transform:none}
	
	/* Apply Bar */
	.apply-bar{margin-top:25px;text-align:right}
	
	/* Settings */
	.settings-nav{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
	.settings-tab-btn{
		padding:10px 18px;
		background:#f1f5f9;
		border:1px solid var(--border);
		border-radius:8px;
		font-size:13px;
		cursor:pointer;
		font-weight:500;
		color:var(--text-primary);
		transition:all .2s;
	}
	.settings-tab-btn.active,.settings-tab-btn:hover{
		background:var(--primary);
		color:#fff;
		border-color:var(--primary);
	}
	.settings-panel{
		display:none;
		background:var(--bg-card);
		border:1px solid var(--border);
		border-radius:12px;
		padding:24px;
		box-shadow:var(--shadow-sm);
	}
	.settings-panel.active{display:block}
	.settings-panel h2{font-size:20px;font-weight:600;margin-bottom:20px;color:var(--text-primary)}
	.settings-panel h3{font-size:18px;font-weight:600;margin-bottom:16px;color:var(--text-primary)}
	
	/* Forms */
	.form-grid{display:grid;gap:18px;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))}
	.form-grid label{
		font-size:13px;
		font-weight:600;
		color:var(--text-primary);
		display:block;
		margin-bottom:8px;
	}
	.form-grid input,
	.form-grid select,
	.form-grid textarea{
		width:100%;
		padding:11px 14px;
		font-size:14px;
		line-height:1.5;
		border:1.5px solid var(--border);
		border-radius:8px;
		background:#fff;
		transition:all .2s;
		font-family:inherit;
		color:var(--text-primary);
	}
	.form-grid input:focus,
	.form-grid select:focus,
	.form-grid textarea:focus{
		outline:none;
		border-color:var(--primary);
		box-shadow:0 0 0 3px var(--primary-light);
	}
	.form-grid input::placeholder,
	.form-grid textarea::placeholder{
		color:var(--text-muted);
	}
	.form-grid textarea{resize:vertical;min-height:90px}
	.inline-note{
		font-size:12px;
		color:var(--danger);
		margin-top:6px;
		line-height:1.4;
	}
	
	/* Utility */
	.muted{color:var(--text-muted);font-size:13px}
	.flex{display:flex;gap:10px;align-items:center}
	.justify-between{justify-content:space-between}
	.mb-20{margin-bottom:20px}
	.gap-10{gap:10px}
	
	@media (max-width:900px){
		nav{width:220px}
		main{padding:24px 20px}
		.card-grid{grid-template-columns:1fr}
	}
</style>
</head>
<body>
<div class="layout">
	<nav>
		<h2>Dashboard</h2>
		<button class="menu-btn active" data-section="home">Home</button>
		<button class="menu-btn" data-section="companies">Companies</button>
		<button class="menu-btn" data-section="reviews">Reviews</button>
		<button class="menu-btn" data-section="settings">Settings</button>
		<button id="logoutBtn" class="menu-btn logout" style="background:transparent;border:none;cursor:pointer;">Logout</button>
	</nav>
	<main>
		<?php if (!$isApplicantApproved): ?>
			<div class="top-note" style="background:#fff7ed;border-color:#fed7aa;"><strong>Pending Admin Approval:</strong> Your account is not yet approved by admin. You cannot apply for jobs until approved.</div>
		<?php endif; ?>
		<?php if ($isApplicantApproved && !$isProfileComplete): ?>
			<div class="top-note"><strong>Complete Profile Setup:</strong> Kumpletuhin ang personal profile bago makapag-apply sa mga trabaho.</div>
		<?php endif; ?>

		<!-- Home Section -->
		<section id="home" class="section active">
			<h1>My Applications</h1>
			<div class="subtabs">
				<button class="subtab-btn active" data-sub="interview">For Interview</button>
				<button class="subtab-btn" data-sub="review">For Review</button>
				<button class="subtab-btn" data-sub="passed">Passed</button>
				<button class="subtab-btn" data-sub="rejected">Rejected</button>
			</div>
			<div id="sub-interview" class="sub-section">
				<table>
					<thead><tr><th>Company</th><th>Position</th><th>Date</th></tr></thead>
					<tbody>
						<?php foreach (($applicationsInterview ?? []) as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['company_name'] ?? '') ?></td>
								<td><?= htmlspecialchars($row['position'] ?? '') ?></td>
								<td><?= htmlspecialchars($applicant['schedule_date'] ?? 'TBA') ?></td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($applicationsInterview)): ?>
							<tr><td colspan="3" class="muted">No interview schedules yet.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div id="sub-review" class="sub-section" style="display:none;">
				<table>
					<thead><tr><th>Company</th><th>Position</th><th>Action</th></tr></thead>
					<tbody>
						<?php foreach (($applicationsReview ?? []) as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['company_name'] ?? '') ?></td>
								<td><?= htmlspecialchars($row['position'] ?? '') ?></td>
								<td>
									<form action="<?= site_url('/applicant/application/delete') ?>" method="POST" onsubmit="return confirm('Delete this application?');">
										<input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
										<button class="btn outline" type="submit">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($applicationsReview)): ?>
							<tr><td colspan="3" class="muted">No applications under review.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div id="sub-passed" class="sub-section" style="display:none;">
				<table>
					<thead><tr><th>Company</th><th>Position</th><th>Status</th></tr></thead>
					<tbody>
						<?php foreach (($applicationsPassed ?? []) as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['company_name'] ?? '') ?></td>
								<td><?= htmlspecialchars($row['position'] ?? '') ?></td>
								<td><span style="color:#16a34a;font-weight:bold;">✅ Passed</span></td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($applicationsPassed)): ?>
							<tr><td colspan="3" class="muted">No passed applications yet.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
			<div id="sub-rejected" class="sub-section" style="display:none;">
				<table>
					<thead><tr><th>Company</th><th>Position</th><th>Action</th></tr></thead>
					<tbody>
						<?php foreach (($applicationsRejected ?? []) as $row): ?>
							<tr>
								<td><?= htmlspecialchars($row['company_name'] ?? '') ?></td>
								<td><?= htmlspecialchars($row['position'] ?? '') ?></td>
								<td>
									<form action="<?= site_url('/applicant/application/delete') ?>" method="POST" onsubmit="return confirm('Delete this record?');">
										<input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
										<button class="btn outline" type="submit">Delete</button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php if (empty($applicationsRejected)): ?>
							<tr><td colspan="3" class="muted">No rejected applications.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</section>

		<!-- Companies Section -->
		<section id="companies" class="section">
			<h1>Companies</h1>
			
			<!-- Search Bar -->
			<div style="margin-bottom:20px;">
				<input type="text" id="jobSearchInput" placeholder="Search for job positions (e.g., Front-end Developer, Full Stack, Designer...)" 
				       style="width:100%;max-width:600px;padding:12px 16px;font-size:14px;border:1px solid var(--border);border-radius:10px;background:#fff;">
			</div>
			
			<div class="card-grid" id="companiesGrid">
				<?php foreach (($companiesApproved ?? []) as $c): ?>
					<div class="company-card" data-company-id="<?= (int)$c['id'] ?>" data-positions="<?= htmlspecialchars(json_encode(array_column($c['positions'] ?? [], 'title'))) ?>">
						<?php if(!empty($c['avatar'])): ?>
							<img src="<?= htmlspecialchars(base_url() . 'uploads/logos/' . rawurlencode($c['avatar'])) ?>" alt="Logo" style="width:60px;height:60px;object-fit:cover;border-radius:12px;border:1px solid var(--border);" />
						<?php endif; ?>
						<h3><?= htmlspecialchars($c['name']) ?></h3>
						<p class="muted" style="margin:0;">Website: <a href="<?= htmlspecialchars($c['website']) ?>" target="_blank"><?= htmlspecialchars($c['website']) ?></a></p>
						<button class="btn" data-view-company="<?= (int)$c['id'] ?>">View</button>
					</div>
				<?php endforeach; ?>
				<?php if (empty($companiesApproved)): ?>
					<p class="muted">No approved companies available.</p>
				<?php endif; ?>
			</div>

			<!-- Company Detail Pane -->
			<div id="companyDetail" class="detail-pane">
				<button class="btn outline" id="closeCompanyDetail" style="float:right;">Close</button>
				<div style="clear:both"></div>
				<div id="companyDetailContent"></div>
			</div>
		</section>

		<!-- Reviews Section -->
		<section id="reviews" class="section">
			<h1>Reviews & Ratings</h1>
			<p class="subtitle">Share your experience with our job portal</p>

			<?php
			$this->call->model('ReviewModel');
			$reviewModel = new ReviewModel();
			$hasReviewed = $reviewModel->hasUserReviewed($_SESSION['applicant_id'] ?? 0, 'applicant');
			$allReviews = $reviewModel->getAllReviews();
			$stats = $reviewModel->getAverageRating();
			$distribution = $reviewModel->getRatingDistribution();
			?>

			<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px">
				<!-- Submit Review Card -->
				<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow-sm)">
					<h2 style="font-size:20px;font-weight:600;margin-bottom:16px">Submit Your Review</h2>
					<?php if ($hasReviewed): ?>
						<div style="padding:20px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#065f46;text-align:center">
							<p style="margin:0;font-size:14px">✓ Thank you! You have already submitted a review.</p>
						</div>
					<?php else: ?>
						<form method="post" action="<?= site_url('review/submit') ?>">
							<div style="margin-bottom:20px">
								<label style="display:block;font-size:14px;font-weight:600;margin-bottom:10px;color:var(--text-primary)">Rating *</label>
								<div class="star-rating" style="display:flex;gap:8px;font-size:32px;cursor:pointer">
									<span class="star" data-value="1">☆</span>
									<span class="star" data-value="2">☆</span>
									<span class="star" data-value="3">☆</span>
									<span class="star" data-value="4">☆</span>
									<span class="star" data-value="5">☆</span>
								</div>
								<input type="hidden" name="rating" id="ratingValue" required>
							</div>
							<div style="margin-bottom:20px">
								<label style="display:block;font-size:14px;font-weight:600;margin-bottom:8px;color:var(--text-primary)">Comment (Optional)</label>
								<textarea name="comment" rows="4" placeholder="Share your experience..." style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;font-family:inherit;resize:vertical"></textarea>
							</div>
							<button type="submit" class="btn" style="width:100%">Submit Review</button>
						</form>
					<?php endif; ?>
				</div>

				<!-- Rating Statistics Card -->
				<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow-sm)">
					<h2 style="font-size:20px;font-weight:600;margin-bottom:16px">Overall Rating</h2>
					<div style="text-align:center;margin-bottom:24px">
						<div style="font-size:48px;font-weight:700;color:var(--primary)">
							<?= number_format($stats['avg_rating'] ?? 0, 1) ?>
						</div>
						<div style="font-size:24px;color:#fbbf24;margin:8px 0">
							<?php
							$avgRating = round($stats['avg_rating'] ?? 0);
							for ($i = 1; $i <= 5; $i++) {
								echo $i <= $avgRating ? '★' : '☆';
							}
							?>
						</div>
						<div style="font-size:13px;color:var(--text-muted)">
							Based on <?= number_format($stats['total_reviews'] ?? 0) ?> reviews
						</div>
					</div>
					<div style="font-size:13px">
						<?php
						$distMap = [];
						foreach ($distribution as $d) {
							$distMap[$d['rating']] = $d['count'];
						}
						for ($i = 5; $i >= 1; $i--):
							$count = $distMap[$i] ?? 0;
							$percentage = ($stats['total_reviews'] ?? 0) > 0 ? ($count / $stats['total_reviews']) * 100 : 0;
						?>
							<div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
								<span style="width:60px;font-weight:500"><?= $i ?> stars</span>
								<div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden">
									<div style="width:<?= $percentage ?>%;height:100%;background:#fbbf24"></div>
								</div>
								<span style="width:40px;text-align:right;color:var(--text-muted)"><?= $count ?></span>
							</div>
						<?php endfor; ?>
					</div>
				</div>
			</div>

			<!-- All Reviews List -->
			<div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px;box-shadow:var(--shadow-sm)">
				<h2 style="font-size:20px;font-weight:600;margin-bottom:20px">User Reviews</h2>
				<?php if (empty($allReviews)): ?>
					<p style="text-align:center;color:var(--text-muted);padding:40px 0">No reviews yet. Be the first to share your experience!</p>
				<?php else: ?>
					<div style="display:flex;flex-direction:column;gap:16px">
						<?php foreach ($allReviews as $review): ?>
							<div style="padding:16px;background:#f8fafc;border:1px solid var(--border);border-radius:10px">
								<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
									<div>
										<div style="font-weight:600;font-size:14px;color:var(--text-primary)"><?= htmlspecialchars($review['user_name']) ?></div>
										<div style="font-size:12px;color:var(--text-muted)"><?= date('M d, Y', strtotime($review['created_at'])) ?></div>
									</div>
									<div style="color:#fbbf24;font-size:16px">
										<?php
										for ($i = 1; $i <= 5; $i++) {
											echo $i <= $review['rating'] ? '★' : '☆';
										}
										?>
									</div>
								</div>
								<?php if (!empty($review['comment'])): ?>
									<p style="margin:0;font-size:14px;color:var(--text-secondary);line-height:1.6"><?= htmlspecialchars($review['comment']) ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<!-- Settings Section -->
		<section id="settings" class="section">
			<h1>Settings</h1>
			<div class="settings-nav">
				<button class="settings-tab-btn active" data-settings="profile">Personal Profile</button>
				<button class="settings-tab-btn" data-settings="account">My Account</button>
				<button class="settings-tab-btn" data-settings="password">Change Password</button>
			</div>
			<!-- Personal Profile -->
			<div id="set-profile" class="settings-panel active">
				<h3 style="margin-top:0;">Personal Profile</h3>
				<form action="<?= site_url('/applicant/profile/save') ?>" method="POST" enctype="multipart/form-data" class="form-grid">
					<div>
						<label>First Name</label>
						<input type="text" name="first_name" value="<?= htmlspecialchars($applicant['first_name'] ?? '') ?>" readonly />
					</div>
					<div>
						<label>Middle Name</label>
						<input type="text" name="middle_name" value="<?= htmlspecialchars($applicant['middle_name'] ?? '') ?>" readonly />
					</div>
						<div>
						<label>Last Name</label>
						<input type="text" name="last_name" value="<?= htmlspecialchars($applicant['last_name'] ?? '') ?>" readonly />
					</div>
					<div>
						<label>Email</label>
						<input type="email" value="<?= htmlspecialchars($applicant['email'] ?? '') ?>" readonly />
					</div>
					<div>
						<label>Birthdate</label>
						<input type="text" value="<?= htmlspecialchars($applicant['birthdate'] ?? '') ?>" readonly />
					</div>
					<div>
						<label>Gender</label>
						<input type="text" name="gender" value="<?= htmlspecialchars($applicant['gender'] ?? '') ?>" />
					</div>
					<div>
						<label>Contact</label>
						<input type="text" name="contact" value="<?= htmlspecialchars($applicant['contact'] ?? '') ?>" />
					</div>
					<div>
						<label>Street</label>
						<input type="text" name="street" value="<?= htmlspecialchars($profile['street'] ?? '') ?>" />
					</div>
					<div>
						<label>Barangay</label>
						<input type="text" name="barangay" value="<?= htmlspecialchars($profile['barangay'] ?? '') ?>" />
					</div>
					<div>
						<label>Municipality</label>
						<input type="text" name="municipality" value="<?= htmlspecialchars($profile['municipality'] ?? '') ?>" />
					</div>
					<div>
						<label>Province</label>
						<input type="text" name="province" value="<?= htmlspecialchars($profile['province'] ?? '') ?>" />
					</div>
					<!-- Resume upload removed from profile per request -->
					<div style="grid-column:1/-1; text-align:right;">
						<button class="btn" type="submit">Save Profile</button>
					</div>
				</form>
			</div>

			<!-- My Account -->
			<div id="set-account" class="settings-panel">
				<h3 style="margin-top:0;">My Account</h3>
				<form action="<?= site_url('/applicant/account/update') ?>" method="POST" class="form-grid" id="accountForm">
					<div>
						<label>First Name</label>
						<input type="text" name="first_name" value="<?= htmlspecialchars($applicant['first_name'] ?? '') ?>" />
					</div>
					<div>
						<label>Middle Name</label>
						<input type="text" name="middle_name" value="<?= htmlspecialchars($applicant['middle_name'] ?? '') ?>" />
					</div>
					<div>
						<label>Last Name</label>
						<input type="text" name="last_name" value="<?= htmlspecialchars($applicant['last_name'] ?? '') ?>" />
					</div>
					<div>
						<label>Email</label>
						<input type="email" name="email" value="<?= htmlspecialchars($applicant['email'] ?? '') ?>" />
					</div>
					<div>
						<label>Contact</label>
						<input type="text" name="contact" value="<?= htmlspecialchars($applicant['contact'] ?? '') ?>" />
					</div>
					<div style="grid-column:1/-1;text-align:right;">
						<button class="btn" type="submit">Update Account</button>
					</div>
				</form>
				<p class="muted" style="margin-top:8px;">Changes here will reflect in Personal Profile automatically.</p>
			</div>

			<!-- Change Password -->
			<div id="set-password" class="settings-panel">
				<h3 style="margin-top:0;">Change Password</h3>
				<form action="<?= site_url('applicant/account/change-password') ?>" method="POST" class="form-grid" id="pwdForm">
					<div>
						<label>Current Password</label>
						<input type="password" name="current_password" id="currentPassword" required />
					</div>
					<div>
						<label>New Password</label>
						<input type="password" name="new_password" id="newPassword" required />
					</div>
					<div>
						<label>Confirm New Password</label>
						<input type="password" name="confirm_password" id="confirmPassword" required />
					</div>
					<div id="pwdHint" style="grid-column:1/-1;display:none;font-size:12px;color:#dc2626;">Password must be at least 8 characters.</div>
					<div style="grid-column:1/-1;text-align:right;">
						<button class="btn" type="submit">Change Password</button>
					</div>
				</form>
			</div>
		</section>

	</main>
</div>

<script>
// Navigation between main sections
const sectionButtons = document.querySelectorAll('.menu-btn[data-section]');
const sections = document.querySelectorAll('.section');
sectionButtons.forEach(btn => {
	btn.addEventListener('click', () => {
		sectionButtons.forEach(b=>b.classList.remove('active'));
		btn.classList.add('active');
		sections.forEach(sec=>sec.classList.remove('active'));
		document.getElementById(btn.dataset.section).classList.add('active');
		history.pushState(null,'','#'+btn.dataset.section); // lightweight hash navigation
	});
});
// Restore hash
if(location.hash){
	const target = document.querySelector(`.menu-btn[data-section='${location.hash.substring(1)}']`);
	if(target) target.click();
}

// Subtabs logic for Home
const subtabBtns = document.querySelectorAll('.subtab-btn');
const subSections = { interview:document.getElementById('sub-interview'), review:document.getElementById('sub-review'), passed:document.getElementById('sub-passed'), rejected:document.getElementById('sub-rejected') };
subtabBtns.forEach(st => {
	st.addEventListener('click', () => {
		subtabBtns.forEach(x=>x.classList.remove('active'));
		st.classList.add('active');
		Object.values(subSections).forEach(el=>el.style.display='none');
		subSections[st.dataset.sub].style.display='block';
	});
});

// Settings tabs
const settingsBtns = document.querySelectorAll('.settings-tab-btn');
const settingsPanels = { profile:document.getElementById('set-profile'), account:document.getElementById('set-account'), password:document.getElementById('set-password') };
settingsBtns.forEach(btn => {
	btn.addEventListener('click', () => {
		settingsBtns.forEach(b=>b.classList.remove('active'));
		btn.classList.add('active');
		Object.values(settingsPanels).forEach(p=>p.classList.remove('active'));
		settingsPanels[btn.dataset.settings].classList.add('active');
	});
});

// Company detail viewing
const companyCards = document.querySelectorAll('[data-view-company]');
const companyDetailPane = document.getElementById('companyDetail');
const companyDetailContent = document.getElementById('companyDetailContent');
const closeCompanyDetail = document.getElementById('closeCompanyDetail');
closeCompanyDetail.addEventListener('click', ()=>{ companyDetailPane.classList.remove('active'); companyDetailContent.innerHTML=''; });

// Preload companies data into JS for dynamic rendering (controller should json_encode)
		const companiesData = <?php echo json_encode($companiesApproved ?? []); ?>;
const BASE_URL = "<?php echo rtrim(base_url(), '/'); ?>";
// Applicant approval state (controls Apply buttons availability)
const applicantApproved = <?php echo json_encode($isApplicantApproved ?? false); ?>;

companyCards.forEach(card => {
	card.addEventListener('click', () => {
		const id = parseInt(card.dataset.viewCompany,10);
		const company = companiesData.find(c=>parseInt(c.id,10)===id);
		if(!company){ return; }
		const positionsHtml = (company.positions||[]).map(pos=>`
			<div class='position-item' data-position-id='${pos.id}'>
				<div class='position-header'>
					<strong>${pos.title}</strong>
					<div class='flex gap-10'>
						<button type='button' class='btn outline btn-view-job' data-job='${pos.id}'>View Job</button>
						<button type='button' class='btn btn-apply' data-company-id='${company.id}' data-position-id='${pos.id}' data-position-title='${(pos.title||'').replace(/"/g,'&quot;')}' ${applicantApproved ? '' : 'disabled'}>Apply</button>
					</div>
				</div>
				<div class='requirements' id='req-${pos.id}'>
					<strong>Requirements:</strong>
					<ul style='margin:6px 0 10px; padding-left:18px;'>${(pos.requirements||[]).map(r=>`<li>${r}</li>`).join('')}</ul>
					<div class='job-desc'><strong>Description:</strong> <div style='margin-top:4px;'>${pos.description||''}</div></div>
				</div>
				<p class='muted' style='margin:8px 0 0;'>${applicantApproved ? 'You can apply directly.' : 'Account not yet approved by admin.'}</p>
			</div>`).join('');
		companyDetailContent.innerHTML = `
			${company.avatar ? `<img src='${BASE_URL}/uploads/logos/` + encodeURIComponent(company.avatar) + `' alt='Logo' style='width:80px;height:80px;object-fit:cover;border-radius:16px;border:1px solid var(--border);margin-bottom:12px;' />` : ''}
			<h2 style='margin:0 0 10px;'>${company.name}</h2>
			<p style='margin:4px 0;'><a href='${company.website}' target='_blank'>${company.website}</a></p>
			<div style='margin-top:12px;'>
				<p><strong>Mission:</strong> ${company.mission||''}</p>
				<p><strong>Vision:</strong> ${company.vision||''}</p>
				<p><strong>Story:</strong> ${company.story||''}</p>
			</div>
			<h3 style='margin:18px 0 8px;'>Hiring Positions</h3>
			<div class='positions'>${positionsHtml || '<p class="muted">No positions listed.</p>'}</div>
			`;
		companyDetailPane.classList.add('active');
		initViewJobToggles();
		initApplyButtons();
	});
});

function initViewJobToggles(){
	const buttons = companyDetailPane.querySelectorAll('.btn-view-job');
	buttons.forEach(b => {
		b.addEventListener('click', () => {
			companyDetailPane.querySelectorAll('.requirements').forEach(r=>{ if(r.id!==`req-${b.dataset.job}`){ r.classList.remove('active'); r.querySelector('.job-desc')?.classList.remove('active'); } });
			const req = document.getElementById('req-'+b.dataset.job);
			if(req){ req.classList.toggle('active'); req.querySelector('.job-desc')?.classList.toggle('active'); }
		});
	});
}

// Apply modal logic
let applyModal, applyForm, applyCompanyInput, applyPositionInput, applyFileInput, applyClose;
function ensureApplyModal(){
		if(document.getElementById('applyModal')) return;
		const modal = document.createElement('div');
		modal.id = 'applyModal';
		modal.style.position='fixed';
		modal.style.inset='0';
		modal.style.background='rgba(15,23,42,.5)';
		modal.style.display='none';
		modal.style.alignItems='center';
		modal.style.justifyContent='center';
		modal.innerHTML = `
			<div style="width:100%;max-width:520px;background:#fff;border-radius:16px;border:1px solid var(--border);padding:20px 22px;box-shadow:0 20px 48px rgba(2,6,23,.25);">
				<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
					<h3 style="margin:0;font-size:18px;color:#1e293b">Submit Application</h3>
					<button id="applyClose" class="btn outline" type="button">Close</button>
				</div>
				<form id="applyForm" action="<?= site_url('/applicant/apply') ?>" method="POST" enctype="multipart/form-data">
						<input type="hidden" name="company_id" id="applyCompany" />
						<input type="hidden" name="position_id" id="applyPosition" />
						<input type="hidden" name="position_title" id="applyPositionTitle" />
					<div class="field">
						<label class="label">Resume (PDF, max 2MB)</label>
						<input class="input" type="file" name="resume" id="applyResume" accept="application/pdf" required />
						<div class="muted" style="font-size:12px;margin-top:6px;line-height:1.4">
							This resume will be sent directly to the employer and stored for review.<br>
							Required: PDF format only, maximum size 2MB.
						</div>
					</div>
					<div style="text-align:right;margin-top:8px">
						<button class="btn" type="submit">Send Application</button>
					</div>
				</form>
			</div>`;
		document.body.appendChild(modal);
}

function initApplyButtons(){
	ensureApplyModal();
	applyModal = document.getElementById('applyModal');
	applyForm = document.getElementById('applyForm');
	applyCompanyInput = document.getElementById('applyCompany');
	applyPositionInput = document.getElementById('applyPosition');
	applyFileInput = document.getElementById('applyResume');
	applyClose = document.getElementById('applyClose');

	// Profile completeness + approval flags from server
	const profileComplete = <?= $isProfileComplete ? 'true' : 'false' ?>;
	const applicantApproved = <?= $isApplicantApproved ? 'true' : 'false' ?>;

	// Disable all apply buttons if not approved or profile incomplete
	companyDetailPane.querySelectorAll('.btn-apply').forEach(btn => {
		if(!applicantApproved || !profileComplete){
			btn.setAttribute('disabled','disabled');
			btn.title = !applicantApproved ? 'Account not yet approved by admin.' : 'Kumpletuhin muna ang Personal Profile.';
		}
	});

	companyDetailPane.querySelectorAll('.btn-apply').forEach(btn => {
		btn.addEventListener('click', () => {
			if (btn.hasAttribute('disabled')) { notify(btn.title || 'Hindi pa pwedeng mag-apply.', 'error'); return; }
			applyCompanyInput.value = btn.dataset.companyId;
			applyPositionInput.value = btn.dataset.positionId;
			document.getElementById('applyPositionTitle').value = btn.dataset.positionTitle || '';
			applyFileInput.value = '';
			applyModal.style.display = 'flex';
		});
	});
	applyClose?.addEventListener('click', ()=>{ applyModal.style.display='none'; });
	applyModal?.addEventListener('click', (e)=>{ if(e.target===applyModal){ applyModal.style.display='none'; } });

	applyForm?.addEventListener('submit', (e)=>{
		const f = applyFileInput.files[0];
		if(!f){ e.preventDefault(); notify('Please attach your resume (PDF).', 'error'); return; }
		const okType = f.type === 'application/pdf' || (f.name||'').toLowerCase().endsWith('.pdf');
		const okSize = f.size <= 2*1024*1024;
		if(!okType){ e.preventDefault(); notify('Resume must be a PDF file.', 'error'); }
		else if(!okSize){ e.preventDefault(); notify('Resume must be 2MB or smaller.', 'error'); }
	});
}

// Job search functionality
const jobSearchInput = document.getElementById('jobSearchInput');
const companiesGrid = document.getElementById('companiesGrid');
if(jobSearchInput && companiesGrid){
	jobSearchInput.addEventListener('input', function(){
		const searchTerm = this.value.toLowerCase().trim();
		const companyCards = companiesGrid.querySelectorAll('.company-card');
		let visibleCount = 0;
		
		companyCards.forEach(card => {
			const positionsData = card.getAttribute('data-positions');
			let hasMatch = false;
			
			if(searchTerm === ''){
				hasMatch = true;
			} else {
				try {
					const positions = JSON.parse(positionsData || '[]');
					hasMatch = positions.some(pos => pos.toLowerCase().includes(searchTerm));
				} catch(e) {
					hasMatch = false;
				}
			}
			
			if(hasMatch){
				card.style.display = '';
				visibleCount++;
			} else {
				card.style.display = 'none';
			}
		});
		
		// Show "no results" message
		let noResults = companiesGrid.querySelector('.no-results-msg');
		if(visibleCount === 0 && searchTerm !== ''){
			if(!noResults){
				noResults = document.createElement('p');
				noResults.className = 'muted no-results-msg';
				noResults.textContent = 'No companies found with that job position.';
				companiesGrid.appendChild(noResults);
			}
			noResults.style.display = 'block';
		} else {
			if(noResults) noResults.style.display = 'none';
		}
	});
}

// Password change hint
const newPwd = document.getElementById('newPassword');
const confirmPwd = document.getElementById('confirmPassword');
const currentPwd = document.getElementById('currentPassword');
const pwdHint = document.getElementById('pwdHint');
const pwdForm = document.getElementById('pwdForm');

if(newPwd && pwdHint){
	newPwd.addEventListener('input', () => {
		const l = newPwd.value.length;
		pwdHint.style.display = (l>0 && l<8) ? 'block' : 'none';
	});
}

if(pwdForm){
	pwdForm.addEventListener('submit', e => {
		e.preventDefault();
		const cur = currentPwd?.value || ''; 
		const np = newPwd?.value || ''; 
		const cp = confirmPwd?.value || '';
		
		// Validation 1: Check all fields filled
		if(!cur.trim()){ notify('Please enter your current password.', 'error'); return; }
		if(!np.trim()){ notify('Please enter a new password.', 'error'); return; }
		if(!cp.trim()){ notify('Please confirm your new password.', 'error'); return; }
		
		// Validation 2: New password minimum length
		if(np.length < 8){ notify('New password must be at least 8 characters.', 'error'); return; }
		
		// Validation 3: New password matches confirm
		if(np !== cp){ notify('New password and confirm password do not match.', 'error'); return; }
		
		// Validation 4: New password cannot be same as current
		if(np === cur){ notify('New password cannot be the same as your current password.', 'error'); return; }
		
		// All validations passed - submit form
		pwdForm.submit();
	});
}

// Lightweight toast notifications
function notify(message, type='info'){
	let cont = document.getElementById('toastContainer');
	if(!cont){
		cont = document.createElement('div');
		cont.id = 'toastContainer';
		cont.style.position='fixed';
		cont.style.top='16px';
		cont.style.right='16px';
		cont.style.display='flex';
		cont.style.flexDirection='column';
		cont.style.gap='8px';
		cont.style.zIndex='9999';
		document.body.appendChild(cont);
	}
	const toast = document.createElement('div');
	toast.style.minWidth='260px';
	toast.style.maxWidth='360px';
	toast.style.padding='12px 14px';
	toast.style.borderRadius='10px';
	toast.style.border='1px solid #e5e7eb';
	toast.style.boxShadow='0 10px 30px rgba(2,6,23,.15)';
	toast.style.background = type==='error' ? '#fee2e2' : (type==='success' ? '#ecfeff' : '#f8fafc');
	toast.style.color = '#0f172a';
	toast.textContent = message;
	cont.appendChild(toast);
	setTimeout(()=>{ toast.style.opacity='0'; toast.style.transition='opacity .4s'; setTimeout(()=>toast.remove(), 400); }, 3500);
}

<?php if(!empty($_SESSION['success'])): ?>
	notify(<?= json_encode($_SESSION['success']) ?>, 'success');
	<?php $_SESSION['success']=null; unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if(!empty($_SESSION['error'])): ?>
	notify(<?= json_encode($_SESSION['error']) ?>, 'error');
	<?php $_SESSION['error']=null; unset($_SESSION['error']); ?>
<?php endif; ?>

// Logout button
document.getElementById('logoutBtn').addEventListener('click', function(e){
	e.preventDefault();
	fetch('<?= site_url('/logout') ?>', {method:'GET', credentials:'same-origin'})
		.then(()=> window.location.href='<?= site_url('/') ?>')
		.catch(()=> window.location.href='<?= site_url('/') ?>');
});

// Star rating system
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('ratingValue');
if(stars.length > 0 && ratingInput){
	let selectedRating = 0;
	
	stars.forEach(star => {
		star.addEventListener('click', function(){
			selectedRating = parseInt(this.getAttribute('data-value'));
			ratingInput.value = selectedRating;
			updateStars(selectedRating);
		});
		
		star.addEventListener('mouseenter', function(){
			const hoverValue = parseInt(this.getAttribute('data-value'));
			updateStars(hoverValue);
		});
	});
	
	document.querySelector('.star-rating').addEventListener('mouseleave', function(){
		updateStars(selectedRating);
	});
	
	function updateStars(rating){
		stars.forEach(star => {
			const starValue = parseInt(star.getAttribute('data-value'));
			if(starValue <= rating){
				star.textContent = '★';
				star.style.color = '#fbbf24';
			} else {
				star.textContent = '☆';
				star.style.color = '#d1d5db';
			}
		});
	}
}
</script>
</body>
</html>
