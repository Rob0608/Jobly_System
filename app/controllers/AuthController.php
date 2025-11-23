<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

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
    }