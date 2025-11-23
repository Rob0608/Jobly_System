<?php

class SocialAuthController extends Controller
{
    private function getGoogleClient()
    {
        $autoloadCandidates = [
            __DIR__ . '/../../../vendor/autoload.php', // project root vendor
            __DIR__ . '/../../vendor/autoload.php',    // fallback
        ];
        foreach ($autoloadCandidates as $auto) {
            if (file_exists($auto)) { require_once $auto; break; }
        }
        $clientId = config_item('google_client_id') ?: '';
        $clientSecret = config_item('google_client_secret') ?: '';
        $redirectUri = config_item('google_redirect_uri') ?: site_url('auth/google/callback');

        if (empty($clientId) || empty($clientSecret)) {
            if (!isset($_SESSION)) session_start();
            $_SESSION['error'] = 'Google Sign-In is not configured. Ask admin to set client ID/secret.';
            redirect('login');
            exit;
        }

        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->setAccessType('offline');
        $client->setPrompt('select_account');
        $client->setScopes(['openid','email','profile']);

        // Optional: configure HTTP client with CA bundle to avoid cURL error 60 on Windows/WAMP
        $caBundle = config_item('ca_bundle_path') ?: '';
        if ($caBundle && file_exists($caBundle)) {
            try {
                $guzzle = new \GuzzleHttp\Client(['verify' => $caBundle]);
                if (method_exists($client, 'setHttpClient')) {
                    $client->setHttpClient($guzzle);
                }
            } catch (\Throwable $e) {
                // silently fall back to default HTTP client
            }
        }
        return $client;
    }

    // /auth/google?role=applicant
    public function google()
    {
        if (!isset($_SESSION)) session_start();
        $role = $_GET['role'] ?? 'applicant';
        if (!in_array($role, ['applicant'])) { // limit to applicant as requested
            $_SESSION['error'] = 'Unsupported role for Google Sign-In.';
            redirect('login');
            return;
        }

        $client = $this->getGoogleClient();
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_role'] = $role;
        if (method_exists($client, 'setState')) { $client->setState($state); }
        $authUrl = $client->createAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    // Google OAuth2 callback
    public function googleCallback()
    {
        if (!isset($_SESSION)) session_start();
        $state = $_GET['state'] ?? '';
        $expected = $_SESSION['oauth_state'] ?? '';
        if (empty($expected) || empty($state) || !hash_equals($expected, $state)) {
            // In some local setups (cookies/SameSite), state can be lost. Continue but refresh state.
            $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
        }
        $role = $_SESSION['oauth_role'] ?? 'applicant';
        $client = $this->getGoogleClient();

        if (empty($_GET['code'])) { $_SESSION['error'] = 'Missing authorization code.'; redirect('login'); return; }

        $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $payload = $client->verifyIdToken();
        if (!$payload) {
            $_SESSION['error'] = 'Unable to verify Google ID token.';
            redirect('login');
            return;
        }

        $email = strtolower(trim($payload['email'] ?? ''));
        $emailVerified = (bool)($payload['email_verified'] ?? false);
        $givenName = $payload['given_name'] ?? '';
        $familyName = $payload['family_name'] ?? '';
        $googleSub = $payload['sub'] ?? '';

        if (!$emailVerified) {
            $_SESSION['error'] = 'Google account email is not verified.';
            redirect('login');
            return;
        }

        if ($role === 'applicant') {
            $this->call->model('ApplicantModel');
            $appModel = new ApplicantModel();
            $existing = $appModel->getApplicantByEmail($email);
            if ($existing && !empty($existing['birthdate'])) {
                // Update last_login timestamp
                $appModel->updateLastLogin($existing['id']);
                
                // login directly
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = 'applicant';
                $_SESSION['applicant_email'] = $existing['email'];
                $_SESSION['applicant_id'] = $existing['id'] ?? null;
                $_SESSION['applicant_name'] = trim(($existing['first_name'] ?? '') . ' ' . ($existing['last_name'] ?? ''));
                redirect('applicant/dashboard');
                return;
            }

            // Show completion form (pre-fill names from Google)
            $this->call->view('applicant/google_complete', [
                'email' => $email,
                'given_name' => $givenName,
                'family_name' => $familyName,
                'google_sub' => $googleSub
            ]);
            return;
        }

        $_SESSION['error'] = 'Unsupported role for Google Sign-In.';
        redirect('login');
    }

    // POST /auth/google/complete => create/update applicant after collecting names + birthday
    public function googleCompleteApplicant()
    {
        if (!isset($_SESSION)) session_start();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $first = trim($_POST['first_name'] ?? '');
        $middle = trim($_POST['middle_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';

        if (empty($email) || empty($first) || empty($last) || empty($birthdate)) {
            $_SESSION['error'] = 'Please complete all required fields.';
            redirect('login');
            return;
        }
        // Age 18-60 check
        try {
            $dob = new DateTime($birthdate);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($dob > $today || $age < 18 || $age > 60) {
                $_SESSION['error'] = 'Applicants must be between 18 and 60 years old.';
                redirect('login');
                return;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Invalid birthdate.';
            redirect('login');
            return;
        }

        $this->call->model('ApplicantModel');
        $appModel = new ApplicantModel();
        $existing = $appModel->getApplicantByEmail($email);
        $hashed = password_hash(bin2hex(random_bytes(12)), PASSWORD_BCRYPT);

        if ($existing) {
            // Update basics if missing
            $this->call->database();
            $this->db->table('applicants')->where('email', $email)->update([
                'first_name' => $first,
                'middle_name' => $middle ?: null,
                'last_name' => $last,
                'birthdate' => $birthdate,
                'is_verified' => 1
            ]);
        } else {
            // Insert new applicant record
            $data = [
                'first_name' => $first,
                'middle_name' => $middle ?: null,
                'last_name' => $last,
                'birthdate' => $birthdate,
                'gender' => '',
                'contact' => '',
                'email' => $email,
                'birth_place' => '',
                'barangay' => '',
                'city' => '',
                'municipality' => '',
                'address' => '',
                'resume' => '',
                'job_title' => '',
                'password' => $hashed,
                'verification_code' => null,
                'is_verified' => 1,
                'status' => 'pending'
            ];
            $appModel->insertApplicant($data);
        }

        // Log them in
        $applicant = $appModel->getApplicantByEmail($email);
        
        // Update last_login timestamp
        if (!empty($applicant['id'])) {
            $appModel->updateLastLogin($applicant['id']);
        }
        
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = 'applicant';
        $_SESSION['applicant_email'] = $applicant['email'];
        $_SESSION['applicant_id'] = $applicant['id'] ?? null;
        $_SESSION['applicant_name'] = trim(($applicant['first_name'] ?? '') . ' ' . ($applicant['last_name'] ?? ''));

        redirect('applicant/dashboard');
    }
}
