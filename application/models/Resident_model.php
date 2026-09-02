<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_model extends CI_Model
{
    protected $table = 'residents';

    // Section option lists moved to their per-section models:
    //   CIVIL_STATUS_OPTIONS, BLOOD_TYPE_OPTIONS -> Resident_personal_model
    //   EDUCATION_OPTIONS                        -> Resident_work_education_model

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
        $this->db->select('residents.id, residents.resident_no, residents.last_name, residents.first_name, residents.middle_name, residents.suffix, residents.sex, residents.birthdate, resident_personal.civil_status, resident_personal.religion, resident_work_education.occupation, resident_work_education.educational_attainment, resident_contact.contact_number')
            ->from($this->table)
            ->join('resident_personal', 'resident_personal.resident_id = residents.id', 'left')
            ->join('resident_work_education', 'resident_work_education.resident_id = residents.id', 'left')
            ->join('resident_contact', 'resident_contact.resident_id = residents.id', 'left')
            ->where('residents.archive', 0)
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

    /**
     * Next sequential Resident ID Number for a barangay, formatted
     * {municipality prefix}-{barangay prefix}-{0001} (e.g. PAL-SJO-0001).
     * Each prefix falls back to the area's `code`, then to an Mxx/Bxx placeholder.
     */
    public function generate_resident_no($barangay_id)
    {
        $barangay = $this->db
            ->select('address_barangay.code, address_barangay.prefix, address_barangay.municipality_id,
                      address_municipality.code AS municipality_code, address_municipality.prefix AS municipality_prefix')
            ->from('address_barangay')
            ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id', 'left')
            ->where('address_barangay.id', $barangay_id)
            ->get()->row();

        $muni_prefix = $this->id_prefix(
            $barangay->municipality_prefix ?? null,
            $barangay->municipality_code ?? null,
            'M' . ($barangay->municipality_id ?? 0)
        );
        $brgy_prefix = $this->id_prefix(
            $barangay->prefix ?? null,
            $barangay->code ?? null,
            'B' . $barangay_id
        );

        $stem = $muni_prefix . '-' . $brgy_prefix . '-';

        $last = $this->db->select('resident_no')
            ->where('barangay_id', $barangay_id)
            ->like('resident_no', $stem, 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)->row();

        $sequence = 1;
        if ($last) {
            $parts = explode('-', $last->resident_no);
            $sequence = (int) end($parts) + 1;
        }

        return sprintf('%s%04d', $stem, $sequence);
    }

    /** First non-empty of prefix / code / placeholder, upper-cased and reduced to A-Z0-9. */
    private function id_prefix($prefix, $code, $placeholder)
    {
        foreach ([$prefix, $code] as $candidate) {
            $candidate = preg_replace('/[^A-Z0-9]/', '', strtoupper(trim((string) $candidate)));
            if ($candidate !== '') {
                return $candidate;
            }
        }
        return $placeholder;
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

    /** Computed Senior Citizen flag; stored on resident_program_flags, set on save. */
    public static function is_senior($birthdate)
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
            ->select('COUNT(*) AS population, SUM(rpf.is_pwd) AS pwd_count, SUM(rpf.is_senior_citizen) AS senior_count, SUM(rpf.is_solo_parent) AS solo_parent_count, COUNT(rpf.is_4ps_beneficiary) AS fourps_count', false)
            ->from($this->table)
            ->join('resident_program_flags rpf', 'rpf.resident_id = residents.id', 'left')
            ->where('residents.archive', 0)
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
