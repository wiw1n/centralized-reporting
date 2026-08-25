<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_model extends CI_Model
{
    protected $table = 'residents';

    const CIVIL_STATUS_OPTIONS = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Live-in'];
    const EDUCATION_OPTIONS = ['None', 'Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'Vocational', 'College Undergraduate', 'College Graduate', 'Post Graduate'];
    const BLOOD_TYPE_OPTIONS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'];

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_by_barangay($barangay_id)
    {
        return $this->db->where('barangay_id', $barangay_id)->where('archive', 0)->order_by('last_name', 'ASC')->get($this->table)->result();
    }

    /**
     * Finds already-profiled residents matching a typed name, so encoders can
     * link an existing record instead of creating a duplicate individual.
     * Scoped to one barangay when given, else to a whole municipality, else unrestricted.
     */
    public function search($term, $barangay_id = null, $municipality_id = null, $limit = 10)
    {
        $this->db->select('id, resident_no, last_name, first_name, middle_name, suffix, sex, birthdate, civil_status, religion, occupation, educational_attainment, contact_number')
            ->from($this->table)
            ->where('archive', 0)
            ->group_start()
                ->like('last_name', $term)
                ->or_like('first_name', $term)
                ->or_like("CONCAT(first_name, ' ', last_name)", $term)
                ->or_like("CONCAT(last_name, ' ', first_name)", $term)
            ->group_end();

        if ($barangay_id) {
            $this->db->where('barangay_id', $barangay_id);
        } elseif ($municipality_id) {
            $this->db->where('barangay_id IN (SELECT id FROM address_barangay WHERE municipality_id = ' . (int) $municipality_id . ')');
        }

        return $this->db->order_by('last_name', 'ASC')->order_by('first_name', 'ASC')->limit($limit)->get()->result();
    }

    /** Next sequential resident_no for a barangay, formatted RES-{barangay_code}-{0001}. */
    public function generate_resident_no($barangay_id)
    {
        $barangay = $this->db->where('id', $barangay_id)->get('address_barangay')->row();
        $prefix = ($barangay && $barangay->code !== null && $barangay->code !== '') ? $barangay->code : ('B' . $barangay_id);

        $last = $this->db->select('resident_no')
            ->where('barangay_id', $barangay_id)
            ->like('resident_no', 'RES-' . $prefix . '-', 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)->row();

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last->resident_no);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('RES-%s-%04d', $prefix, $sequence);
    }

    public function create(array $data)
    {
        $data['is_senior_citizen'] = $this->is_senior($data['birthdate']) ? 1 : 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        $data['is_senior_citizen'] = $this->is_senior($data['birthdate']) ? 1 : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function archive($id)
    {
        return $this->db->where('id', $id)->update($this->table, ['archive' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    private function is_senior($birthdate)
    {
        return !empty($birthdate) && (new DateTime($birthdate))->diff(new DateTime())->y >= 60;
    }

    const AGE_BRACKETS = [
        '0-5' => [0, 5],
        '6-12' => [6, 12],
        '13-17' => [13, 17],
        '18-59' => [18, 59],
        '60+' => [60, 200],
    ];

    /** Population and demographic breakdowns for one barangay. */
    public function get_barangay_summary($barangay_id)
    {
        return $this->summarize('residents.barangay_id = ' . (int) $barangay_id);
    }

    /** Same breakdowns rolled up across every barangay in a municipality. */
    public function get_town_summary($municipality_id)
    {
        return $this->summarize('residents.barangay_id IN (SELECT id FROM address_barangay WHERE municipality_id = ' . (int) $municipality_id . ')');
    }

    private function summarize($resident_where)
    {
        $totals = $this->db
            ->select('COUNT(*) AS population, SUM(is_pwd) AS pwd_count, SUM(is_senior_citizen) AS senior_count, SUM(is_solo_parent) AS solo_parent_count, SUM(is_4ps_beneficiary) AS fourps_count')
            ->from($this->table)
            ->where('archive', 0)
            ->where($resident_where)
            ->get()->row();

        $sex_breakdown = ['Male' => 0, 'Female' => 0];
        $sex_rows = $this->db
            ->select('sex, COUNT(*) AS total')
            ->from($this->table)
            ->where('archive', 0)
            ->where($resident_where)
            ->group_by('sex')
            ->get()->result();
        foreach ($sex_rows as $row) {
            $sex_breakdown[$row->sex] = (int) $row->total;
        }

        $age_brackets = [];
        foreach (self::AGE_BRACKETS as $label => [$min, $max]) {
            $age_brackets[$label] = (int) $this->db
                ->from($this->table)
                ->where('archive', 0)
                ->where($resident_where)
                ->where('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >=', $min)
                ->where('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) <=', $max)
                ->count_all_results();
        }

        $household_count = (int) ($this->db
            ->select('COUNT(DISTINCT CONCAT(residents.barangay_id, "-", resident_household.household_no)) AS household_count', false)
            ->from($this->table)
            ->join('resident_household', 'resident_household.resident_id = residents.id')
            ->where('residents.archive', 0)
            ->where($resident_where)
            ->where('resident_household.household_no IS NOT NULL')
            ->get()->row()->household_count ?? 0);

        return (object) [
            'population' => (int) ($totals->population ?? 0),
            'sex_breakdown' => $sex_breakdown,
            'age_brackets' => $age_brackets,
            'pwd_count' => (int) ($totals->pwd_count ?? 0),
            'senior_count' => (int) ($totals->senior_count ?? 0),
            'solo_parent_count' => (int) ($totals->solo_parent_count ?? 0),
            'fourps_count' => (int) ($totals->fourps_count ?? 0),
            'household_count' => $household_count,
        ];
    }
}
