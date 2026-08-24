<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Household_model extends CI_Model
{
    protected $table = 'households';

    const RELATIONSHIP_OPTIONS = ['Head', 'Spouse', 'Son', 'Daughter', 'Parent', 'Sibling', 'Grandchild', 'Other Relative', 'Boarder', 'Other'];
    const CIVIL_STATUS_OPTIONS = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Live-in'];
    const EDUCATION_OPTIONS = ['None', 'Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'Vocational', 'College Undergraduate', 'College Graduate', 'Post Graduate'];

    const AGE_BRACKETS = [
        '0-5' => [0, 5],
        '6-12' => [6, 12],
        '13-17' => [13, 17],
        '18-59' => [18, 59],
        '60+' => [60, 200],
    ];

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_by_barangay($barangay_id)
    {
        return $this->db->where('barangay_id', $barangay_id)->where('archive', 0)->order_by('household_no', 'ASC')->get($this->table)->result();
    }

    /** Next sequential household_no for a barangay, formatted HH-{barangay_code}-{0001}. */
    public function generate_household_no($barangay_id)
    {
        $barangay = $this->db->where('id', $barangay_id)->get('address_barangay')->row();
        $prefix = ($barangay && $barangay->code !== null && $barangay->code !== '') ? $barangay->code : ('B' . $barangay_id);

        $last = $this->db->select('household_no')
            ->where('barangay_id', $barangay_id)
            ->like('household_no', 'HH-' . $prefix . '-', 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)->row();

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last->household_no);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('HH-%s-%04d', $prefix, $sequence);
    }

    public function create(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function archive($id)
    {
        return $this->db->where('id', $id)->update($this->table, ['archive' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /** Household count, population, and demographic breakdowns for one barangay. */
    public function get_barangay_summary($barangay_id)
    {
        return $this->summarize('households.barangay_id = ' . (int) $barangay_id);
    }

    /** Same breakdowns rolled up across every barangay in a municipality. */
    public function get_town_summary($municipality_id)
    {
        return $this->summarize('households.barangay_id IN (SELECT id FROM address_barangay WHERE municipality_id = ' . (int) $municipality_id . ')');
    }

    private function summarize($household_where)
    {
        $household_count = (int) $this->db
            ->where('archive', 0)
            ->where($household_where)
            ->count_all_results($this->table);

        $member_totals = $this->db
            ->select('COUNT(*) AS population, SUM(is_pwd) AS pwd_count, SUM(is_senior_citizen) AS senior_count, SUM(is_solo_parent) AS solo_parent_count, SUM(is_4ps_beneficiary) AS fourps_count')
            ->from('household_members')
            ->join('households', 'households.id = household_members.household_id')
            ->where('households.archive', 0)
            ->where($household_where)
            ->get()->row();

        $sex_breakdown = ['Male' => 0, 'Female' => 0];
        $sex_rows = $this->db
            ->select('household_members.sex, COUNT(*) AS total')
            ->from('household_members')
            ->join('households', 'households.id = household_members.household_id')
            ->where('households.archive', 0)
            ->where($household_where)
            ->group_by('household_members.sex')
            ->get()->result();
        foreach ($sex_rows as $row) {
            $sex_breakdown[$row->sex] = (int) $row->total;
        }

        $age_brackets = [];
        foreach (self::AGE_BRACKETS as $label => [$min, $max]) {
            $age_brackets[$label] = (int) $this->db
                ->from('household_members')
                ->join('households', 'households.id = household_members.household_id')
                ->where('households.archive', 0)
                ->where($household_where)
                ->where('TIMESTAMPDIFF(YEAR, household_members.birthdate, CURDATE()) >=', $min)
                ->where('TIMESTAMPDIFF(YEAR, household_members.birthdate, CURDATE()) <=', $max)
                ->count_all_results();
        }

        return (object) [
            'household_count' => $household_count,
            'population' => (int) ($member_totals->population ?? 0),
            'sex_breakdown' => $sex_breakdown,
            'age_brackets' => $age_brackets,
            'pwd_count' => (int) ($member_totals->pwd_count ?? 0),
            'senior_count' => (int) ($member_totals->senior_count ?? 0),
            'solo_parent_count' => (int) ($member_totals->solo_parent_count ?? 0),
            'fourps_count' => (int) ($member_totals->fourps_count ?? 0),
        ];
    }
}
