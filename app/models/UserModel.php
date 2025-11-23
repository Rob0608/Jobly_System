<?php
class UserModel extends Model
{
    protected $table = 'companies';

     public function countAll()
{
    // Simple count query for total rows in companies table
    $result = $this->db->table($this->table)->get_all();
    return $result ? count($result) : 0;
}

    public function countByStatus($status)
{
    // Count filtered by status
    $result = $this->db->table($this->table)
                       ->where('status', $status)
                       ->get_all();
    return $result ? count($result) : 0;
}
}
