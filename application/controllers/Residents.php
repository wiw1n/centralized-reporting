<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Residents extends MY_Controller
{
    /** null = unrestricted (super_admin/admin); int = locked to this barangay; 0 = encoder has no barangay assigned */
    protected $restricted_barangay_id;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin', 'encoder']);
        $this->load->model('resident_model');
        $this->load->model('resident_personal_model');
        $this->load->model('resident_contact_model');
        $this->load->model('resident_work_education_model');
        $this->load->model('resident_government_ids_model');
        $this->load->model('resident_program_flags_model');
        $this->load->model('resident_remarks_model');
        $this->load->model('resident_household_model');
        $this->load->model('resident_data_survey_model');
        $this->load->model('barangay_model');
        $this->load->model('municipality_model');
        $this->load->model('province_model');
        $this->load->model('region_model');
        $this->load->library('form_validation');

        $this->restricted_barangay_id = null;
        if ($this->current_user->role_name === 'encoder') {
            $barangay_assignment = null;
            foreach ($this->authentication->assigned_areas() as $area) {
                if ($area->scope_type === 'barangay') {
                    $barangay_assignment = $area;
                    break;
                }
            }
            $this->restricted_barangay_id = $barangay_assignment->barangay_id ?? 0;
        }
        $this->data['restricted_barangay_id'] = $this->restricted_barangay_id;
    }

    public function index()
    {
        $this->data['page_title'] = 'Residents';
        $this->data['active_menu'] = 'residents';
        $this->data['regions'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_barangay'] = null;

        if ($this->restricted_barangay_id !== null) {
            $this->data['locked_barangay'] = $this->restricted_barangay_id
                ? $this->barangay_model->get_by_id($this->restricted_barangay_id)
                : null;
        } elseif ($this->restricted_municipality_id !== null) {
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($this->restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('residents/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $barangay_id = $this->input->get('barangay_id');
        $municipality_id = $this->input->get('municipality_id');
        $province_id = $this->input->get('province_id');
        $region_id = $this->input->get('region_id');
        $restricted_barangay_id = $this->restricted_barangay_id;
        $restricted_municipality_id = $this->restricted_municipality_id;

        $result = $this->datatable->response(
            function ($db) use ($barangay_id, $municipality_id, $province_id, $region_id, $restricted_barangay_id, $restricted_municipality_id) {
                $db->select("residents.id, residents.resident_no, resident_household.household_no,
                        CONCAT(residents.last_name, ', ', residents.first_name, ' ', COALESCE(residents.middle_name, '')) AS full_name,
                        residents.sex, residents.birthdate,
                        address_barangay.name AS barangay_name,
                        TIMESTAMPDIFF(YEAR, residents.birthdate, CURDATE()) AS age")
                    ->from('residents')
                    ->join('resident_household', 'resident_household.resident_id = residents.id', 'left')
                    ->join('address_barangay', 'address_barangay.id = residents.barangay_id')
                    ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id')
                    ->join('address_province', 'address_province.id = address_municipality.province_id')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('residents.archive', 0);

                if ($restricted_barangay_id !== null) {
                    $db->where('residents.barangay_id', $restricted_barangay_id);
                } elseif ($restricted_municipality_id !== null) {
                    $db->where('address_barangay.municipality_id', $restricted_municipality_id);
                    if ($barangay_id) {
                        $db->where('residents.barangay_id', $barangay_id);
                    }
                } elseif ($barangay_id) {
                    $db->where('residents.barangay_id', $barangay_id);
                } elseif ($municipality_id) {
                    $db->where('address_barangay.municipality_id', $municipality_id);
                } elseif ($province_id) {
                    $db->where('address_municipality.province_id', $province_id);
                } elseif ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['residents.resident_no', 'resident_household.household_no', 'residents.last_name', 'residents.first_name'],
            ['residents.resident_no', 'resident_household.household_no', 'full_name', 'address_barangay.name', 'residents.sex', 'age', null],
            function ($row) {
                return [
                    'resident_no' => html_escape($row->resident_no),
                    'household_no' => html_escape($row->household_no ?? ''),
                    'full_name' => html_escape(trim($row->full_name)),
                    'barangay_name' => html_escape($row->barangay_name),
                    'sex' => html_escape($row->sex),
                    'age' => (int) $row->age,
                    'actions' => '<a href="' . base_url('residents/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> '
                        . '<a href="' . base_url('residents/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this resident?\');"><i class="bi bi-trash"></i></a>',
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    /**
     * AJAX name lookup used by the household member form so an encoder can
     * link an already-profiled resident instead of re-typing a duplicate
     * individual. Scoped to the encoder's/admin's allowed barangay(s).
     */
    public function search()
    {
        $term = trim((string) $this->input->get('q'));
        if (mb_strlen($term) < 2) {
            $this->output->set_content_type('application/json')->set_output(json_encode([]));
            return;
        }

        $barangay_id = $this->input->get('barangay_id') ?: null;

        if ($this->restricted_barangay_id !== null) {
            $barangay_id = $this->restricted_barangay_id ?: null;
        } elseif ($barangay_id) {
            $barangay = $this->barangay_model->get_by_id($barangay_id);
            if (!$barangay || ($this->restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id)) {
                $barangay_id = null;
            }
        }

        $municipality_id = $barangay_id ? null : $this->restricted_municipality_id;

        $rows = $this->resident_model->search($term, $barangay_id, $municipality_id);

        $results = array_map(function ($r) {
            return [
                'id' => (int) $r->id,
                'resident_no' => $r->resident_no,
                'last_name' => $r->last_name,
                'first_name' => $r->first_name,
                'middle_name' => $r->middle_name,
                'suffix' => $r->suffix,
                'sex' => $r->sex,
                'birthdate' => $r->birthdate,
                'civil_status' => $r->civil_status,
                'religion' => $r->religion,
                'occupation' => $r->occupation,
                'educational_attainment' => $r->educational_attainment,
                'contact_number' => $r->contact_number,
                'label' => trim($r->last_name . ', ' . $r->first_name . ' ' . ($r->middle_name ?? ''))
                    . ' - ' . $r->sex . ', b. ' . $r->birthdate . ' (' . $r->resident_no . ')',
            ];
        }, $rows);

        $this->output->set_content_type('application/json')->set_output(json_encode($results));
    }

    public function create()
    {
        $restricted_barangay_id = $this->restricted_barangay_id;
        $restricted_municipality_id = $this->restricted_municipality_id;

        $this->data['page_title'] = 'Add Resident';
        $this->data['active_menu'] = 'residents';
        $this->data['resident'] = null;
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_municipality'] = null;
        $this->data['locked_barangay'] = null;

        if ($restricted_barangay_id !== null) {
            if (!$restricted_barangay_id) {
                $this->session->set_flashdata('error', 'No barangay is assigned to your account. Contact your Super Admin.');
                redirect('residents');
            }
            $this->data['locked_barangay'] = $this->barangay_model->get_by_id($restricted_barangay_id);
        } elseif ($restricted_municipality_id !== null) {
            if (!$restricted_municipality_id) {
                $this->session->set_flashdata('error', 'No active municipality is configured. Contact your Super Admin.');
                redirect('residents');
            }
            $this->data['locked_municipality'] = $this->municipality_model->get_by_id($restricted_municipality_id);
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        $this->set_validation_rules($restricted_barangay_id, $restricted_municipality_id);

        if ($this->form_validation->run() === true) {
            $barangay_id = $restricted_barangay_id !== null ? $restricted_barangay_id : $this->input->post('barangay_id');
            $resident_id = $this->resident_model->create(array_merge(
                $this->collect_post_fields(),
                [
                    'barangay_id' => $barangay_id,
                    'resident_no' => $this->resident_model->generate_resident_no($barangay_id),
                    'created_by' => $this->current_user->id,
                ]
            ));
            $this->resident_personal_model->save($resident_id, $this->collect_personal_fields());
            $this->resident_contact_model->save($resident_id, $this->collect_contact_fields());
            $this->resident_work_education_model->save($resident_id, $this->collect_work_education_fields());
            $this->resident_government_ids_model->save($resident_id, $this->collect_government_ids_fields());
            $this->resident_program_flags_model->save($resident_id, $this->collect_program_flags_fields());
            $this->resident_remarks_model->save($resident_id, $this->collect_remarks_fields());
            $this->resident_household_model->save($resident_id, $this->collect_household_fields());
            $this->resident_data_survey_model->save($resident_id, $this->collect_data_survey_fields());
            $this->session->set_flashdata('success', 'Resident created successfully.');
            redirect('residents');
        }

        if ($restricted_barangay_id === null && $restricted_municipality_id === null) {
            if ($this->input->post('province_id')) {
                $this->data['municipalities'] = $this->municipality_model->get_by_province($this->input->post('province_id'));
            }
            if ($this->input->post('region_id')) {
                $this->data['provinces'] = $this->province_model->get_by_region($this->input->post('region_id'));
            }
            if ($this->input->post('municipality_id')) {
                $this->data['barangays'] = $this->barangay_model->get_by_municipality($this->input->post('municipality_id'));
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('residents/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $resident = $this->resident_model->get_by_id($id);
        if (!$resident) {
            show_404();
        }

        $barangay = $this->barangay_model->get_by_id($resident->barangay_id);
        if (!$barangay) {
            show_404();
        }

        $restricted_barangay_id = $this->restricted_barangay_id;
        $restricted_municipality_id = $this->restricted_municipality_id;

        if ($restricted_barangay_id !== null && (int) $resident->barangay_id !== (int) $restricted_barangay_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
        if ($restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

        $this->data['page_title'] = 'Edit Resident';
        $this->data['active_menu'] = 'residents';
        $this->data['resident'] = $resident;
        $this->data['resident_personal'] = $this->resident_personal_model->get_by_resident($id);
        $this->data['resident_contact'] = $this->resident_contact_model->get_by_resident($id);
        $this->data['resident_work_education'] = $this->resident_work_education_model->get_by_resident($id);
        $this->data['resident_government_ids'] = $this->resident_government_ids_model->get_by_resident($id);
        $this->data['resident_program_flags'] = $this->resident_program_flags_model->get_by_resident($id);
        $this->data['resident_remarks'] = $this->resident_remarks_model->get_by_resident($id);
        $this->data['resident_household'] = $this->resident_household_model->get_by_resident($id);
        $this->data['resident_data_survey'] = $this->resident_data_survey_model->get_by_resident($id);
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['barangays'] = [];
        $this->data['current_region_id'] = $province->region_id ?? null;
        $this->data['current_province_id'] = $municipality->province_id ?? null;
        $this->data['current_municipality_id'] = $barangay->municipality_id;
        $this->data['locked_municipality'] = null;
        $this->data['locked_barangay'] = null;

        if ($restricted_barangay_id !== null) {
            $this->data['locked_barangay'] = $barangay;
        } elseif ($restricted_municipality_id !== null) {
            $this->data['locked_municipality'] = $municipality;
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
            $this->data['provinces'] = $province ? $this->province_model->get_by_region($province->region_id) : [];
            $this->data['municipalities'] = $municipality ? $this->municipality_model->get_by_province($municipality->province_id) : [];
            $this->data['barangays'] = $municipality ? $this->barangay_model->get_by_municipality($municipality->id) : [];
        }

        $this->set_validation_rules($restricted_barangay_id, $restricted_municipality_id);

        if ($this->form_validation->run() === true) {
            $barangay_id = $restricted_barangay_id !== null ? $restricted_barangay_id : $this->input->post('barangay_id');
            $this->resident_model->update($id, array_merge(
                $this->collect_post_fields(),
                ['barangay_id' => $barangay_id]
            ));
            $this->resident_personal_model->save($id, $this->collect_personal_fields());
            $this->resident_contact_model->save($id, $this->collect_contact_fields());
            $this->resident_work_education_model->save($id, $this->collect_work_education_fields());
            $this->resident_government_ids_model->save($id, $this->collect_government_ids_fields());
            $this->resident_program_flags_model->save($id, $this->collect_program_flags_fields());
            $this->resident_remarks_model->save($id, $this->collect_remarks_fields());
            $this->resident_household_model->save($id, $this->collect_household_fields());
            $this->resident_data_survey_model->save($id, $this->collect_data_survey_fields());
            $this->session->set_flashdata('success', 'Resident updated successfully.');
            redirect('residents');
        }

        if ($restricted_barangay_id === null && $restricted_municipality_id === null) {
            if ($this->input->post('province_id')) {
                $this->data['municipalities'] = $this->municipality_model->get_by_province($this->input->post('province_id'));
            }
            if ($this->input->post('region_id')) {
                $this->data['provinces'] = $this->province_model->get_by_region($this->input->post('region_id'));
            }
            if ($this->input->post('municipality_id')) {
                $this->data['barangays'] = $this->barangay_model->get_by_municipality($this->input->post('municipality_id'));
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('residents/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        $resident = $this->resident_model->get_by_id($id);
        if (!$resident) {
            show_404();
        }
        $barangay = $this->barangay_model->get_by_id($resident->barangay_id);

        if ($this->restricted_barangay_id !== null && (int) $resident->barangay_id !== (int) $this->restricted_barangay_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
        if ($this->restricted_municipality_id !== null && $barangay && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $this->resident_model->archive($id);
        $this->session->set_flashdata('success', 'Resident deleted successfully.');
        redirect('residents');
    }

    private function set_validation_rules($restricted_barangay_id, $restricted_municipality_id)
    {
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('middle_name', 'Middle Name', 'trim|max_length[100]');
        $this->form_validation->set_rules('suffix', 'Suffix', 'trim|max_length[20]');
        $this->form_validation->set_rules('sex', 'Sex', 'required|trim|in_list[Male,Female]');
        $this->form_validation->set_rules('birthdate', 'Birthdate', 'required|trim');
        $this->form_validation->set_rules('birthplace', 'Birthplace', 'trim|max_length[150]');
        $this->form_validation->set_rules('civil_status', 'Civil Status', 'required|trim|in_list[' . implode(',', Resident_personal_model::CIVIL_STATUS_OPTIONS) . ']');
        $this->form_validation->set_rules('religion', 'Religion', 'trim|max_length[50]');
        $this->form_validation->set_rules('citizenship', 'Citizenship', 'trim|max_length[50]');
        $this->form_validation->set_rules('blood_type', 'Blood Type', 'trim');
        $this->form_validation->set_rules('purok_sitio', 'Purok/Sitio', 'trim|max_length[100]');
        $this->form_validation->set_rules('address_line', 'Address', 'trim|max_length[150]');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'trim|max_length[20]');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('occupation', 'Occupation', 'trim|max_length[150]');
        $this->form_validation->set_rules('employer', 'Employer', 'trim|max_length[150]');
        $this->form_validation->set_rules('monthly_income', 'Monthly Income', 'trim|numeric');
        $this->form_validation->set_rules('educational_attainment', 'Educational Attainment', 'trim');
        $this->form_validation->set_rules('national_id_no', 'National ID No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('voters_id_no', "Voter's ID No.", 'trim|max_length[50]');
        $this->form_validation->set_rules('sss_no', 'SSS No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('gsis_no', 'GSIS No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('pagibig_no', 'Pag-IBIG No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('philhealth_no', 'PhilHealth No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('tin_no', 'TIN No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('yakap_no', 'Yakap No.', 'trim|max_length[50]');
        $this->form_validation->set_rules('indigenous_group', 'Indigenous Group', 'trim|max_length[100]');
        $this->form_validation->set_rules('remarks', 'Remarks', 'trim');

        $this->form_validation->set_rules('type_of_resident', 'Type of Resident', 'trim|in_list[' . implode(',', Resident_household_model::TYPE_OF_RESIDENT_OPTIONS) . ']');
        $this->form_validation->set_rules('household_no', 'Household No.', 'trim|max_length[30]');
        $this->form_validation->set_rules('relationship_to_head', 'Relationship to Head', 'trim');
        $this->form_validation->set_rules('ordinal_position', 'Ord. Position', 'trim|numeric');
        $this->form_validation->set_rules('other_illness', 'Other Illness', 'trim|max_length[150]');
        $this->form_validation->set_rules('gravida', 'Gravida', 'trim|numeric');
        $this->form_validation->set_rules('para', 'Para', 'trim|numeric');
        $this->form_validation->set_rules('lmp_date', 'LMP', 'trim');
        $this->form_validation->set_rules('edc_date', 'EDC', 'trim');
        $this->form_validation->set_rules('tt_status', 'TT Status', 'trim');
        $this->form_validation->set_rules('school_level', 'School Level', 'trim');
        $this->form_validation->set_rules('school_type', 'School Type', 'trim');
        $this->form_validation->set_rules('school_nutritional_status', 'School Nutritional Status', 'trim');
        $this->form_validation->set_rules('adult_medical_history', 'Adult Medical History', 'trim|max_length[255]');
        $this->form_validation->set_rules('adult_nutritional_status', 'Adult Nutritional Status', 'trim');
        $this->form_validation->set_rules('adolescent_medical_history', 'Adolescent Medical History', 'trim|max_length[255]');
        $this->form_validation->set_rules('adolescent_nutritional_status', 'Adolescent Nutritional Status', 'trim');
        $this->form_validation->set_rules('senior_medical_history', 'Senior Citizen Medical History', 'trim|max_length[255]');
        $this->form_validation->set_rules('senior_nutritional_status', 'Senior Citizen Nutritional Status', 'trim');
        $this->form_validation->set_rules('wra_fp_method', 'Family Planning Method Used', 'trim|max_length[100]');
        $this->form_validation->set_rules('wra_fp_facility_of_buying', 'Facility of Buying', 'trim|max_length[150]');
        $this->form_validation->set_rules('wra_fp_status_of_application', 'FP Status of Application', 'trim');
        $this->form_validation->set_rules('wra_papsmear_result', 'Pap Smear Result', 'trim|max_length[255]');
        $this->form_validation->set_rules('wra_nutritional_status', 'WRA Nutritional Status', 'trim');
        $this->form_validation->set_rules('child_immunization_status', 'Child Immunization Status', 'trim');
        $this->form_validation->set_rules('child_newborn_screening', 'Newborn Screening', 'trim');
        $this->form_validation->set_rules('child_newborn_screening_result', 'Newborn Screening Result', 'trim|max_length[255]');
        $this->form_validation->set_rules('child_infant_feeding', 'Infant Feeding', 'trim');
        $this->form_validation->set_rules('child_complementary_feeding', 'Complementary Feeding', 'trim');

        $this->form_validation->set_rules('immunization_status', 'Immun. Status', 'trim');
        $this->form_validation->set_rules('covid_vaccine_status', 'COVID-19 Immun. Status', 'trim');
        $this->form_validation->set_rules('schisto_mda_status', 'Schisto MDA Status', 'trim');
        $this->form_validation->set_rules('schisto_mda_date', 'Schisto MDA Date of Tx', 'trim');
        $this->form_validation->set_rules('exercises', 'Exercise', 'trim');
        $this->form_validation->set_rules('exercise_frequency', 'Exercise Frequency', 'trim|max_length[50]');
        $this->form_validation->set_rules('has_recreational_activity', 'Recreational Activity', 'trim');

        if ($restricted_barangay_id === null) {
            $this->form_validation->set_rules('barangay_id', 'Barangay', 'required|trim|numeric');
            if ($restricted_municipality_id === null) {
                $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
                $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
                $this->form_validation->set_rules('municipality_id', 'Municipality', 'required|trim|numeric');
            }
        }
    }

    /** Maps posted identity fields (everything else is split into per-section child tables) onto the residents row shape. */
    private function collect_post_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'last_name' => $this->input->post('last_name'),
            'first_name' => $this->input->post('first_name'),
            'middle_name' => $text('middle_name'),
            'suffix' => $text('suffix'),
            'sex' => $this->input->post('sex'),
            'birthdate' => $this->input->post('birthdate'),
        ];
    }

    /** Maps posted Personal Information fields onto the resident_personal row shape. */
    private function collect_personal_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'birthplace' => $text('birthplace'),
            'civil_status' => $this->input->post('civil_status'),
            'religion' => $text('religion'),
            'citizenship' => $this->input->post('citizenship') ?: 'Filipino',
            'blood_type' => $text('blood_type'),
        ];
    }

    /** Maps posted Contact & Address fields onto the resident_contact row shape. */
    private function collect_contact_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'purok_sitio' => $text('purok_sitio'),
            'address_line' => $text('address_line'),
            'contact_number' => $text('contact_number'),
            'email' => $text('email'),
        ];
    }

    /** Maps posted Occupation & Education fields onto the resident_work_education row shape. */
    private function collect_work_education_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'occupation' => $text('occupation'),
            'employer' => $text('employer'),
            'monthly_income' => $text('monthly_income'),
            'educational_attainment' => $text('educational_attainment'),
        ];
    }

    /** Maps posted Government IDs fields onto the resident_government_ids row shape. */
    private function collect_government_ids_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'national_id_no' => $text('national_id_no'),
            'voters_id_no' => $text('voters_id_no'),
            'sss_no' => $text('sss_no'),
            'gsis_no' => $text('gsis_no'),
            'pagibig_no' => $text('pagibig_no'),
            'philhealth_no' => $text('philhealth_no'),
            'tin_no' => $text('tin_no'),
            'yakap_no' => $text('yakap_no'),
        ];
    }

    /** Maps posted Program Flags fields onto the resident_program_flags row shape. */
    private function collect_program_flags_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'is_pwd' => $this->input->post('is_pwd') ? 1 : 0,
            'is_senior_citizen' => Resident_model::is_senior($this->input->post('birthdate')) ? 1 : 0,
            'is_solo_parent' => $this->input->post('is_solo_parent') ? 1 : 0,
            // A varchar holding the 4Ps ID Number: NULL = not a beneficiary,
            // '' = beneficiary with an unknown ID, otherwise the ID string.
            'is_4ps_beneficiary' => $this->input->post('is_4ps_beneficiary') ? ($text('fourps_id_number') ?? '') : null,
            'is_ofw' => $this->input->post('is_ofw') ? 1 : 0,
            'is_indigenous' => $this->input->post('is_indigenous') ? 1 : 0,
            'indigenous_group' => $text('indigenous_group'),
        ];
    }

    /** Maps the posted Remarks field onto the resident_remarks row shape. */
    private function collect_remarks_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;

        return [
            'remarks' => $text('remarks'),
        ];
    }

    /** Maps posted household-profile fields onto the resident_household row shape. */
    private function collect_household_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;
        $int = fn ($field) => trim((string) $this->input->post($field)) !== '' ? (int) $this->input->post($field) : null;

        return [
            'type_of_resident' => in_array($this->input->post('type_of_resident'), Resident_household_model::TYPE_OF_RESIDENT_OPTIONS, true) ? $this->input->post('type_of_resident') : null,
            'household_no' => $text('household_no'),
            'relationship_to_head' => $text('relationship_to_head'),
            'ordinal_position' => $int('ordinal_position'),
            'is_surveyed' => $this->input->post('is_surveyed') ? 1 : 0,
            'has_hypertension' => $this->input->post('has_hypertension') ? 1 : 0,
            'has_diabetes' => $this->input->post('has_diabetes') ? 1 : 0,
            'has_asthma' => $this->input->post('has_asthma') ? 1 : 0,
            'other_illness' => $text('other_illness'),
            'is_pregnant' => $this->input->post('is_pregnant') ? 1 : 0,
            'is_lactating' => $this->input->post('is_lactating') ? 1 : 0,
            'gravida' => $int('gravida'),
            'para' => $int('para'),
            'lmp_date' => $text('lmp_date'),
            'edc_date' => $text('edc_date'),
            'tt_status' => (in_array($this->input->post('tt_status'), Resident_household_model::TT_STATUS_OPTIONS, true)) ? $this->input->post('tt_status') : null,
            'school_level' => $text('school_level'),
            'school_type' => $text('school_type'),
            'school_weighed' => $this->input->post('school_weighed') ? 1 : 0,
            'school_nutritional_status' => $text('school_nutritional_status'),
            'adult_medical_history' => $text('adult_medical_history'),
            'adult_nutritional_status' => in_array($this->input->post('adult_nutritional_status'), Resident_household_model::LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS, true) ? $this->input->post('adult_nutritional_status') : null,
            'adolescent_medical_history' => $text('adolescent_medical_history'),
            'adolescent_nutritional_status' => in_array($this->input->post('adolescent_nutritional_status'), Resident_household_model::LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS, true) ? $this->input->post('adolescent_nutritional_status') : null,
            'senior_medical_history' => $text('senior_medical_history'),
            'senior_nutritional_status' => in_array($this->input->post('senior_nutritional_status'), Resident_household_model::LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS, true) ? $this->input->post('senior_nutritional_status') : null,
            'wra_mns_iron_folic' => $this->input->post('wra_mns_iron_folic') ? 1 : 0,
            'wra_mns_calcium_carbonate' => $this->input->post('wra_mns_calcium_carbonate') ? 1 : 0,
            'wra_mns_mms' => $this->input->post('wra_mns_mms') ? 1 : 0,
            'wra_fp_method' => in_array($this->input->post('wra_fp_method'), Resident_household_model::WRA_FP_METHOD_OPTIONS, true) ? $this->input->post('wra_fp_method') : null,
            'wra_fp_facility_of_buying' => $text('wra_fp_facility_of_buying'),
            'wra_fp_status_of_application' => in_array($this->input->post('wra_fp_status_of_application'), Resident_household_model::WRA_FP_STATUS_OF_APPLICATION_OPTIONS, true) ? $this->input->post('wra_fp_status_of_application') : null,
            'wra_papsmear_done' => $this->input->post('wra_papsmear_done') ? 1 : 0,
            'wra_papsmear_result' => $text('wra_papsmear_result'),
            'wra_nutritional_status' => in_array($this->input->post('wra_nutritional_status'), Resident_household_model::LIFESTAGE_NUTRITIONAL_STATUS_OPTIONS, true) ? $this->input->post('wra_nutritional_status') : null,
            'child_immunization_status' => in_array($this->input->post('child_immunization_status'), Resident_household_model::CHILD_IMMUNIZATION_STATUS_OPTIONS, true) ? $this->input->post('child_immunization_status') : null,
            'child_newborn_screening' => in_array($this->input->post('child_newborn_screening'), ['Yes', 'No'], true) ? $this->input->post('child_newborn_screening') : null,
            'child_newborn_screening_result' => $this->input->post('child_newborn_screening') === 'Yes' ? $text('child_newborn_screening_result') : null,
            'child_infant_feeding' => in_array($this->input->post('child_infant_feeding'), Resident_household_model::CHILD_INFANT_FEEDING_OPTIONS, true) ? $this->input->post('child_infant_feeding') : null,
            'child_complementary_feeding' => in_array($this->input->post('child_complementary_feeding'), Resident_household_model::CHILD_COMPLEMENTARY_FEEDING_OPTIONS, true) ? $this->input->post('child_complementary_feeding') : null,
            'child_mns_deworming' => $this->input->post('child_mns_deworming') ? 1 : 0,
            'child_mns_vit_a' => $this->input->post('child_mns_vit_a') ? 1 : 0,
            'child_mns_micronutrient_powder' => $this->input->post('child_mns_micronutrient_powder') ? 1 : 0,
            'child_mns_ferrous_sulfate' => $this->input->post('child_mns_ferrous_sulfate') ? 1 : 0,
            'child_mns_multivitamins' => $this->input->post('child_mns_multivitamins') ? 1 : 0,
        ];
    }

    /** Maps posted Data Survey Tool fields onto the resident_data_survey row shape. */
    private function collect_data_survey_fields()
    {
        $text = fn ($field) => trim((string) $this->input->post($field)) !== '' ? trim((string) $this->input->post($field)) : null;
        $yn = fn ($field) => in_array($this->input->post($field), ['0', '1'], true) ? (int) $this->input->post($field) : null;

        return [
            'immunization_status' => in_array($this->input->post('immunization_status'), Resident_data_survey_model::IMMUNIZATION_STATUS_OPTIONS, true) ? $this->input->post('immunization_status') : null,
            'covid_vaccine_status' => in_array($this->input->post('covid_vaccine_status'), Resident_data_survey_model::COVID_VACCINE_STATUS_OPTIONS, true) ? $this->input->post('covid_vaccine_status') : null,
            'schisto_mda_status' => $yn('schisto_mda_status'),
            'schisto_mda_date' => $text('schisto_mda_date'),
            'eats_breakfast' => $this->input->post('eats_breakfast') ? 1 : 0,
            'eats_lunch' => $this->input->post('eats_lunch') ? 1 : 0,
            'eats_snacks' => $this->input->post('eats_snacks') ? 1 : 0,
            'exercises' => $yn('exercises'),
            'exercise_frequency' => $text('exercise_frequency'),
            'has_recreational_activity' => $yn('has_recreational_activity'),
        ];
    }
}
