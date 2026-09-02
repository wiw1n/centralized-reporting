<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_remarks_model extends CI_Model
{
    protected $table = 'resident_remarks';

    public function get_by_resident($resident_id)
    {
        return $this->db->where('resident_id', $resident_id)->get($this->table)->row();
    }

    /** Upserts the one Remarks row for a resident. */
    public function save($resident_id, array $data)
    {
        $data['resident_id'] = $resident_id;
        $existing = $this->get_by_resident($resident_id);

        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->where('resident_id', $resident_id)->update($this->table, $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
}
