<?php
class AdminController extends Controller
{
    private function ensureAdmin()
    {
        if (!isset($_SESSION)) session_start();
        if (empty($_SESSION['logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
            redirect('admin');
            exit;
        }
    }

    public function dashboard()
    {
        $this->ensureAdmin();
        $this->call->model('CompanyModel');
        $this->call->model('UserModel');
        $this->call->model('ApplicantModel');
        $this->call->model('ReviewModel');

        $total_companies = $this->CompanyModel->countAll();
        $total_users = $this->UserModel->countAll();
        $total_applications = $this->ApplicantModel->countAll();

        $companies_approved = $this->CompanyModel->countByStatus('approved');
        $companies_pending = $this->CompanyModel->countByStatus('pending');

        $users_approved = $this->UserModel->countByStatus('approved');
        $users_pending = $this->UserModel->countByStatus('pending');

        $applications = $this->ApplicantModel->getRecentApplications();

        // Fetch full lists for dashboard tables
        $applicants = $this->ApplicantModel->getAllApplicants();
        $employers = $this->CompanyModel->getAllCompanies();

        // Pending applicants: status='pending' (regardless of email verification)
        // They appear here until admin approves them; verification just unlocks login.
        $pendingApplicants = array_filter($applicants, function($r){
            return isset($r['status']) && $r['status'] === 'pending';
        });
        // Pending employers: either explicit status 'pending' OR not verified
        $pendingEmployers = array_filter($employers, function($r){
            $isPendingStatus = isset($r['status']) && $r['status'] === 'pending';
            $notVerified = isset($r['is_verified']) && (int)$r['is_verified'] === 0;
            return $isPendingStatus || $notVerified;
        });

        // Approved lists for Home tab
        $approvedApplicants = array_filter($applicants, function($r){
            return isset($r['status']) && $r['status'] === 'approved';
        });
        // Only companies with explicit status = 'approved' show in Home.
        // This ensures new employers must be in Pending (status='pending')
        // and then explicitly approved to appear in Home.
        $approvedEmployers = array_filter($employers, function($r){
            return isset($r['status']) && $r['status'] === 'approved';
        });

        // Deactivated accounts
        $deactivatedApplicants = array_filter($applicants, function($r){
            return isset($r['status']) && $r['status'] === 'deactivated';
        });
        $deactivatedEmployers = array_filter($employers, function($r){
            return isset($r['status']) && $r['status'] === 'deactivated';
        });

        // Get review data
        $allReviews = $this->ReviewModel->getAllReviews();
        $stats = $this->ReviewModel->getAverageRating();
        $distribution = $this->ReviewModel->getRatingDistribution();

        $this->call->view('company/dashboard', [
            'total_companies' => $total_companies,
            'total_users' => $total_users,
            'total_applications' => $total_applications,
            'companies_approved' => $companies_approved,
            'companies_pending' => $companies_pending,
            'users_approved' => $users_approved,
            'users_pending' => $users_pending,
            'applications' => $applications,
            'applicants' => array_values($approvedApplicants),
            'employers' => array_values($approvedEmployers),
            'pendingApplicants' => array_values($pendingApplicants),
            'pendingEmployers' => array_values($pendingEmployers),
            'deactivatedApplicants' => array_values($deactivatedApplicants),
            'deactivatedEmployers' => array_values($deactivatedEmployers),
            'allReviews' => $allReviews,
            'reviewStats' => $stats,
            'reviewDistribution' => $distribution
        ]);
    }
    public function getAllApplicants()
{
    $this->call->database();

    $sql = "SELECT id, first_name, middle_name, last_name, email, contact, gender, city, municipality, created_at
            FROM applicants
            ORDER BY created_at DESC";

    $query = $this->db->raw($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

    // Approve a pending applicant or employer
    public function approve()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid approve request.';
            redirect('company/dashboard');
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->updateStatus($id, 'approved');
            $_SESSION['success'] = 'Applicant approved.';
        } else {
            $this->CompanyModel->approveCompany($id);
            $_SESSION['success'] = 'Employer approved.';
        }

        redirect('company/dashboard');
    }

    // Delete a pending applicant or employer from database
    public function deletePending()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid delete request.';
            redirect('company/dashboard');
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->deleteApplicant($id);
            $_SESSION['success'] = 'Applicant deleted.';
        } else {
            $this->CompanyModel->deleteCompany($id);
            $_SESSION['success'] = 'Employer deleted.';
        }

        redirect('company/dashboard');
    }

    // Show edit page for applicant or employer with deactivate/activate actions
    public function edit()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_GET['type'] ?? '';
        $id = $_GET['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid edit request.';
            redirect('company/dashboard');
        }

        $record = ($type === 'applicant')
            ? $this->ApplicantModel->getById($id)
            : $this->CompanyModel->getById($id);

        if (!$record) {
            $_SESSION['error'] = 'Record not found.';
            redirect('company/dashboard');
        }

        $this->call->view('company/admin_edit', [
            'type' => $type,
            'record' => $record
        ]);
    }

    public function deactivate()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid deactivate request.';
            redirect('admin/edit?type=' . urlencode($type) . '&id=' . urlencode((string)$id));
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->updateStatus($id, 'deactivated');
        } else {
            $this->CompanyModel->updateStatus($id, 'deactivated');
        }

        $_SESSION['success'] = 'Account deactivated.';
        redirect('admin/edit?type=' . urlencode($type) . '&id=' . urlencode((string)$id));
    }

    public function activate()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid activate request.';
            redirect('admin/edit?type=' . urlencode($type) . '&id=' . urlencode((string)$id));
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->updateStatus($id, 'approved');
        } else {
            $this->CompanyModel->updateStatus($id, 'approved');
        }

        $_SESSION['success'] = 'Account activated.';
        redirect('admin/edit?type=' . urlencode($type) . '&id=' . urlencode((string)$id));
    }

    // Reactivate a deactivated account (from deactivated tab)
    public function reactivate()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid reactivate request.';
            redirect('company/dashboard?tab=deactivated');
            return;
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->updateStatus($id, 'approved');
            $_SESSION['success'] = 'Applicant reactivated and approved.';
        } else {
            $this->CompanyModel->updateStatus($id, 'approved');
            $_SESSION['success'] = 'Employer reactivated and approved.';
        }

        redirect('company/dashboard?tab=deactivated');
    }

    // Permanently delete a deactivated account
    public function deletePermanent()
    {
        $this->ensureAdmin();
        $this->call->model('ApplicantModel');
        $this->call->model('CompanyModel');

        $type = $_POST['type'] ?? '';
        $id = $_POST['id'] ?? null;

        if (!$id || !in_array($type, ['applicant','employer'])) {
            $_SESSION['error'] = 'Invalid delete request.';
            redirect('company/dashboard?tab=deactivated');
            return;
        }

        if ($type === 'applicant') {
            $this->ApplicantModel->deleteApplicant($id);
            $_SESSION['success'] = 'Applicant permanently deleted.';
        } else {
            $this->CompanyModel->deleteCompany($id);
            $_SESSION['success'] = 'Employer permanently deleted.';
        }

        redirect('company/dashboard?tab=deactivated');
    }

}
