<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Households extends MY_Controller
{
    /** null = unrestricted (super_admin/admin); int = locked to this barangay; 0 = encoder has no barangay assigned */
    protected $restricted_barangay_id;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin', 'encoder']);
        $this->load->model('household_model');
        $this->load->model('household_member_model');
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
        $this->data['page_title'] = 'Households';
        $this->data['active_menu'] = 'households';
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
        $this->load->view('households/index', $this->data);
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
                $db->select("households.id, households.household_no, address_barangay.name AS barangay_name,
                        (SELECT COUNT(*) FROM household_members hm WHERE hm.household_id = households.id) AS member_count,
                        (SELECT CONCAT(hm2.last_name, ', ', hm2.first_name) FROM household_members hm2 WHERE hm2.household_id = households.id AND hm2.relationship_to_head = 'Head' LIMIT 1) AS head_name")
                    ->from('households')
                    ->join('address_barangay', 'address_barangay.id = households.barangay_id')
                    ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id')
                    ->join('address_province', 'address_province.id = address_municipality.province_id')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('households.archive', 0);

                if ($restricted_barangay_id !== null) {
                    $db->where('households.barangay_id', $restricted_barangay_id);
                } elseif ($restricted_municipality_id !== null) {
                    $db->where('address_barangay.municipality_id', $restricted_municipality_id);
                    if ($barangay_id) {
                        $db->where('households.barangay_id', $barangay_id);
                    }
                } elseif ($barangay_id) {
                    $db->where('households.barangay_id', $barangay_id);
                } elseif ($municipality_id) {
                    $db->where('address_barangay.municipality_id', $municipality_id);
                } elseif ($province_id) {
                    $db->where('address_municipality.province_id', $province_id);
                } elseif ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['households.household_no'],
            ['households.household_no', 'address_barangay.name', 'head_name', 'member_count', null],
            function ($row) {
                return [
                    'household_no' => html_escape($row->household_no),
                    'barangay_name' => html_escape($row->barangay_name),
                    'head_name' => $row->head_name ? html_escape($row->head_name) : '<span class="text-muted">&mdash; No head recorded &mdash;</span>',
                    'member_count' => (int) $row->member_count,
                    'actions' => '<a href="' . base_url('households/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> '
                        . '<a href="' . base_url('households/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this household?\');"><i class="bi bi-trash"></i></a>',
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        $restricted_barangay_id = $this->restricted_barangay_id;
        $restricted_municipality_id = $this->restricted_municipality_id;

        $this->data['page_title'] = 'Add Household';
        $this->data['active_menu'] = 'households';
        $this->data['household'] = null;
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_municipality'] = null;
        $this->data['locked_barangay'] = null;

        if ($restricted_barangay_id !== null) {
            if (!$restricted_barangay_id) {
                $this->session->set_flashdata('error', 'No barangay is assigned to your account. Contact your Super Admin.');
                redirect('households');
            }
            $this->data['locked_barangay'] = $this->barangay_model->get_by_id($restricted_barangay_id);
        } elseif ($restricted_municipality_id !== null) {
            if (!$restricted_municipality_id) {
                $this->session->set_flashdata('error', 'No active municipality is configured. Contact your Super Admin.');
                redirect('households');
            }
            $this->data['locked_municipality'] = $this->municipality_model->get_by_id($restricted_municipality_id);
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        $this->form_validation->set_rules('purok_sitio', 'Purok/Sitio', 'trim|max_length[100]');
        $this->form_validation->set_rules('address_line', 'Address', 'trim|max_length[150]');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'trim|max_length[20]');
        $this->form_validation->set_rules('remarks', 'Remarks', 'trim');
        if ($restricted_barangay_id === null) {
            $this->form_validation->set_rules('barangay_id', 'Barangay', 'required|trim|numeric');
            if ($restricted_municipality_id === null) {
                $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
                $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
                $this->form_validation->set_rules('municipality_id', 'Municipality', 'required|trim|numeric');
            }
        }

        $members = $this->input->post('members') ?: [];
        $member_errors = [];

        if ($this->form_validation->run() === true) {
            $barangay_id = $restricted_barangay_id !== null ? $restricted_barangay_id : $this->input->post('barangay_id');
            $member_errors = $this->validate_members($members);

            if (empty($member_errors)) {
                $household_id = $this->household_model->create([
                    'barangay_id' => $barangay_id,
                    'household_no' => $this->household_model->generate_household_no($barangay_id),
                    'purok_sitio' => $this->input->post('purok_sitio'),
                    'address_line' => $this->input->post('address_line'),
                    'contact_number' => $this->input->post('contact_number'),
                    'remarks' => $this->input->post('remarks'),
                    'is_surveyed' => $this->input->post('is_surveyed') ? 1 : 0,
                    'created_by' => $this->current_user->id,
                ]);
                $this->household_member_model->replace_members_for_household($household_id, $members);
                $this->session->set_flashdata('success', 'Household created successfully.');
                redirect('households');
            }
        }

        if (!empty($member_errors)) {
            $this->session->set_flashdata('error', implode(' ', $member_errors));
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

        $this->data['posted_members'] = !empty($members) ? $members : null;
        $this->data['existing_members'] = [];

        $this->load->view('templates/header', $this->data);
        $this->load->view('households/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $household = $this->household_model->get_by_id($id);
        if (!$household) {
            show_404();
        }

        $barangay = $this->barangay_model->get_by_id($household->barangay_id);
        if (!$barangay) {
            show_404();
        }

        $restricted_barangay_id = $this->restricted_barangay_id;
        $restricted_municipality_id = $this->restricted_municipality_id;

        if ($restricted_barangay_id !== null && (int) $household->barangay_id !== (int) $restricted_barangay_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
        if ($restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

        $this->data['page_title'] = 'Edit Household';
        $this->data['active_menu'] = 'households';
        $this->data['household'] = $household;
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

        $this->form_validation->set_rules('purok_sitio', 'Purok/Sitio', 'trim|max_length[100]');
        $this->form_validation->set_rules('address_line', 'Address', 'trim|max_length[150]');
        $this->form_validation->set_rules('contact_number', 'Contact Number', 'trim|max_length[20]');
        $this->form_validation->set_rules('remarks', 'Remarks', 'trim');
        if ($restricted_barangay_id === null) {
            $this->form_validation->set_rules('barangay_id', 'Barangay', 'required|trim|numeric');
            if ($restricted_municipality_id === null) {
                $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
                $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
                $this->form_validation->set_rules('municipality_id', 'Municipality', 'required|trim|numeric');
            }
        }

        $members = $this->input->post('members') ?: [];
        $member_errors = [];

        if ($this->form_validation->run() === true) {
            $barangay_id = $restricted_barangay_id !== null ? $restricted_barangay_id : $this->input->post('barangay_id');
            $member_errors = $this->validate_members($members);

            if (empty($member_errors)) {
                $this->household_model->update($id, [
                    'barangay_id' => $barangay_id,
                    'purok_sitio' => $this->input->post('purok_sitio'),
                    'address_line' => $this->input->post('address_line'),
                    'contact_number' => $this->input->post('contact_number'),
                    'remarks' => $this->input->post('remarks'),
                    'is_surveyed' => $this->input->post('is_surveyed') ? 1 : 0,
                ]);
                $this->household_member_model->replace_members_for_household($id, $members);
                $this->session->set_flashdata('success', 'Household updated successfully.');
                redirect('households');
            }
        }

        if (!empty($member_errors)) {
            $this->session->set_flashdata('error', implode(' ', $member_errors));
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

        $this->data['posted_members'] = !empty($members) ? $members : null;
        $this->data['existing_members'] = $this->household_member_model->get_by_household($id);

        $this->load->view('templates/header', $this->data);
        $this->load->view('households/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        $household = $this->household_model->get_by_id($id);
        if (!$household) {
            show_404();
        }
        $barangay = $this->barangay_model->get_by_id($household->barangay_id);

        if ($this->restricted_barangay_id !== null && (int) $household->barangay_id !== (int) $this->restricted_barangay_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
        if ($this->restricted_municipality_id !== null && $barangay && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $this->household_model->archive($id);
        $this->session->set_flashdata('success', 'Household deleted successfully.');
        redirect('households');
    }

    /** Read-only demographic summary report for one barangay. */
    public function profile($barangay_id)
    {
        $barangay = $this->barangay_model->get_by_id($barangay_id);
        if (!$barangay) {
            show_404();
        }

        if ($this->restricted_barangay_id !== null && (int) $barangay_id !== (int) $this->restricted_barangay_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
        if ($this->restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;
        $region = $province ? $this->region_model->get_by_id($province->region_id) : null;

        $this->data['page_title'] = 'Barangay Profile';
        $this->data['active_menu'] = 'households';
        $this->data['barangay'] = $barangay;
        $this->data['municipality'] = $municipality;
        $this->data['province'] = $province;
        $this->data['region'] = $region;
        $this->data['summary'] = $this->household_model->get_barangay_summary($barangay_id);

        $this->load->view('templates/header', $this->data);
        $this->load->view('households/profile', $this->data);
        $this->load->view('templates/footer');
    }

    /**
     * Validates the posted member rows: required fields per row, valid
     * option values, and exactly one row marked as household Head.
     * Returns an array of human-readable error strings (empty = valid).
     */
    private function validate_members(array $rows)
    {
        $errors = [];

        if (empty($rows)) {
            $errors[] = 'A household must have at least one member.';
            return $errors;
        }

        $head_count = 0;
        foreach ($rows as $i => $row) {
            $label = 'Member #' . ($i + 1) . ': ';

            if (empty($row['last_name']) || empty($row['first_name'])) {
                $errors[] = $label . 'last name and first name are required.';
            }
            if (!empty($row['relationship_to_head']) && in_array($row['relationship_to_head'], Household_model::RELATIONSHIP_OPTIONS, true)) {
                if ($row['relationship_to_head'] === 'Head') {
                    $head_count++;
                }
            } else {
                $errors[] = $label . 'a valid relationship to head is required.';
            }
            if (empty($row['sex']) || !in_array($row['sex'], ['Male', 'Female'], true)) {
                $errors[] = $label . 'sex is required.';
            }
            if (empty($row['birthdate'])) {
                $errors[] = $label . 'birthdate is required.';
            }
            if (empty($row['civil_status']) || !in_array($row['civil_status'], Household_model::CIVIL_STATUS_OPTIONS, true)) {
                $errors[] = $label . 'a valid civil status is required.';
            }
        }

        if ($head_count === 0) {
            $errors[] = 'A household must have exactly one member marked as Head.';
        } elseif ($head_count > 1) {
            $errors[] = 'Only one member may be marked as Head.';
        }

        return $errors;
    }
}
