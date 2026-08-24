<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_household_model extends CI_Model
{
    protected $table = 'resident_household';

    const RELATIONSHIP_OPTIONS = ['Head', 'Spouse', 'Son', 'Daughter', 'Parent', 'Sibling', 'Grandchild', 'Other Relative', 'Boarder', 'Other'];
    const TT_STATUS_OPTIONS = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5', 'Fully Immunized'];
    const NUTRITIONAL_STATUS_WEIGHT_AGE_OPTIONS = ['Severely Underweight', 'Underweight', 'Normal', 'Overweight'];
    const NUTRITIONAL_STATUS_HEIGHT_AGE_OPTIONS = ['Severely Stunted', 'Stunted', 'Normal', 'Tall'];
    const NUTRITIONAL_STATUS_WEIGHT_HEIGHT_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];
    const SCHOOL_LEVEL_OPTIONS = ['Day Care', 'Kindergarten', 'Elementary'];
    const SCHOOL_TYPE_OPTIONS = ['Public', 'Private'];
    const SCHOOL_NUTRITIONAL_STATUS_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];

    public function get_by_resident($resident_id)
    {
        return $this->db->where('resident_id', $resident_id)->get($this->table)->row();
    }

    /** Upserts the one household-profile row for a resident. */
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
