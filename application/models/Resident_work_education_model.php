<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_work_education_model extends CI_Model
{
    protected $table = 'resident_work_education';

    const EDUCATION_OPTIONS = ['None', 'Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'Vocational', 'College Undergraduate', 'College Graduate', 'Post Graduate', 'Out of School Youth', 'Other'];

    public function get_by_resident($resident_id)
    {
        return $this->db->where('resident_id', $resident_id)->get($this->table)->row();
    }

    /** Upserts the one Occupation & Education row for a resident. */
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
