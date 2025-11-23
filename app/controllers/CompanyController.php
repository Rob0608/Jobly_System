<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Removed unused GoogleMaps import to avoid autoload errors if package changes.

require_once 'app/third_party/PHPMailer/src/Exception.php';
require_once 'app/third_party/PHPMailer/src/PHPMailer.php';
require_once 'app/third_party/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // ✅ make sure autoload works
require_once __DIR__ . '/../helpers/phpmailer_helper.php';

class CompanyController extends Controller
{
    // ✅ Registration Form
    public function register()
    {
        $this->call->view('company/register');
    }

    public function save()
    {
        // Simplified registration: only accept the core fields shown in the form
        // company_name, address (+lat/lng), country_code, phone, email, password, avatar, job_position
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();

        // Basic required fields
        $companyName = trim($_POST['company_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');

        // Validate required fields
        if ($companyName === '' || $address === '' || $email === '' || $phone === '') {
            echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
            exit;
        }

        // Password validation
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($password) || strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
            exit;
        }
        if ($password !== $confirm) {
            echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
            exit;
        }

        // No avatar upload here; registration only stores the core fields.

        $latitude = $_POST['latitude'] ?? '';
        $longitude = $_POST['longitude'] ?? '';
        if (empty($latitude) || empty($longitude)) {
            // Attempt graceful geocoding via Nominatim
            $latitude = '';
            $longitude = '';
            $nominatimUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' . urlencode($address);
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: LavaLustApp/1.0\r\nAccept: application/json\r\n",
                    'timeout' => 5
                ]
            ]);
            try {
                $response = @file_get_contents($nominatimUrl, false, $ctx);
                if ($response !== false) {
                    $geoData = json_decode($response, true);
                    if (!empty($geoData[0]['lat']) && !empty($geoData[0]['lon'])) {
                        $latitude = $geoData[0]['lat'];
                        $longitude = $geoData[0]['lon'];
                    }
                }
            } catch (Exception $e) {
                error_log('Geocode error: ' . $e->getMessage());
            }
        }

        // Prevent duplicate by email
        if ($companyModel->getCompanyByEmail($email)) {
            echo "<script>alert('An account with that email already exists.'); window.history.back();</script>";
            exit;
        }

        // Handle business permit upload
        $businessPermitPath = '';
        if (isset($_FILES['business_permit']) && $_FILES['business_permit']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['business_permit'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileTmp = $file['tmp_name'];
            
            // Validate file
            $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($fileExt, $allowedExtensions)) {
                echo "<script>alert('Only PDF, JPG, and PNG files are allowed.'); window.history.back();</script>";
                exit;
            }
            
            if ($fileSize > $maxSize) {
                echo "<script>alert('File size must not exceed 5MB.'); window.history.back();</script>";
                exit;
            }
            
            // Create uploads directory if not exists
            if (!is_dir('uploads/permits')) {
                mkdir('uploads/permits', 0777, true);
            }
            
            // Save file with unique name
            $uniqueName = 'permit_' . time() . '_' . uniqid() . '.' . $fileExt;
            $uploadPath = 'uploads/permits/' . $uniqueName;
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $businessPermitPath = $uploadPath;
            } else {
                echo "<script>alert('Failed to upload business permit. Please try again.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Please upload a business permit.'); window.history.back();</script>";
            exit;
        }

        $verification_code = rand(1000, 9999);

        $data = [
            'company_name' => $companyName,
            'address' => $address,
            'latitude' => $latitude ?: null,
            'longitude' => $longitude ?: null,
            'phone' => $phone,
            'email' => $email,
            'verification_code' => $verification_code,
            'is_verified' => 0,
            'status' => 'pending',
            'business_permit' => $businessPermitPath
        ];

        $data['password'] = password_hash($password, PASSWORD_BCRYPT);

        // Insert and send verification email
        $companyModel->insertCompany($data);
        $this->sendVerificationMail($data);

        // Use site_url so subdirectory (e.g. /LavaLust-Final/) is respected
        $verifyUrl = site_url('/company/verify') . '?email=' . urlencode($data['email']);
        header('Location: ' . $verifyUrl);
        exit;
    }

    // ✅ Send verification email
    private function sendVerificationMail(array $data)
    {
        $subject = 'Your Company Verification Code';
        $body = "
            <h3>Hello, {$data['company_name']}!</h3>
            <p>Thank you for registering your company.</p>
            <p>Your 4-digit verification code is:</p>
            <h2 style='letter-spacing:5px;'>{$data['verification_code']}</h2>
            <p>Enter this code on the verification page to activate your account.</p>
        ";

        $res = phpmailer_send([
            'to' => [$data['email'] => $data['company_name']],
            'subject' => $subject,
            'body' => $body,
            'from_name' => 'Company Verification'
        ]);
        if (!$res['success']) {
            error_log('Company verification email failed: ' . ($res['error'] ?? json_encode($res)));
        }
    }

    // ✅ Verification Page
    public function verify()
    {
        $email = $_GET['email'] ?? '';
        $this->call->view('company/success', ['email' => $email]);
    }

    // ✅ Verify code submission
    public function verify_code()
    {
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();

        $email = $_POST['email'];
        $code = $_POST['c1'] . $_POST['c2'] . $_POST['c3'] . $_POST['c4'];

        if ($companyModel->verifyCode($email, $code)) {
            echo "<script>alert('✅ Company verified successfully! You can now log in.');
                  window.location.href='" . site_url('/login') . "';</script>";
        } else {
            $retryUrl = site_url('/company/verify') . '?email=' . urlencode($email);
            echo "<script>alert('❌ Invalid verification code. Please try again.');
                  window.location.href='" . $retryUrl . "';</script>";
        }
    }

    // Employer dashboard (company-facing)
    public function employerDashboard()
    {
        // Require employer to be logged in
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') {
            $_SESSION['error'] = 'Please login as employer to access the employer dashboard.';
            redirect('login');
            return;
        }

        // Load models
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');
        $appModel = new ApplicantModel();
        $companyModel = new CompanyModel();

        // Company info from session or DB
        $company = null;
        if (!empty($_SESSION['email'])) {
            $company = $companyModel->getCompanyByEmail($_SESSION['email']);
        } elseif (!empty($_SESSION['company_id'])) {
            $company = $companyModel->getCompanyByEmail($_SESSION['company']['email'] ?? '');
        }

        // Fetch applications from company_applications table for this company
        $pending = [];
        $interviews = [];
        if ($company && isset($company['id'])) {
            try {
                $this->call->database();
                // Get pending applications with applicant details
                $sql = "SELECT ca.*, a.first_name, a.middle_name, a.last_name, a.email, a.contact, a.resume, cj.position
                        FROM company_applications ca
                        LEFT JOIN applicants a ON ca.applicant_email = a.email
                        LEFT JOIN company_jobs cj ON ca.position_id = cj.id
                        WHERE ca.company_id = ? AND ca.status = 'pending'
                        ORDER BY ca.created_at DESC";
                $stmt = $this->db->raw($sql, [(int)$company['id']]);
                $pending = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                // Get interview scheduled applications
                $sqlInterview = "SELECT ca.*, a.first_name, a.middle_name, a.last_name, a.email, a.contact, a.schedule_date, cj.position
                                 FROM company_applications ca
                                 LEFT JOIN applicants a ON ca.applicant_email = a.email
                                 LEFT JOIN company_jobs cj ON ca.position_id = cj.id
                                 WHERE ca.company_id = ? AND ca.status = 'interview'
                                 ORDER BY a.schedule_date ASC";
                $stmtInterview = $this->db->raw($sqlInterview, [(int)$company['id']]);
                $interviews = $stmtInterview->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Exception $e) {
                error_log('EmployerDashboard fetch error: ' . $e->getMessage());
                $pending = [];
                $interviews = [];
            }
        }

        // Jobs list
        $this->call->model('JobModel');
        $jobModel = new JobModel();
        $jobs = [];
        if ($company && isset($company['id'])) {
            $jobs = $jobModel->listByCompany($company['id']);
        }

        $this->call->view('company/EmployerDashboard', [
            'pendingApplications' => $pending,
            'interviews' => $interviews,
            'jobs' => $jobs,
            'company' => $company ?: []
        ]);
    }

    // Update application status
    public function updateApplication()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('company/employer');
            return;
        }
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 'pending';
        $schedule = $_POST['schedule_date'] ?? null;
        $applicantEmail = $_POST['applicant_email'] ?? null;
        $position = $_POST['position'] ?? null;

        if (empty($id)) {
            $_SESSION['error'] = 'Invalid application id.';
            redirect('company/employer');
            return;
        }

        try {
            $this->call->database();
            
            // Update company_applications status
            $this->db->table('company_applications')
                ->where('id', (int)$id)
                ->update(['status' => $status]);

            // If scheduling interview, update applicant's schedule_date and send email
            if ($status === 'interview' && !empty($schedule)) {
                // Get applicant email from application
                $stmt = $this->db->raw('SELECT applicant_email FROM company_applications WHERE id = ? LIMIT 1', [(int)$id]);
                $app = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($app && !empty($app['applicant_email'])) {
                    $this->db->table('applicants')
                        ->where('email', $app['applicant_email'])
                        ->update(['schedule_date' => $schedule]);
                    
                    // Send interview schedule email notification
                    error_log('About to send interview notification to: ' . $app['applicant_email']);
                    $this->sendInterviewNotificationEmail($app['applicant_email'], $schedule, $position ?? 'Position TBA');
                    error_log('Sent interview notification');
                }
            }

            // If passed, send email notification
            if ($status === 'passed' && !empty($applicantEmail)) {
                error_log('About to send passed notification to: ' . $applicantEmail);
                $this->sendPassedNotificationEmail($applicantEmail);
                error_log('Sent passed notification');
            }

            $_SESSION['success'] = 'Application status updated.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update application.';
            error_log('Update application error: ' . $e->getMessage());
        }

        redirect('company/employer');
    }

    // Send email notification when applicant passes
    private function sendPassedNotificationEmail($applicantEmail)
    {
        $subject = 'Congratulations! You Passed the Interview Round';
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Poppins, Arial; background: #f3f4f6; padding: 20px; }
                    .container { background: #fff; padding: 30px; border-radius: 15px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                    h2 { color: #16a34a; margin-top: 0; }
                    p { color: #334155; line-height: 1.6; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>🎉 Congratulations!</h2>
                    <p>Dear Applicant,</p>
                    <p>We're excited to inform you that you have <strong>passed</strong> the interview round with our company!</p>
                    <p>This is a great achievement and shows that you have the skills and qualities we're looking for. We look forward to the next steps in the hiring process.</p>
                    <p>Thank you for your interest in joining our team. If you have any questions, please don't hesitate to contact us.</p>
                    <p><strong>Best regards,</strong><br/>Job Portal Team</p>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply directly to this message.</p>
                    </div>
                </div>
            </body>
            </html>";

        $res = phpmailer_send([
            'to' => $applicantEmail,
            'subject' => $subject,
            'body' => $body,
            'from_name' => 'Job Portal'
        ]);
        if (!$res['success']) {
            error_log('Passed notification email failed: ' . ($res['error'] ?? json_encode($res)));
        }
    }

    // Send interview schedule notification email
    private function sendInterviewNotificationEmail($applicantEmail, $scheduleDate, $position)
    {
        // Format the schedule date for display
        $dateObj = new DateTime($scheduleDate, new DateTimeZone('Asia/Manila'));
        $formattedDate = $dateObj->format('F d, Y h:i A');

        $subject = 'Interview Schedule Notification - Job Portal';
        $body = "
            <html>
            <head>
                <style>
                    body { font-family: Poppins, Arial; background: #f3f4f6; padding: 20px; }
                    .container { background: #fff; padding: 30px; border-radius: 15px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                    h2 { color: #2563eb; margin-top: 0; }
                    p { color: #334155; line-height: 1.6; }
                    .event-box { background: #f0f9ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 8px; }
                    .event-box strong { color: #1e40af; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>📅 Interview Scheduled</h2>
                    <p>Dear Applicant,</p>
                    <p>Great news! Your interview has been scheduled for the position of <strong>{$position}</strong>.</p>
                    <div class='event-box'>
                        <p><strong>📍 Interview Details:</strong></p>
                        <p><strong>Position:</strong> {$position}</p>
                        <p><strong>Date & Time:</strong> {$formattedDate}</p>
                        <p style='margin-bottom: 0;'><strong>Timezone:</strong> Asia/Manila (UTC+8)</p>
                    </div>
                    <p>The interview details have also been added to your Google Calendar. Make sure to check your email for the calendar invite.</p>
                    <p>If you have any questions or need to reschedule, please contact us as soon as possible.</p>
                    <p><strong>Best regards,</strong><br/>Job Portal Team</p>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply directly to this message.</p>
                    </div>
                </div>
            </body>
            </html>";

        $res = phpmailer_send([
            'to' => $applicantEmail,
            'subject' => $subject,
            'body' => $body,
            'from_name' => 'Job Portal'
        ]);
        if (!$res['success']) {
            error_log('Interview notification email failed: ' . ($res['error'] ?? json_encode($res)));
        }
    }

    // Minimal job posting handlers (stubs)
    public function postJob()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') {
            $_SESSION['error'] = 'Please login as employer.';
            redirect('login');
            return;
        }

        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $company = $companyModel->getCompanyByEmail($_SESSION['email'] ?? '');
        if (!$company) { $_SESSION['error'] = 'Company not found.'; redirect('company/employer?tab=post'); return; }

        if (($company['status'] ?? 'pending') !== 'approved') {
            $_SESSION['error'] = 'Your account must be approved to post jobs.';
            redirect('company/employer?tab=post');
            return;
        }

        $position = trim($_POST['position'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($position === '') { $_SESSION['error'] = 'Position is required.'; redirect('company/employer?tab=post'); return; }

        $this->call->model('JobModel');
        $jobModel = new JobModel();
        $jobModel->insertJob($company['id'], $position, $requirements, $description);

        $_SESSION['success'] = 'Job posted.';
        redirect('company/employer?tab=post');
    }

    public function editJob()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') { redirect('login'); return; }
        $id = $_POST['id'] ?? null;
        $position = trim($_POST['position'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!$id || $position==='') { $_SESSION['error'] = 'Invalid job update.'; redirect('company/employer?tab=post'); return; }
        $this->call->model('JobModel');
        $jobModel = new JobModel();
        $jobModel->updateJob($id, $position, $requirements, $description);
        $_SESSION['success'] = 'Job updated.';
        redirect('company/employer?tab=post');
    }

    public function deleteJob()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') { redirect('login'); return; }
        $id = $_POST['id'] ?? null;
        if (!$id) { $_SESSION['error'] = 'Invalid job id.'; redirect('company/employer?tab=post'); return; }
        $this->call->model('JobModel');
        $jobModel = new JobModel();
        $jobModel->deleteJob($id);
        $_SESSION['success'] = 'Job deleted.';
        redirect('company/employer?tab=post');
    }

    public function updateProfile()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') {
            $_SESSION['error'] = 'Please login as employer.';
            redirect('login');
            return;
        }

        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $company = $companyModel->getCompanyByEmail($_SESSION['email'] ?? '');
        if (!$company) {
            $_SESSION['error'] = 'Company record not found.';
            redirect('company/employer?tab=settings');
            return;
        }
        $id = $company['id'];

        $company_name = trim($_POST['company_name'] ?? $company['company_name']);
        $website      = trim($_POST['website'] ?? $company['website']);
        $story        = trim($_POST['story'] ?? $company['story']);
        $mission      = trim($_POST['mission'] ?? $company['mission']);
        $vision       = trim($_POST['vision'] ?? $company['vision']);

        // Avatar upload (robust path + validation + fallback)
        $avatarName = $company['avatar'] ?? '';
        if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $targetDirFs = __DIR__ . '/../../uploads/logos/';
            if (!is_dir($targetDirFs)) { @mkdir($targetDirFs, 0777, true); }

            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp','jfif'];
            $sizeOk = (int)($_FILES['avatar']['size'] ?? 0) <= 2 * 1024 * 1024; // 2MB
            if (in_array($ext, $allowed, true) && $sizeOk) {
                $avatarName = 'logo_' . $id . '_' . time() . '.' . preg_replace('/[^a-z0-9]/','', $ext);
                $ok = @move_uploaded_file($_FILES['avatar']['tmp_name'], $targetDirFs . $avatarName);
                if (!$ok) {
                    // Revert to previous if move failed
                    $avatarName = $company['avatar'] ?? '';
                    $_SESSION['error'] = 'Failed to save uploaded logo. Please try again.';
                }
            } else {
                $_SESSION['error'] = 'Invalid logo file (type/size). Max 2MB; jpg, png, gif, webp.';
            }
        }

        $companyModel->updateProfile($id, [
            'company_name' => $company_name,
            'website'      => $website,
            'story'        => $story,
            'mission'      => $mission,
            'vision'       => $vision,
            'avatar'       => $avatarName
        ]);

        $_SESSION['success'] = 'Company profile saved.';
        redirect('company/employer?tab=settings&sub=profile');
    }

    public function changePassword()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'employer') {
            redirect('login');
            return;
        }
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Validate inputs are not empty
        if (empty($current) || empty($new) || empty($confirm)) {
            $_SESSION['error'] = 'All password fields are required.';
            redirect('company/employer#settings');
            return;
        }

        // Load company first to verify current password early
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $email = $_SESSION['email'] ?? '';
        $company = $companyModel->getCompanyByEmail($email);
        
        if (!$company) {
            $_SESSION['error'] = 'Company record not found.';
            redirect('company/employer#settings');
            return;
        }

        // Verify current password is correct
        // Support both bcrypt hashed and plain text passwords (for backward compatibility)
        $storedPassword = $company['password'];
        $passwordMatch = false;
        
        // Check if it's a bcrypt hash
        if (preg_match('/^\$2[aby]\$/', $storedPassword)) {
            $passwordMatch = password_verify($current, $storedPassword);
        } else {
            // Fall back to plain text comparison (should not happen with new registrations)
            $passwordMatch = ($current === $storedPassword);
        }
        
        error_log('Employer ' . $email . ' password verification: ' . ($passwordMatch ? 'PASS' : 'FAIL'));
        
        if (!$passwordMatch) {
            $_SESSION['error'] = 'Current password is incorrect.';
            redirect('company/employer#settings');
            return;
        }

        // Validate new password length
        if (strlen($new) < 8) {
            $_SESSION['error'] = 'New password must be at least 8 characters.';
            redirect('company/employer#settings');
            return;
        }

        // Validate new password matches confirm
        if ($new !== $confirm) {
            $_SESSION['error'] = 'New password and confirm password do not match.';
            redirect('company/employer#settings');
            return;
        }

        // Check if new password is same as current password
        $newIsSameCurrent = false;
        if (preg_match('/^\$2[aby]\$/', $storedPassword)) {
            $newIsSameCurrent = password_verify($new, $storedPassword);
        } else {
            $newIsSameCurrent = ($new === $storedPassword);
        }
        
        if ($newIsSameCurrent) {
            $_SESSION['error'] = 'New password cannot be the same as current password.';
            redirect('company/employer#settings');
            return;
        }

        // All validations passed - hash and update password
        $hashed = password_hash($new, PASSWORD_BCRYPT);
        $this->call->database();
        $this->db->table('companies')->where('email', $email)->update(['password' => $hashed]);
        error_log('Employer ' . $email . ' password changed successfully');
        $_SESSION['success'] = 'Password changed successfully! Log in again with your new password.';
        redirect('company/employer#settings');
    }
}
