<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

// PHPMailer (used for password reset emails here)
require_once 'app/third_party/PHPMailer/src/Exception.php';
require_once 'app/third_party/PHPMailer/src/PHPMailer.php';
require_once 'app/third_party/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'app/helpers/phpmailer_helper.php';

class AuthController extends Controller
{
    // === ADMIN LOGIN ===
    public function adminLogin()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['role'] === 'admin') {
            redirect('company/dashboard');
        }

        // ✅ show the admin login form, not the dashboard
        $this->call->view('admin_login');
    }

    public function processAdminLogin()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $username;
            $_SESSION['email'] = 'admin@jobportal.com';
            redirect('company/dashboard');
        } else {
            $_SESSION['error'] = 'Invalid admin credentials!';
            redirect('admin');
        }
    }

    // === USER LOGIN (Employer & Applicant) ===
    public function userLogin()
    {
        // Always render the user login form as the landing page.
        // Previously this redirected logged-in users to the dashboard; keep
        // the login page as the default landing so '/admin' remains the
        // dedicated admin login route.
        $this->call->view('login');
    }

    public function processUserLogin()
    {
        // Normalize input (trim + lowercase for email matching)
        $username = strtolower(trim($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Try to identify employer first
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $user = $companyModel->getCompanyByEmail($username);

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                $_SESSION['error'] = 'Please verify your email first!';
                redirect('login');
            }

            // Auto-reactivate if deactivated
            if (isset($user['status']) && $user['status'] === 'deactivated') {
                $companyModel->updateStatus($user['id'], 'approved');
            }

            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = 'employer';
            $_SESSION['email'] = $user['email'];
            $_SESSION['company_name'] = $user['company_name'] ?? '';
            $_SESSION['company_id'] = $user['id'] ?? null;
            $_SESSION['company'] = [
                'id' => $user['id'] ?? null,
                'name' => $user['company_name'] ?? ($user['name'] ?? 'Company'),
                'verified' => isset($user['is_verified']) ? (bool)$user['is_verified'] : false,
                'address' => $user['address'] ?? '',
                'description' => $user['story'] ?? ''
            ];
            $_SESSION['employer'] = [
                'id' => $user['id'] ?? null,
                'name' => ($user['company_name'] ?? ($user['name'] ?? '')) . ' User',
                'email' => $user['email'] ?? '',
                'phone' => trim((($user['country_code'] ?? '') . ' ' . ($user['phone'] ?? ''))),
                'role' => 'company_user',
                'company_id' => $user['id'] ?? null
            ];

            // Update last_login timestamp
            $companyModel->updateLastLogin($user['id']);

            redirect('company/employer');
            return;
        }

        // Fallback - try applicant
        $this->call->model('ApplicantModel');
        $appModel = new ApplicantModel();
        $app = $appModel->getApplicantByEmail($username);

        if ($app && isset($app['password']) && password_verify($password, $app['password'])) {
            if (isset($app['is_verified']) && $app['is_verified'] == 0) {
                $_SESSION['error'] = 'Please verify your email first!';
                redirect('login');
            }

            // Auto-reactivate if deactivated
            if (isset($app['status']) && $app['status'] === 'deactivated') {
                $appModel->updateStatus($app['id'], 'approved');
            }

            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = 'applicant';
            $_SESSION['applicant_email'] = $app['email'];
            $_SESSION['applicant_id'] = $app['id'] ?? null;
            $_SESSION['applicant_name'] = trim(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? ''));

            // Update last_login timestamp
            $appModel->updateLastLogin($app['id']);

            // Redirect applicant to dedicated dashboard
            redirect('applicant/dashboard');
            return;
        }

        $_SESSION['error'] = 'Invalid credentials!';
        redirect('login');
    }

    // === LOGOUTS ===
    public function logout()
    {
        session_destroy();
        redirect('login'); // normal users go back to user login
    }

    public function adminLogout()
    {
        session_destroy();
        redirect('admin'); // admins go back to admin login
    }

    // === FORGOT PASSWORD FLOW ===
    public function forgotForm()
    {
        $this->call->view('forgot');
    }

    // Send reset code by email (only if registered as applicant or employer)
    public function sendResetCode()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (empty($email)) {
            $_SESSION['error'] = 'Please enter your email address.';
            redirect('forgot');
            return;
        }

        // Check if email exists in companies or applicants
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $company = $companyModel->getCompanyByEmail($email);

        $this->call->model('ApplicantModel');
        $appModel = new ApplicantModel();
        $applicant = $appModel->getApplicantByEmail($email);

        if (!$company && !$applicant) {
            // For privacy, show generic message but do not send email
            $_SESSION['success'] = 'If the email is registered, a verification code has been sent.';
            redirect('forgot');
            return;
        }

        // Create code and store
        $code = rand(1000, 9999);
        $this->call->model('PasswordResetModel');
        $pr = new PasswordResetModel();
        $pr->createCode($email, (string)$code, 30);

        // Send email
        $this->sendPasswordResetEmail($email, $code, $company ? 'employer' : 'applicant');

        // Redirect directly to verify page with email prefilled for better UX
        $_SESSION['success'] = 'If the email is registered, a verification code has been sent.';
        redirect('forgot/verify?email=' . urlencode($email));
    }

    // Show verify code form (optionally prefill email)
    public function forgotVerifyForm()
    {
        $email = $_GET['email'] ?? '';
        $this->call->view('forgot_verify', ['email' => $email]);
    }

    // Verify the code submitted by user and allow reset
    public function verifyResetCode()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $code = trim($_POST['code'] ?? '');

        if (empty($email) || empty($code)) {
            $_SESSION['error'] = 'Email and verification code are required.';
            redirect('forgot/verify');
            return;
        }

        $this->call->model('PasswordResetModel');
        $pr = new PasswordResetModel();
        $row = $pr->verifyCode($email, $code);
        if (!$row) {
            $_SESSION['error'] = 'Invalid or expired verification code.';
            redirect('forgot/verify?email=' . urlencode($email));
            return;
        }

        // Verified - store email in session for reset step
        if (!isset($_SESSION)) session_start();
        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_code'] = $code;
        redirect('forgot/reset');
    }

    // Show reset password form
    public function forgotResetForm()
    {
        if (!isset($_SESSION)) session_start();
        $email = $_SESSION['password_reset_email'] ?? '';
        if (empty($email)) {
            $_SESSION['error'] = 'Please verify your email first.';
            redirect('forgot');
            return;
        }
        $this->call->view('forgot_reset', ['email' => $email]);
    }

    // Reset password (applicant or employer)
    public function resetPassword()
    {
        if (!isset($_SESSION)) session_start();
        $email = $_SESSION['password_reset_email'] ?? '';
        $code = $_SESSION['password_reset_code'] ?? '';

        if (empty($email) || empty($code)) {
            $_SESSION['error'] = 'Verification required. Start the forgot-password process again.';
            redirect('forgot');
            return;
        }

        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($new) || strlen($new) < 8) {
            $_SESSION['error'] = 'New password must be at least 8 characters.';
            redirect('forgot/reset');
            return;
        }
        if ($new !== $confirm) {
            $_SESSION['error'] = 'New password and confirm password do not match.';
            redirect('forgot/reset');
            return;
        }

        // Re-verify code to ensure not expired
        $this->call->model('PasswordResetModel');
        $pr = new PasswordResetModel();
        $valid = $pr->verifyCode($email, $code);
        if (!$valid) {
            $_SESSION['error'] = 'Invalid or expired verification code.';
            redirect('forgot');
            return;
        }

        // Determine whether this email is employer or applicant
        $this->call->model('CompanyModel');
        $companyModel = new CompanyModel();
        $company = $companyModel->getCompanyByEmail($email);

        $this->call->model('ApplicantModel');
        $appModel = new ApplicantModel();
        $applicant = $appModel->getApplicantByEmail($email);

        $hashed = password_hash($new, PASSWORD_BCRYPT);
        if ($company) {
            $this->call->database();
            $this->db->table('companies')->where('email', $email)->update(['password' => $hashed]);
        } elseif ($applicant) {
            $this->call->database();
            $this->db->table('applicants')->where('email', $email)->update(['password' => $hashed]);
        } else {
            $_SESSION['error'] = 'Account not found.';
            redirect('forgot');
            return;
        }

        // Consume the reset code and clear session
        $pr->consumeCode($email, $code);
        unset($_SESSION['password_reset_email'], $_SESSION['password_reset_code']);

        $_SESSION['success'] = 'Password reset successfully. You may now login.';
        redirect('login');
    }

    // Helper: send reset email
    private function sendPasswordResetEmail($email, $code, $role = 'applicant')
    {
        $body = "<p>Hello,</p>"
              . "<p>We received a request to reset your password. Use the verification code below to reset your password. This code expires in 30 minutes.</p>"
              . "<h2 style='letter-spacing:6px;'>" . htmlspecialchars($code) . "</h2>"
              . "<p>If you did not request this, you can safely ignore this email.</p>"
              . "<p>— Job Portal</p>";

        $res = phpmailer_send([
            'to' => $email,
            'subject' => 'Your password reset verification code',
            'body' => $body,
            'is_html' => true,
        ]);
        if (!$res['success']) {
            error_log('Failed to send password reset email: ' . ($res['error'] ?? 'unknown'));
        }
    }
    }