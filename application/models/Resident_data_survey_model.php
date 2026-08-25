<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resident_data_survey_model extends CI_Model
{
    protected $table = 'resident_data_survey';

    const IMMUNIZATION_STATUS_OPTIONS = ['FIC', 'INC', 'No Immunization'];
    const COVID_VACCINE_STATUS_OPTIONS = ['1st Dose', '2nd Dose', 'Booster 1', 'Booster 2', 'Booster 3', 'None'];

    public function get_by_resident($resident_id)
    {
        return $this->db->where('resident_id', $resident_id)->get($this->table)->row();
    }

    /** Upserts the one Data Survey Tool row for a resident. */
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

    private function roster_select()
    {
        return $this->db->select("
                residents.id AS resident_id, residents.last_name, residents.first_name, residents.middle_name,
                residents.sex, residents.birthdate,
                resident_data_survey.immunization_status, resident_data_survey.covid_vaccine_status,
                resident_data_survey.schisto_mda_status, resident_data_survey.schisto_mda_date,
                resident_data_survey.eats_breakfast, resident_data_survey.eats_lunch, resident_data_survey.eats_snacks,
                resident_data_survey.exercises, resident_data_survey.exercise_frequency,
                resident_data_survey.has_recreational_activity
            ")
            ->from('residents')
            ->join($this->table, $this->table . '.resident_id = residents.id', 'left')
            ->where('residents.archive', 0);
    }

    /** Every active resident in a barangay, with their Data Survey Tool answers (blank if not yet surveyed). */
    public function get_survey_roster_by_barangay($barangay_id)
    {
        return $this->roster_select()
            ->where('residents.barangay_id', $barangay_id)
            ->order_by('residents.last_name', 'ASC')
            ->order_by('residents.first_name', 'ASC')
            ->get()->result();
    }

    /** Same as get_survey_roster_by_barangay(), but across every barangay in a municipality; each row also carries barangay_name. */
    public function get_survey_roster_by_municipality($municipality_id)
    {
        return $this->roster_select()
            ->select('address_barangay.name AS barangay_name')
            ->join('address_barangay', 'address_barangay.id = residents.barangay_id')
            ->where('address_barangay.municipality_id', $municipality_id)
            ->order_by('address_barangay.name', 'ASC')
            ->order_by('residents.last_name', 'ASC')
            ->order_by('residents.first_name', 'ASC')
            ->get()->result();
    }
}
