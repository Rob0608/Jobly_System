<?php

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';

    public function __construct()
    {
        parent::__construct();
    }

    // Create or replace a reset code for an email
    public function createCode($email, $code, $ttlMinutes = 30)
    {
        $this->call->database();
        $expires = date('Y-m-d H:i:s', time() + ($ttlMinutes * 60));

        // Remove existing codes for this email
        try {
            $this->db->table($this->table)->where('email', $email)->delete();
            $this->db->table($this->table)->insert([
                'email' => $email,
                'code' => $code,
                'expires_at' => $expires,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            return true;
        } catch (Exception $e) {
            error_log('PasswordResetModel::createCode error: ' . $e->getMessage());
            return false;
        }
    }

    // Verify a reset code for an email (not consuming it)
    public function verifyCode($email, $code)
    {
        $this->call->database();
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = ? AND code = ? AND expires_at >= ? LIMIT 1";
            $stmt = $this->db->raw($sql, [$email, $code, date('Y-m-d H:i:s')]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return !empty($row) ? $row : false;
        } catch (Exception $e) {
            error_log('PasswordResetModel::verifyCode error: ' . $e->getMessage());
            return false;
        }
    }

    // Consume (delete) a code after successful reset
    public function consumeCode($email, $code)
    {
        $this->call->database();
        try {
            $this->db->table($this->table)->where('email', $email)->where('code', $code)->delete();
            return true;
        } catch (Exception $e) {
            error_log('PasswordResetModel::consumeCode error: ' . $e->getMessage());
            return false;
        }
    }

    // Clean up expired records (optional utility)
    public function cleanupExpired()
    {
        $this->call->database();
        try {
            $this->db->table($this->table)->where('expires_at <', date('Y-m-d H:i:s'))->delete();
            return true;
        } catch (Exception $e) {
            error_log('PasswordResetModel::cleanupExpired error: ' . $e->getMessage());
            return false;
        }
    }
}
