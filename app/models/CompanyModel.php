<?php

class CompanyModel extends Model
{
    protected $table = 'companies';

    public function __construct()
    {
        parent::__construct();
    }

    // ✅ Insert company record
    public function insertCompany($data)
    {
        $this->call->database();

        // Many fields are optional at registration; default to nulls to avoid notices
        $insertData = [
            'company_name'       => $data['company_name'] ?? '',
            'address'            => $data['address'] ?? '',
            'country_code'       => $data['country_code'] ?? '+63',
            'phone'              => $data['phone'] ?? '',
            'email'              => $data['email'] ?? '',
            'password'           => $data['password'] ?? null,
            'website'            => $data['website'] ?? null,
            'story'              => $data['story'] ?? null,
            'mission'            => $data['mission'] ?? null,
            'vision'             => $data['vision'] ?? null,
            'products_services'  => $data['products_services'] ?? null,
            'job_position'       => $data['job_position'] ?? null,
            'avatar'             => $data['avatar'] ?? null,
            'business_permit'    => $data['business_permit'] ?? null,
            'verification_code'  => $data['verification_code'] ?? null,
            'is_verified'        => $data['is_verified'] ?? 0,
            'status'             => $data['status'] ?? 'pending',
            'latitude'           => !empty($data['latitude']) ? $data['latitude'] : null,
            'longitude'          => !empty($data['longitude']) ? $data['longitude'] : null,
        ];

        $this->db->table($this->table)->insert($insertData);
    }

    // ✅ Check if company name OR website already exists
    public function existsByNameOrWebsite($companyName, $website)
    {
        $this->call->database();

        $query = $this->db->table($this->table)
                          ->where('company_name', $companyName)
                          ->or_where('website', $website)
                          ->get();

        return !empty($query);
    }

    // ✅ Verify company using verification code
    public function verifyCode($email, $code)
    {
        $this->call->database();

        $result = $this->db->table($this->table)
                           ->where('email', $email)
                           ->where('verification_code', $code)
                           ->get();

        if (!empty($result)) {
            $this->db->table($this->table)
                     ->where('email', $email)
                     ->update(['is_verified' => 1]);
            return true;
        }

        return false;
    }

    // Mark company as approved/verified
    public function approveCompany($id)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->update(['is_verified' => 1, 'status' => 'approved']);
        return true;
    }

    public function deleteCompany($id)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->delete();
        return true;
    }

    public function getCompanyByEmail($email)
{
    $this->call->database();
    // Use a raw SELECT to guarantee we get a single associative row back
    // This avoids variability from different DB helper return shapes.
    $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
    try {
        $stmt = $this->db->raw($sql, [$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        // If something goes wrong, return null so caller can handle auth failure
        return null;
    }
}


    public function countAll()
    {
        $this->call->database();
        $result = $this->db->table($this->table)->get();
        return $result ? count($result) : 0;
    }

    // ✅ Fetch all companies
    public function getAllCompanies()
    {
        $this->call->database();
        // Include business_permit and last_login for admin dashboard
        $sql = "SELECT id, company_name, email, is_verified, status, website, mission, vision, story, avatar, job_position, business_permit, last_login, created_at
                FROM {$this->table}
                ORDER BY created_at DESC";
        try {
            $stmt = $this->db->raw($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('CompanyModel::getAllCompanies error: ' . $e->getMessage());
            return [];
        }
    }

    public function countByStatus($status)
    {
        $this->call->database();

        $result = $this->db->table($this->table)
                           ->where('status', $status)
                           ->get();

        return $result ? count($result) : 0;
    }

    // Fetch a single company by id
    public function getById($id)
    {
        $this->call->database();
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        try {
            $stmt = $this->db->raw($sql, [(int)$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Generic status update for company (employer)
    public function updateStatus($id, $status)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->update(['status' => $status]);
        return true;
    }

    // Update company profile fields (excluding sensitive auth fields)
    public function updateProfile($id, array $data)
    {
        $this->call->database();
        $update = [];
        foreach (['company_name','website','story','mission','vision','avatar'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (!empty($update)) {
            $this->db->table($this->table)->where('id', (int)$id)->update($update);
        }
        return true;
    }

    // Update last_login timestamp
    public function updateLastLogin($id)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->update(['last_login' => date('Y-m-d H:i:s')]);
        return true;
    }
}
