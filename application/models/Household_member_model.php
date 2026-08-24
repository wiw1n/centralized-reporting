<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Household_member_model extends CI_Model
{
    protected $table = 'household_members';

    const NUTRITIONAL_STATUS_WEIGHT_AGE_OPTIONS = ['Severely Underweight', 'Underweight', 'Normal', 'Overweight'];
    const NUTRITIONAL_STATUS_HEIGHT_AGE_OPTIONS = ['Severely Stunted', 'Stunted', 'Normal', 'Tall'];
    const NUTRITIONAL_STATUS_WEIGHT_HEIGHT_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];
    const SCHOOL_LEVEL_OPTIONS = ['Day Care', 'Kindergarten', 'Elementary'];
    const SCHOOL_TYPE_OPTIONS = ['Public', 'Private'];
    const SCHOOL_NUTRITIONAL_STATUS_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];
    const TT_STATUS_OPTIONS = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5', 'Fully Immunized'];

    public function get_by_household($household_id)
    {
        return $this->db->select('household_members.*, residents.resident_no AS linked_resident_no')
            ->from($this->table)
            ->join('residents', 'residents.id = household_members.resident_id', 'left')
            ->where('household_members.household_id', $household_id)
            ->order_by("FIELD(relationship_to_head, 'Head') DESC", '', false)
            ->order_by('household_members.last_name', 'ASC')
            ->get()->result();
    }

    public function count_by_household($household_id)
    {
        return (int) $this->db->where('household_id', $household_id)->count_all_results($this->table);
    }

    /** Hard-deletes and reinserts every member row for a household in one transaction. */
    public function replace_members_for_household($household_id, array $members)
    {
        $this->db->trans_start();

        $this->db->where('household_id', $household_id)->delete($this->table);

        if (!empty($members)) {
            $now = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($members as $member) {
                $rows[] = [
                    'household_id' => $household_id,
                    'resident_id' => !empty($member['resident_id']) ? (int) $member['resident_id'] : null,
                    'last_name' => $member['last_name'] ?? '',
                    'first_name' => $member['first_name'] ?? '',
                    'middle_name' => !empty($member['middle_name']) ? $member['middle_name'] : null,
                    'suffix' => !empty($member['suffix']) ? $member['suffix'] : null,
                    'relationship_to_head' => $member['relationship_to_head'] ?? '',
                    'ordinal_position' => !empty($member['ordinal_position']) ? (int) $member['ordinal_position'] : null,
                    'sex' => $member['sex'] ?? '',
                    'birthdate' => $member['birthdate'] ?? null,
                    'civil_status' => $member['civil_status'] ?? '',
                    'religion' => !empty($member['religion']) ? $member['religion'] : null,
                    'occupation' => !empty($member['occupation']) ? $member['occupation'] : null,
                    'educational_attainment' => !empty($member['educational_attainment']) ? $member['educational_attainment'] : null,
                    'contact_number' => !empty($member['contact_number']) ? $member['contact_number'] : null,
                    'is_pwd' => !empty($member['is_pwd']) ? 1 : 0,
                    'is_senior_citizen' => !empty($member['birthdate']) && $this->is_senior($member['birthdate']) ? 1 : 0,
                    'is_solo_parent' => !empty($member['is_solo_parent']) ? 1 : 0,
                    'is_4ps_beneficiary' => !empty($member['is_4ps_beneficiary']) ? 1 : 0,
                    'is_pregnant' => !empty($member['is_pregnant']) ? 1 : 0,
                    'is_lactating' => !empty($member['is_lactating']) ? 1 : 0,
                    'has_hypertension' => !empty($member['has_hypertension']) ? 1 : 0,
                    'has_diabetes' => !empty($member['has_diabetes']) ? 1 : 0,
                    'has_asthma' => !empty($member['has_asthma']) ? 1 : 0,
                    'other_illness' => !empty($member['other_illness']) ? $member['other_illness'] : null,
                    'gravida' => !empty($member['gravida']) ? (int) $member['gravida'] : null,
                    'para' => (isset($member['para']) && $member['para'] !== '') ? (int) $member['para'] : null,
                    'lmp_date' => !empty($member['lmp_date']) ? $member['lmp_date'] : null,
                    'edc_date' => !empty($member['edc_date']) ? $member['edc_date'] : null,
                    'tt_status' => (!empty($member['tt_status']) && in_array($member['tt_status'], self::TT_STATUS_OPTIONS, true)) ? $member['tt_status'] : null,
                    'opt_plus_measured' => !empty($member['opt_plus_measured']) ? 1 : 0,
                    'nutritional_status_weight_age' => !empty($member['nutritional_status_weight_age']) ? $member['nutritional_status_weight_age'] : null,
                    'nutritional_status_height_age' => !empty($member['nutritional_status_height_age']) ? $member['nutritional_status_height_age'] : null,
                    'nutritional_status_weight_height' => !empty($member['nutritional_status_weight_height']) ? $member['nutritional_status_weight_height'] : null,
                    'school_level' => !empty($member['school_level']) ? $member['school_level'] : null,
                    'school_type' => !empty($member['school_type']) ? $member['school_type'] : null,
                    'school_weighed' => !empty($member['school_weighed']) ? 1 : 0,
                    'school_nutritional_status' => !empty($member['school_nutritional_status']) ? $member['school_nutritional_status'] : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $this->db->insert_batch($this->table, $rows);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    private function is_senior($birthdate)
    {
        return (new DateTime($birthdate))->diff(new DateTime())->y >= 60;
    }
}
