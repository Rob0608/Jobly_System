<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'app/third_party/PHPMailer/src/Exception.php';
require_once 'app/third_party/PHPMailer/src/PHPMailer.php';
require_once 'app/third_party/PHPMailer/src/SMTP.php';
require_once 'app/helpers/phpmailer_helper.php';

class ApplicantController extends Controller
{
    // Show registration form
    public function register()
    {
        $this->call->view('applicant/register');
    }

public function applications()
{
    // Load model
    $this->call->model('ApplicantModel');

    // Get all applicants
    $applicants = $this->ApplicantModel->getAllApplicants();

    // Pass data to view
    $this->call->view('company/applications', ['applicants' => $applicants]);
}



    // Handle form submission + send verification email
    public function save()
    {
        $this->call->model('ApplicantModel');
        $appModel = new ApplicantModel();

        // Basic validation
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';

         // Contact validation: only digits, 10-11 characters (e.g. 9123456789 or 09123456789)
        $contactRaw = trim($_POST['contact'] ?? '');
        if ($contactRaw === '' || !preg_match('/^\d{10,11}$/', $contactRaw)) {
            echo "<script>alert('Invalid contact number. Use digits only (10-11 digits).'); window.history.back();</script>";
            exit;
        }

        // Age validation (must be >= 18) with inline UX (flash message)
        if (empty($birthdate)) {
            if (!isset($_SESSION)) session_start();
            $_SESSION['error_birthdate'] = 'Birthdate is required.';
            header('Location: /applicant/register');
            exit;
        }
        try {
            $dob = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            // Ensure birthdate not in future and age between 18-60
            if ($dob > $today || $age < 18 || $age > 60) {
                if (!isset($_SESSION)) session_start();
                $_SESSION['error_birthdate'] = 'You must be between 18 and 60 years old to register.';
                header('Location: /applicant/register');
                exit;
            }
        } catch (Exception $e) {
            if (!isset($_SESSION)) session_start();
            $_SESSION['error_birthdate'] = 'Invalid birthdate format.';
            header('Location: /applicant/register');
            exit;
        }

        if (empty($password) || strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters.'); window.history.back();</script>";
            exit;
        }
        if ($password !== $confirm) {
            echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
            exit;
        }

        // Hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Handle resume upload
        $resumeFileName = '';
        if (!empty($_FILES['resume']['name'])) {
            $targetDir = "uploads/resumes/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $resumeFileName = time() . '_' . basename($_FILES['resume']['name']);
            move_uploaded_file($_FILES['resume']['tmp_name'], $targetDir . $resumeFileName);
        }

        // Generate 4-digit verification code
        $verification_code = rand(1000, 9999);

        // Prepare data for DB insert
        $data = [
            'first_name'        => $_POST['first_name'] ?? '',
            'middle_name'       => $_POST['middle_name'] ?? null,
            'last_name'         => $_POST['last_name'] ?? '',
            'birthdate'         => $_POST['birthdate'] ?? null,
            'gender'            => $_POST['gender'] ?? '',
            'contact'           => $contactRaw,
            'email'             => strtolower(trim($_POST['email'] ?? '')),
            'birth_place'       => $_POST['birth_place'] ?? '',
            'barangay'          => $_POST['barangay'] ?? '',
            'city'              => $_POST['city'] ?? '',
            'municipality'      => $_POST['municipality'] ?? '',
            'address'           => $_POST['address'] ?? '',
            'resume'            => $resumeFileName,
            'job_title'         => $_POST['job_title'] ?? '',
            'password'          => $hashed,
            'verification_code' => $verification_code,
            'is_verified'       => 0,
            'status'            => 'pending'
        ];

        // Server-side name validation
        $first = trim($data['first_name']);
        $middle = trim((string)$data['middle_name']);
        $last = trim($data['last_name']);
        // Last name is required; allow letters, spaces, hyphen, apostrophe and period (for suffixes)
        if ($last === '') {
            echo "<script>alert('Last name is required.'); window.history.back();</script>";
            exit;
        }
        $namePattern = '/^[\p{L}\s\'\-\.]+$/u';
        if ($first !== '' && !preg_match($namePattern, $first)) {
            echo "<script>alert('First name contains invalid characters.'); window.history.back();</script>";
            exit;
        }
        if ($middle !== '' && !preg_match($namePattern, $middle)) {
            echo "<script>alert('Middle name contains invalid characters.'); window.history.back();</script>";
            exit;
        }
        if (!preg_match($namePattern, $last)) {
            echo "<script>alert('Last name contains invalid characters.'); window.history.back();</script>";
            exit;
        }

       $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        if ($companyModel->getCompanyByEmail($data['email'])) {
            echo "<script>alert('This email is already registered as an employer. Please use a different email.'); window.history.back();</script>";
            exit;
        }

        // Insert applicant
        $appModel->insertApplicant($data);

        // Send verification email
        $this->sendVerificationMail($data);

        // Redirect to verification page using framework URL helper (respects base path/index.php)
        redirect('applicant/verify?email=' . urlencode($data['email']));
        return;
    }

