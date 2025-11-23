<?php

class JobModel extends Model
{
    protected $table = 'company_jobs';

    public function listByCompany($companyId)
    {
        $this->call->database();
        $stmt = $this->db->raw("SELECT id, company_id, position, description, requirements, created_at FROM {$this->table} WHERE company_id = ? ORDER BY created_at DESC", [(int)$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertJob($companyId, $position, $requirements = '', $description = '')
    {
        $this->call->database();
        $this->db->table($this->table)->insert([
            'company_id' => (int)$companyId,
            'position' => $position,
            'requirements' => $requirements,
            'description' => $description
        ]);
        return true;
    }

    public function updateJob($id, $position, $requirements, $description)
    {
        $this->call->database();
        $this->db->table($this->table)
            ->where('id', (int)$id)
            ->update([
                'position' => $position,
                'requirements' => $requirements,
                'description' => $description
            ]);
        return true;
    }

    public function deleteJob($id)
    {
        $this->call->database();
        $this->db->table($this->table)->where('id', (int)$id)->delete();
        return true;
    }
}
