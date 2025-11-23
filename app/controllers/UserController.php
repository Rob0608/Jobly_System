<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserController extends Controller
{
    // Simple applicant dashboard
    public function dashboard()
    {
        // Redirect applicant dashboard to the unified company dashboard which
        // already renders an applicants view for users with role 'applicant'.
        if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'applicant') {
            $_SESSION['error'] = 'Please log in as an applicant to view this page.';
            redirect('login');
            return;
        }

        redirect('company/dashboard');
    }
}