    // ✅ Send verification email (using PHPMailer)
    private function sendVerificationMail(array $data)
    {
        $body = '<h3>Hello, ' . htmlspecialchars($data['first_name'] ?? '') . '!</h3>'
            . '<p>Thank you for registering as an applicant.</p>'
            . '<p>Here is your 4-digit verification code:</p>'
            . '<h2 style="letter-spacing:5px;">' . htmlspecialchars((string)($data['verification_code'] ?? '')) . '</h2>'
            . '<p>Enter this code on the verification page to activate your account.</p>';

        $result = phpmailer_send([
            'to' => $data['email'],
            'to_name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'subject' => 'Your Applicant Verification Code',
            'body' => $body,
            'is_html' => true,
        ]);

        if (!$result['success']) {
            error_log('Verification email failed: ' . ($result['error'] ?? 'unknown'));
        }
    }

    // ✅ Show verification page
    public function verify()
    {
        $email = $_GET['email'] ?? '';
        $this->call->view('applicant/verify', ['email' => $email]);
    }

    // ✅ Handle verification code input
    public function verify_code()
{
    $this->call->model('ApplicantModel');
    $appModel = new ApplicantModel();

    $email = $_POST['email'];
    $code = $_POST['c1'] . $_POST['c2'] . $_POST['c3'] . $_POST['c4'];

    if ($appModel->verifyCode($email, $code)) {
        // ✅ Marked verified: set a success message and redirect to login page
        if (!isset($_SESSION)) session_start();
        $_SESSION['success'] = 'Your account has been verified. You may now log in.';
        redirect('login');
        return;
    } else {
        // ❌ If wrong code, redirect back with error flag
        redirect('applicant/verify?email=' . urlencode($email) . '&error=1');
        return;
    }

}

