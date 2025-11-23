<?php

class ApplicantModel extends Model
{
    protected $table = 'applicants';

    // ✅ Insert applicant record with verification
    public function insertApplicant($data)
    {
        $this->call->database();

        $insertData = [
            'first_name'   => $data['first_name'],
            'middle_name'  => $data['middle_name'],
            'last_name'    => $data['last_name'],
            'birthdate'    => $data['birthdate'] ?? null,
            'gender'       => $data['gender'] ?? '',
            'contact'      => $data['contact'] ?? '',
            'email'        => $data['email'] ?? '',
            'birth_place'  => $data['birth_place'] ?? '',
            'barangay'     => $data['barangay'] ?? '',
            'city'         => $data['city'] ?? '',
            'municipality' => $data['municipality'] ?? '',
            'address'      => $data['address'] ?? '',
            'resume'       => $data['resume'] ?? '',
            'job_title'    => $data['job_title'] ?? '',
            'password'     => $data['password'],
            'status'       => $data['status'] ?? 'pending',
            'verification_code' => $data['verification_code'], // new field
            'is_verified'  => $data['is_verified'],            // new field
            'created_at'   => date('Y-m-d H:i:s')
        ];

        $this->db->table($this->table)->insert($insertData);

        return true;
    }

    // ✅ Verify applicant email by code
    public function verifyCode($email, $code)
    {
        $this->call->database();
        // Use a raw SELECT to reliably fetch a single row (db wrapper can return different shapes)
        $sql = "SELECT id FROM {$this->table} WHERE email = ? AND verification_code = ? LIMIT 1";
        try {
            $stmt = $this->db->raw($sql, [$email, $code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                // Mark as verified and clear the verification_code for safety
                // Status stays 'pending' until admin approves
                $this->db->table($this->table)
                    ->where('email', $email)
                    ->update(['is_verified' => 1, 'verification_code' => null]);
                return true;
            }
        } catch (Exception $e) {
            // log and return false
            error_log('ApplicantModel::verifyCode error: ' . $e->getMessage());
        }

        return false;
    }

    // ✅ Count all applicants
    public function countAll()
    {
        $this->call->database();
        $result = $this->db->table($this->table)
                           ->select('COUNT(*) AS total')
                           ->get();
        return $result[0]['total'] ?? 0;
    }

    // ✅ Fetch recent applications
    public function getRecentApplications($limit = 5)
    {
        $this->call->database();
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT {$limit}";
        $query = $this->db->raw($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Fetch all applicants
    public function getAllApplicants()
    {
        $this->call->database();
        // Include last_login for admin dashboard
        $sql = "SELECT id, first_name, middle_name, last_name, email, contact, gender, city, municipality, resume, job_title, status, schedule_date, last_login, created_at
                FROM {$this->table}
                ORDER BY created_at DESC";
        $query = $this->db->raw($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update status for an applicant
    public function updateStatus($id, $status)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->update(['status' => $status]);
        return true;
    }

    // Delete an applicant by id
    public function deleteApplicant($id)
    {
        $this->call->database();
        $this->db->table($this->table)
                 ->where('id', (int)$id)
                 ->delete();
        return true;
    }

    // Fetch a single applicant row by email for authentication
    public function getApplicantByEmail($email)
    {
        $this->call->database();
        // Use a raw SELECT to ensure a single associative row is returned
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        try {
            $stmt = $this->db->raw($sql, [$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Fetch a single applicant by id
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
