<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_household_model extends CI_Model
{
    protected $table = 'resident_household';

    const RELATIONSHIP_OPTIONS = ['Head', 'Member', 'Spouse', 'Son', 'Daughter', 'Parent', 'Sibling', 'Grandchild', 'Other Relative', 'Boarder', 'Other'];
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

    /** Residents in a barangay that have a household_no, grouped into households for the BHW Family Profiling Form. */
    public function get_household_roster_by_barangay($barangay_id)
    {
        $rows = $this->db->select("
                resident_household.household_no,
                resident_household.relationship_to_head, resident_household.ordinal_position,
                resident_household.has_hypertension, resident_household.has_diabetes, resident_household.has_asthma, resident_household.other_illness,
                resident_household.gravida, resident_household.para, resident_household.lmp_date, resident_household.edc_date, resident_household.tt_status,
                residents.last_name, residents.first_name, residents.middle_name, residents.sex, residents.birthdate,
                residents.educational_attainment, residents.occupation, residents.religion
            ")
            ->from($this->table)
            ->join('residents', 'residents.id = resident_household.resident_id')
            ->where('residents.barangay_id', $barangay_id)
            ->where('residents.archive', 0)
            ->where('resident_household.household_no IS NOT NULL')
            ->order_by('resident_household.household_no', 'ASC')
            ->order_by("FIELD(resident_household.relationship_to_head, 'Head') DESC", '', false)
            ->order_by('residents.last_name', 'ASC')
            ->get()->result();

        $households = [];
        foreach ($rows as $row) {
            if (!isset($households[$row->household_no])) {
                $households[$row->household_no] = (object) ['household_no' => $row->household_no, 'members' => []];
            }
            $households[$row->household_no]->members[] = $row;
        }

        return array_values($households);
    }

    /** Same as get_household_roster_by_barangay(), but across every barangay in a municipality; each household also carries its barangay_name. */
    public function get_household_roster_by_municipality($municipality_id)
    {
        $rows = $this->db->select("
                resident_household.household_no,
                resident_household.relationship_to_head, resident_household.ordinal_position,
                resident_household.has_hypertension, resident_household.has_diabetes, resident_household.has_asthma, resident_household.other_illness,
                resident_household.gravida, resident_household.para, resident_household.lmp_date, resident_household.edc_date, resident_household.tt_status,
                residents.last_name, residents.first_name, residents.middle_name, residents.sex, residents.birthdate,
                residents.educational_attainment, residents.occupation, residents.religion,
                residents.barangay_id, address_barangay.name AS barangay_name
            ")
            ->from($this->table)
            ->join('residents', 'residents.id = resident_household.resident_id')
            ->join('address_barangay', 'address_barangay.id = residents.barangay_id')
            ->where('address_barangay.municipality_id', $municipality_id)
            ->where('residents.archive', 0)
            ->where('resident_household.household_no IS NOT NULL')
            ->order_by('address_barangay.name', 'ASC')
            ->order_by('resident_household.household_no', 'ASC')
            ->order_by("FIELD(resident_household.relationship_to_head, 'Head') DESC", '', false)
            ->order_by('residents.last_name', 'ASC')
            ->get()->result();

        $households = [];
        foreach ($rows as $row) {
            $key = $row->barangay_id . ':' . $row->household_no;
            if (!isset($households[$key])) {
                $households[$key] = (object) ['household_no' => $row->household_no, 'barangay_name' => $row->barangay_name, 'members' => []];
            }
            $households[$key]->members[] = $row;
        }

        return array_values($households);
    }
}