    // ==================== DASHBOARD + RELATED ACTIONS ====================
    public function dashboard()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }

            $this->call->model('ApplicantModel');
            $this->call->model('CompanyModel');
            $this->call->model('JobModel');
            $appModel = new ApplicantModel();
            $companyModel = new CompanyModel();
            $jobModel = new JobModel();

            $email = $_SESSION['applicant_email'] ?? '';
            $applicant = $email ? $appModel->getApplicantByEmail($email) : [];

            // Fetch approved companies with profile fields and avatar
            $rawCompanies = $companyModel->getAllCompanies();
            $companiesApproved = [];
            foreach ($rawCompanies as $c) {
                if (($c['status'] ?? '') === 'approved') {
                    // Map jobs to positions expected by the view
                    $jobs = $jobModel->listByCompany($c['id']);
                    $positions = [];
                    foreach ($jobs as $j) {
                        $req = $j['requirements'] ?? '';
                        $reqArr = array_filter(array_map('trim', explode(',', (string)$req)));
                        $positions[] = [
                            'id' => $j['id'],
                            'title' => $j['position'],
                            'requirements' => $reqArr,
                            'description' => $j['description'] ?? ''
                        ];
                    }
                    // Fallback: if no rows in company_jobs, parse companies.job_position as comma-separated titles
                    if (empty($positions) && !empty($c['job_position'])) {
                        $raw = array_filter(array_map('trim', explode(',', (string)$c['job_position'])));
                        $i = 1;
                        foreach ($raw as $title) {
                            $positions[] = [
                                'id' => (int)($c['id'] * 1000 + $i++),
                                'title' => $title,
                                'requirements' => [],
                                'description' => ''
                            ];
                        }
                    }
                    $companiesApproved[] = [
                        'id' => $c['id'],
                        'name' => $c['company_name'],
                        'website' => $c['website'] ?? '',
                        'mission' => $c['mission'] ?? '',
                        'vision' => $c['vision'] ?? '',
                        'story' => $c['story'] ?? '',
                        'avatar' => $c['avatar'] ?? '',
                        'positions' => $positions
                    ];
                }
            }

            // Fetch applicant's applications from company_applications table
            $applicationsInterview = [];
            $applicationsReview = [];
            $applicationsRejected = [];
            $applicationsPassed = [];
            
            if ($email) {
                try {
                    $this->call->database();
                    
                    // Interview scheduled applications
                    $sqlInterview = "SELECT ca.*, c.company_name, cj.position, cj.description 
                                    FROM company_applications ca 
                                    LEFT JOIN companies c ON ca.company_id = c.id 
                                    LEFT JOIN company_jobs cj ON ca.position_id = cj.id 
                                    WHERE ca.applicant_email = ? AND ca.status = 'interview'
                                    ORDER BY ca.id DESC";
                    $stmtInterview = $this->db->raw($sqlInterview, [$email]);
                    $applicationsInterview = $stmtInterview->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    
                    // For review (pending) applications
                    $sqlReview = "SELECT ca.*, c.company_name, cj.position, cj.description 
                                 FROM company_applications ca 
                                 LEFT JOIN companies c ON ca.company_id = c.id 
                                 LEFT JOIN company_jobs cj ON ca.position_id = cj.id 
                                 WHERE ca.applicant_email = ? AND ca.status = 'pending'
                                 ORDER BY ca.id DESC";
                    $stmtReview = $this->db->raw($sqlReview, [$email]);
                    $applicationsReview = $stmtReview->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    
                    // Passed applications
                    $sqlPassed = "SELECT ca.*, c.company_name, cj.position, cj.description 
                                 FROM company_applications ca 
                                 LEFT JOIN companies c ON ca.company_id = c.id 
                                 LEFT JOIN company_jobs cj ON ca.position_id = cj.id 
                                 WHERE ca.applicant_email = ? AND ca.status = 'passed'
                                 ORDER BY ca.id DESC";
                    $stmtPassed = $this->db->raw($sqlPassed, [$email]);
                    $applicationsPassed = $stmtPassed->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    
                    // Rejected applications
                    $sqlRejected = "SELECT ca.*, c.company_name, cj.position, cj.description 
                                   FROM company_applications ca 
                                   LEFT JOIN companies c ON ca.company_id = c.id 
                                   LEFT JOIN company_jobs cj ON ca.position_id = cj.id 
                                   WHERE ca.applicant_email = ? AND ca.status = 'rejected'
                                   ORDER BY ca.id DESC";
                    $stmtRejected = $this->db->raw($sqlRejected, [$email]);
                    $applicationsRejected = $stmtRejected->fetchAll(PDO::FETCH_ASSOC) ?: [];
                    
                } catch (Exception $e) {
                    error_log('Fetch applicant applications error: ' . $e->getMessage());
                }
            }

            // Build profile view model from applicants table fields
            $profile = [
                'street' => $applicant['address'] ?? '',
                'barangay' => $applicant['barangay'] ?? '',
                'municipality' => $applicant['municipality'] ?? '',
                'province' => $applicant['city'] ?? ''
            ];
            // Profile completeness: require key address fields only; resume is uploaded during apply
            $isProfileComplete = !empty($applicant['gender'] ?? '')
                && !empty($applicant['contact'] ?? '')
                && !empty($profile['street'])
                && !empty($profile['barangay'])
                && !empty($profile['municipality'])
                && !empty($profile['province']);
            // Applicants must be approved by admin before they can apply
            $isApplicantApproved = ($applicant['status'] ?? '') === 'approved';

            $data = compact(
                'applicationsInterview',
                'applicationsReview',
                'applicationsPassed',
                'applicationsRejected',
                'companiesApproved',
                'applicant',
                'profile',
                'isProfileComplete',
                'isApplicantApproved'
            );
            $this->call->view('applicant/ApplicantDashboard', $data);
        }

        // Save profile details (address). Resume upload removed as per new flow
        public function saveProfile()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }
            // Persist to applicants table (mapping: street -> address, province -> city) + gender/contact
            $street     = trim($_POST['street'] ?? '');
            $barangay   = trim($_POST['barangay'] ?? '');
            $municipality = trim($_POST['municipality'] ?? '');
            $province   = trim($_POST['province'] ?? '');
            $gender     = trim($_POST['gender'] ?? '');
            $contact    = trim($_POST['contact'] ?? '');
            
            // Validate contact: digits only and 10-11 characters
            if ($contact !== '' && !preg_match('/^\d{10,11}$/', $contact)) {
                $_SESSION['error'] = 'Invalid contact number. Use digits only (10-11 digits).';
                redirect('applicant/dashboard#settings');
                return;
            }

            $email      = $_SESSION['applicant_email'] ?? '';

            if (empty($email)) { redirect('login'); return; }

            try {
                $this->call->database();
                $updateData = [
                    'address'      => $street,
                    'barangay'     => $barangay,
                    'municipality' => $municipality,
                    'city'         => $province,
                    'gender'       => $gender,
                    'contact'      => $contact
                ];
                $this->db->table('applicants')->where('email', $email)->update($updateData);
                $_SESSION['success'] = 'Profile saved.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Failed to save profile.';
            }
            redirect('applicant/dashboard#settings');
        }

        // Update account (basic fields). Placeholder implementation
        public function updateAccount()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }
            // TODO: Update applicant row in DB
            $_SESSION['success'] = 'Account updated (placeholder).';
            redirect('applicant/dashboard#settings');
        }

        public function changePassword()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            // Validate inputs are not empty
            if (empty($current) || empty($new) || empty($confirm)) {
                $_SESSION['error'] = 'All password fields are required.';
                redirect('applicant/dashboard#settings');
                return;
            }

            // Load applicant first to verify current password early
            $this->call->model('ApplicantModel');
            $appModel = new ApplicantModel();
            $email = $_SESSION['applicant_email'] ?? '';
            $applicant = $appModel->getApplicantByEmail($email);
            
            if (!$applicant) {
                $_SESSION['error'] = 'Applicant record not found.';
                redirect('applicant/dashboard#settings');
                return;
            }

            // Verify current password is correct
            // Support both bcrypt hashed and plain text passwords (for backward compatibility)
            $storedPassword = $applicant['password'];
            $passwordMatch = false;
            
            // Check if it's a bcrypt hash
            if (preg_match('/^\$2[aby]\$/', $storedPassword)) {
                $passwordMatch = password_verify($current, $storedPassword);
            } else {
                // Fall back to plain text comparison (should not happen with new registrations)
                $passwordMatch = ($current === $storedPassword);
            }
            
            error_log('Applicant ' . $email . ' password verification: ' . ($passwordMatch ? 'PASS' : 'FAIL'));
            
            if (!$passwordMatch) {
                $_SESSION['error'] = 'Current password is incorrect.';
                redirect('applicant/dashboard#settings');
                return;
            }

            // Validate new password length
            if (strlen($new) < 8) {
                $_SESSION['error'] = 'New password must be at least 8 characters.';
                redirect('applicant/dashboard#settings');
                return;
            }

            // Validate new password matches confirm
            if ($new !== $confirm) {
                $_SESSION['error'] = 'New password and confirm password do not match.';
                redirect('applicant/dashboard#settings');
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
                redirect('applicant/dashboard#settings');
                return;
            }

            // All validations passed - hash and update password
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $this->call->database();
            $this->db->table('applicants')->where('email', $email)->update(['password' => $hashed]);
            error_log('Applicant ' . $email . ' password changed successfully');
            $_SESSION['success'] = 'Password changed successfully! Log in again with your new password.';
            redirect('applicant/dashboard#settings');
        }

        // Apply to a position with resume upload + optional message (emails employer)
        public function apply()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }
            $companyId = (int)($_POST['company_id'] ?? 0);
            $positionId = (int)($_POST['position_id'] ?? 0);

            if ($companyId <= 0 || $positionId <= 0) {
                $_SESSION['error'] = 'Invalid application request.';
                redirect('applicant/dashboard');
                return;
            }

            // Load applicant for completeness validation first
            $this->call->model('ApplicantModel');
            $appModel = new ApplicantModel();
            $applicantEmail = $_SESSION['applicant_email'] ?? '';
            $applicant = $appModel->getApplicantByEmail($applicantEmail);
            if (!$applicant) { $_SESSION['error'] = 'Applicant record not found.'; redirect('applicant/dashboard#settings'); return; }

            // Required fields list (must all be non-empty)
            $required = [
                'first_name','middle_name','last_name','birthdate','gender','contact',
                'address','barangay','municipality','city'
            ];
            $incomplete = [];
            foreach ($required as $f) {
                if (empty(trim($applicant[$f] ?? ''))) { $incomplete[] = $f; }
            }
            if (!empty($incomplete)) {
                $_SESSION['error'] = 'Kumpletuhin muna ang iyong personal profile bago mag-apply. Kulang: ' . implode(', ', $incomplete);
                redirect('applicant/dashboard#settings');
                return;
            }

            if (empty($_FILES['resume']['name']) || !is_uploaded_file($_FILES['resume']['tmp_name'])) {
                $_SESSION['error'] = 'Please attach your resume (PDF).';
                redirect('applicant/dashboard');
                return;
            }

            // Validate and store resume
            $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf'];
            $sizeOk = (int)($_FILES['resume']['size'] ?? 0) <= 2 * 1024 * 1024; // 2MB
            if (!in_array($ext, $allowed, true) || !$sizeOk) {
                $_SESSION['error'] = 'Resume must be a PDF up to 2MB.';
                redirect('applicant/dashboard');
                return;
            }

            $targetDirFs = __DIR__ . '/../../uploads/resumes/';
            if (!is_dir($targetDirFs)) { @mkdir($targetDirFs, 0777, true); }

            // Safe filename generation
            $safeBase = preg_replace('/[^a-z0-9]/i','_', pathinfo($_FILES['resume']['name'], PATHINFO_FILENAME));
            $fileName = 'resume_' . $safeBase . '_' . time() . '.pdf';
            if (!@move_uploaded_file($_FILES['resume']['tmp_name'], $targetDirFs . $fileName)) {
                $_SESSION['error'] = 'Failed to save resume. Please try again.';
                redirect('applicant/dashboard');
                return;
            }

            // Load applicant and company for email context
            $this->call->model('CompanyModel');
            $companyModel = new CompanyModel();
            // Safely call getById (method_exists check) and fallback raw query if needed
            if (method_exists($companyModel, 'getById')) {
                $company = $companyModel->getById($companyId);
            } else {
                $company = null;
            }
            if (!$company) {
                try {
                    $this->call->database();
                    $stmt = $this->db->raw('SELECT * FROM companies WHERE id = ? LIMIT 1', [$companyId]);
                    $company = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
                } catch (Exception $e) { $company = []; }
            }

            // Email the employer with the resume attached
            $sent = false;
            try {
                $toEmail = $company['email'] ?? '';
                if ($toEmail) {
                    $body = "<p>You have a new application.</p>"
                          . "<p><strong>Applicant:</strong> " . htmlspecialchars(($applicant['first_name'] ?? '') . ' ' . ($applicant['last_name'] ?? '')) . " (" . htmlspecialchars($applicant['email'] ?? '') . ")</p>"
                          . "<p><strong>Position ID:</strong> " . (int)$positionId . "</p>";

                    $res = phpmailer_send([
                        'to' => $toEmail,
                        'to_name' => $company['company_name'] ?? 'Employer',
                        'subject' => 'New Application: ' . ($applicant['first_name'] ?? 'Applicant') . ' - Position #' . $positionId,
                        'body' => $body,
                        'is_html' => true,
                        'attachments' => [['path' => $targetDirFs . $fileName, 'name' => 'Resume.pdf']],
                    ]);
                    if ($res['success']) { $sent = true; }
                }
            } catch (Exception $e) {
                // keep going; we'll still record application locally
                error_log('Application email send exception: ' . $e->getMessage());
            }

            // Insert application record in company_applications table
            try {
                $this->call->database();
                // Determine job title for display
                $positionTitle = '';
                try {
                    $stmt = $this->db->raw('SELECT position FROM company_jobs WHERE id = ? LIMIT 1', [$positionId]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $positionTitle = $row['position'] ?? '';
                } catch (Exception $e) { /* ignore */ }
                if ($positionTitle === '' && !empty($_POST['position_title'])) {
                    $positionTitle = trim($_POST['position_title']);
                }

                $resumePath = 'uploads/resumes/' . $fileName;
                
                // Store application in company_applications table
                $this->db->table('company_applications')->insert([
                    'company_id' => $companyId,
                    'applicant_email' => $applicantEmail,
                    'position_id' => $positionId,
                    'resume' => $resumePath,
                    'message' => $_POST['message'] ?? '',
                    'status' => 'pending'
                ]);
                
                // Update applicant's resume (but DON'T change status - preserve 'approved')
                $this->db->table('applicants')->where('email', $applicantEmail)->update([
                    'resume' => $resumePath,
                    'job_title' => $positionTitle
                ]);
            } catch (Exception $e) {
                error_log('Application save error: ' . $e->getMessage());
            }

            $_SESSION['success'] = $sent ? 'Application sent to employer.' : 'Application recorded. The employer will review it.';
            redirect('applicant/dashboard');
        }

        public function deleteApplication()
        {
            if (!isset($_SESSION)) session_start();
            if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'applicant') {
                redirect('login');
                return;
            }
            
            $id = $_POST['id'] ?? null;
            $applicantEmail = $_SESSION['applicant_email'] ?? '';
            
            if (empty($id) || empty($applicantEmail)) {
                $_SESSION['error'] = 'Invalid request.';
                redirect('applicant/dashboard');
                return;
            }
            
            try {
                $this->call->database();
                // Delete only if the application belongs to the logged-in applicant
                $this->db->table('company_applications')
                    ->where('id', (int)$id)
                    ->where('applicant_email', $applicantEmail)
                    ->delete();
                    
                $_SESSION['success'] = 'Application deleted successfully.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Failed to delete application.';
                error_log('Delete application error: ' . $e->getMessage());
            }
            
            redirect('applicant/dashboard');
        }
}
