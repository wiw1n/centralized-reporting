<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_household_model extends CI_Model
{
    protected $table = 'resident_household';

    const TYPE_OF_RESIDENT_OPTIONS = ['Resident', 'Non-Resident'];
    const RELATIONSHIP_OPTIONS = ['Head', 'Member', 'Spouse', 'Son', 'Daughter', 'Parent', 'Sibling', 'Grandchild', 'Other Relative', 'Boarder', 'Other'];
    const TT_STATUS_OPTIONS = ['TT1', 'TT2', 'TT3', 'TT4', 'TT5', 'Fully Immunized'];
    const NUTRITIONAL_STATUS_WEIGHT_AGE_OPTIONS = ['Severely Underweight', 'Underweight', 'Normal', 'Overweight'];
    const NUTRITIONAL_STATUS_HEIGHT_AGE_OPTIONS = ['Severely Stunted', 'Stunted', 'Normal', 'Tall'];
    const NUTRITIONAL_STATUS_WEIGHT_HEIGHT_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];
    const SCHOOL_LEVEL_OPTIONS = ['Day Care', 'Kindergarten', 'Elementary'];
    const SCHOOL_TYPE_OPTIONS = ['Public', 'Private'];
    const SCHOOL_NUTRITIONAL_STATUS_OPTIONS = ['Severely Wasted', 'Wasted', 'Normal', 'Overweight', 'Obese'];
    const LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS = ['Underweight', 'Normal', 'Overweight', 'Obese Class I', 'Obese Class II'];
    const WRA_FP_METHOD_OPTIONS = ['Pills (COC)', 'Pills (POP)', 'Injectable (DMPA)', 'IUD', 'Implant', 'Condom', 'BTL', 'Vasectomy', 'LAM', 'Natural / Fertility Awareness', 'Withdrawal', 'Other', 'None'];
    const WRA_FP_STATUS_OF_APPLICATION_OPTIONS = ['New Acceptor', 'Current User', 'Changing Method', 'Changing Clinic', 'Dropout', 'Restart'];
    const CHILD_IMMUNIZATION_STATUS_OPTIONS = ['Fully Immunized (FIC)', 'Completely Immunized (CIC)', 'Partially Immunized', 'Unimmunized'];
    const CHILD_INFANT_FEEDING_OPTIONS = ['Exclusive Breastfeeding', 'Formula Feeding', 'Mixed Feeding', 'Food'];
    const CHILD_COMPLEMENTARY_FEEDING_OPTIONS = ['Breastfeeding + Food', 'Formula Feeding + Food', 'Mixed Feeding + Food', 'Food'];

    const ENV_TOILET_TYPE_OPTIONS = ['Water Sealed', 'Open Pit', 'Others', 'None'];
    const ENV_WATER_SOURCE_OPTIONS = ['Pipe', 'Well', 'Spring'];
    const ENV_OWNER_SHARER_OPTIONS = ['Owner', 'Sharer'];
    const ENV_HOUSE_TYPE_OPTIONS = ['Light Materials', 'Concrete', 'Semi Concrete'];
    const ENV_LOT_OCCUPANCY_OPTIONS = ['Owner', 'Rented', 'Sharer', 'Illegal Settler'];

    public function get_by_resident($resident_id)
    {
        return $this->db->where('resident_id', $resident_id)->get($this->table)->row();
    }

    /**
     * Upserts the one household-profile row for a resident.
     *
     * A "household" here is just residents sharing the same household_no
     * within a barangay -- there's no separate households table -- so the
     * house's map coordinates are kept in sync across every member: if this
     * resident submitted coordinates, they're pushed out to the rest of the
     * household; if not, they're pulled in from a sibling that already has
     * them (so joining an already-pinned household auto-adopts its location).
     */
    public function save($resident_id, array $data, $barangay_id = null)
    {
        $data['resident_id'] = $resident_id;
        $existing = $this->get_by_resident($resident_id);

        $household_no = $data['household_no'] ?? null;
        $has_own_coordinates = ($data['latitude'] ?? null) !== null && ($data['longitude'] ?? null) !== null;

        if ($household_no !== null && $household_no !== '' && $barangay_id && !$has_own_coordinates) {
            $sibling = $this->get_household_coordinates($household_no, $barangay_id, $resident_id);
            if ($sibling) {
                $data['latitude'] = $sibling->latitude;
                $data['longitude'] = $sibling->longitude;
                $has_own_coordinates = true;
            }
        }

        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db->where('resident_id', $resident_id)->update($this->table, $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
        }

        if ($household_no !== null && $household_no !== '' && $barangay_id && $has_own_coordinates) {
            $this->sync_household_coordinates($household_no, $barangay_id, $resident_id, $data['latitude'], $data['longitude']);
        }

        return $existing ? true : $this->db->insert_id();
    }

    /** Coordinates already set by another member of the same household (household_no + barangay), if any. */
    private function get_household_coordinates($household_no, $barangay_id, $exclude_resident_id)
    {
        return $this->db->select('resident_household.latitude, resident_household.longitude')
            ->from($this->table)
            ->join('residents', 'residents.id = resident_household.resident_id')
            ->where('resident_household.household_no', $household_no)
            ->where('residents.barangay_id', $barangay_id)
            ->where('resident_household.resident_id !=', $exclude_resident_id)
            ->where('resident_household.latitude IS NOT NULL')
            ->where('resident_household.longitude IS NOT NULL')
            ->limit(1)
            ->get()->row();
    }

    /** Pushes coordinates onto every other resident sharing this household_no + barangay. */
    private function sync_household_coordinates($household_no, $barangay_id, $resident_id, $latitude, $longitude)
    {
        $siblings = $this->db->select('residents.id')
            ->from('residents')
            ->join($this->table, $this->table . '.resident_id = residents.id')
            ->where($this->table . '.household_no', $household_no)
            ->where('residents.barangay_id', $barangay_id)
            ->where('residents.id !=', $resident_id)
            ->get()->result();

        if (!$siblings) {
            return;
        }

        $this->db->where_in('resident_id', array_column($siblings, 'id'))->update($this->table, [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Residents in a barangay that have a household_no, grouped into households for the BHW Family Profiling Form. */
    public function get_household_roster_by_barangay($barangay_id)
    {
        $rows = $this->db->select("
                resident_household.household_no, resident_household.latitude, resident_household.longitude,
                resident_household.relationship_to_head, resident_household.ordinal_position,
                resident_household.has_hypertension, resident_household.has_diabetes, resident_household.has_asthma, resident_household.other_illness,
                resident_household.gravida, resident_household.para, resident_household.lmp_date, resident_household.edc_date, resident_household.tt_status,
                residents.last_name, residents.first_name, residents.middle_name, residents.sex, residents.birthdate,
                resident_work_education.educational_attainment, resident_work_education.occupation, resident_personal.religion
            ")
            ->from($this->table)
            ->join('residents', 'residents.id = resident_household.resident_id')
            ->join('resident_personal', 'resident_personal.resident_id = residents.id', 'left')
            ->join('resident_work_education', 'resident_work_education.resident_id = residents.id', 'left')
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
                $households[$row->household_no] = (object) ['household_no' => $row->household_no, 'latitude' => null, 'longitude' => null, 'members' => []];
            }
            if ($households[$row->household_no]->latitude === null && $row->latitude !== null && $row->longitude !== null) {
                $households[$row->household_no]->latitude = $row->latitude;
                $households[$row->household_no]->longitude = $row->longitude;
            }
            $households[$row->household_no]->members[] = $row;
        }

        return array_values($households);
    }

    /** Same as get_household_roster_by_barangay(), but across every barangay in a municipality; each household also carries its barangay_name. */
    public function get_household_roster_by_municipality($municipality_id)
    {
        $rows = $this->db->select("
                resident_household.household_no, resident_household.latitude, resident_household.longitude,
                resident_household.relationship_to_head, resident_household.ordinal_position,
                resident_household.has_hypertension, resident_household.has_diabetes, resident_household.has_asthma, resident_household.other_illness,
                resident_household.gravida, resident_household.para, resident_household.lmp_date, resident_household.edc_date, resident_household.tt_status,
                residents.last_name, residents.first_name, residents.middle_name, residents.sex, residents.birthdate,
                resident_work_education.educational_attainment, resident_work_education.occupation, resident_personal.religion,
                residents.barangay_id, address_barangay.name AS barangay_name
            ")
            ->from($this->table)
            ->join('residents', 'residents.id = resident_household.resident_id')
            ->join('resident_personal', 'resident_personal.resident_id = residents.id', 'left')
            ->join('resident_work_education', 'resident_work_education.resident_id = residents.id', 'left')
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
                $households[$key] = (object) ['household_no' => $row->household_no, 'barangay_name' => $row->barangay_name, 'latitude' => null, 'longitude' => null, 'members' => []];
            }
            if ($households[$key]->latitude === null && $row->latitude !== null && $row->longitude !== null) {
                $households[$key]->latitude = $row->latitude;
                $households[$key]->longitude = $row->longitude;
            }
            $households[$key]->members[] = $row;
        }

        return array_values($households);
    }
}
