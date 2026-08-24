<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report_template_model extends CI_Model
{
    protected $table = 'report_templates';

    public function get_by_user($user_id)
    {
        return $this->db->where('user_id', $user_id)->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function create($user_id, $name, array $fields, array $filters)
    {
        $this->db->insert($this->table, [
            'user_id' => $user_id,
            'name' => $name,
            'fields' => json_encode(array_values($fields)),
            'filters' => json_encode($filters),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    public function delete($id, $user_id)
    {
        return $this->db->where('id', $id)->where('user_id', $user_id)->delete($this->table);
    }
}
