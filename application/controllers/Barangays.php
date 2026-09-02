<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barangays extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin']);
        $this->load->model('barangay_model');
        $this->load->model('municipality_model');
        $this->load->model('province_model');
        $this->load->model('region_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['page_title'] = 'Barangays';
        $this->data['active_menu'] = 'barangays';
        $this->data['regions'] = $this->restricted_municipality_id === null ? $this->region_model->get_all() : [];

        $this->load->view('templates/header', $this->data);
        $this->load->view('barangays/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $municipality_id = $this->input->get('municipality_id');
        $province_id = $this->input->get('province_id');
        $region_id = $this->input->get('region_id');
        $restricted_id = $this->restricted_municipality_id;

        $result = $this->datatable->response(
            function ($db) use ($municipality_id, $province_id, $region_id, $restricted_id) {
                $db->select('address_barangay.id, address_barangay.name, address_barangay.prefix, address_barangay.poblacion, address_municipality.name AS municipality_name, address_province.name AS province_name, address_region.name AS region_name')
                    ->from('address_barangay')
                    ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id')
                    ->join('address_province', 'address_province.id = address_municipality.province_id')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('address_barangay.archive', 0);
                if ($restricted_id !== null) {
                    $db->where('address_barangay.municipality_id', $restricted_id);
                } elseif ($municipality_id) {
                    $db->where('address_barangay.municipality_id', $municipality_id);
                } elseif ($province_id) {
                    $db->where('address_municipality.province_id', $province_id);
                } elseif ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['address_barangay.name', 'address_barangay.prefix'],
            ['address_barangay.name', 'address_barangay.prefix', 'address_municipality.name', 'address_province.name', null, null],
            function ($row) {
                return [
                    'name' => html_escape($row->name),
                    'prefix' => html_escape($row->prefix ?? ''),
                    'municipality_name' => html_escape($row->municipality_name),
                    'province_name' => html_escape($row->province_name),
                    'poblacion' => $row->poblacion ? '<span class="badge bg-info">Yes</span>' : '',
                    'actions' => '<a href="' . base_url('barangays/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> '
                        . '<a href="' . base_url('barangays/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this barangay?\');"><i class="bi bi-trash"></i></a>',
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        $restricted_id = $this->restricted_municipality_id;
        $this->data['page_title'] = 'Add Barangay';
        $this->data['active_menu'] = 'barangays';
        $this->data['barangay'] = null;
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['locked_municipality'] = null;

        if ($restricted_id !== null) {
            if (!$restricted_id) {
                $this->session->set_flashdata('error', 'No active municipality is configured. Contact your Super Admin.');
                redirect('barangays');
            }
            $this->data['locked_municipality'] = $this->municipality_model->get_by_id($restricted_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        $this->form_validation->set_rules('name', 'Barangay Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('prefix', 'Prefix', 'trim|max_length[10]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('total_puroks', 'Total Number of Puroks', 'trim|numeric');
        $this->form_validation->set_rules('day_care_centers_public', 'Day Care Centers (Public)', 'trim|numeric');
        $this->form_validation->set_rules('day_care_centers_private', 'Day Care Centers (Private)', 'trim|numeric');
        $this->form_validation->set_rules('elementary_schools_public', 'Elementary Schools (Public)', 'trim|numeric');
        $this->form_validation->set_rules('elementary_schools_private', 'Elementary Schools (Private)', 'trim|numeric');
        if ($restricted_id === null) {
            $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
            $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
            $this->form_validation->set_rules('municipality_id', 'Municipality', 'required|trim|numeric');
        }

        if ($this->form_validation->run() === true) {
            $municipality_id = $restricted_id !== null ? $restricted_id : $this->input->post('municipality_id');
            if ($this->barangay_model->name_exists($this->input->post('name'), $municipality_id)) {
                $this->session->set_flashdata('error', 'A barangay with that name already exists in the selected municipality.');
            } else {
                $this->barangay_model->create([
                    'municipality_id' => $municipality_id,
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'prefix' => strtoupper(trim((string) $this->input->post('prefix'))) ?: null,
                    'description' => $this->input->post('description'),
                    'poblacion' => $this->input->post('poblacion') ? 1 : 0,
                    'total_puroks' => (int) $this->input->post('total_puroks'),
                    'day_care_centers_public' => (int) $this->input->post('day_care_centers_public'),
                    'day_care_centers_private' => (int) $this->input->post('day_care_centers_private'),
                    'elementary_schools_public' => (int) $this->input->post('elementary_schools_public'),
                    'elementary_schools_private' => (int) $this->input->post('elementary_schools_private'),
                ]);
                $this->session->set_flashdata('success', 'Barangay created successfully.');
                redirect('barangays');
            }
        }

        if ($restricted_id === null) {
            if ($this->input->post('province_id')) {
                $this->data['municipalities'] = $this->municipality_model->get_by_province($this->input->post('province_id'));
            }
            if ($this->input->post('region_id')) {
                $this->data['provinces'] = $this->province_model->get_by_region($this->input->post('region_id'));
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('barangays/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $barangay = $this->barangay_model->get_by_id($id);
        if (!$barangay) {
            show_404();
        }

        $restricted_id = $this->restricted_municipality_id;
        if ($restricted_id !== null && (int) $barangay->municipality_id !== (int) $restricted_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

        $this->data['page_title'] = 'Edit Barangay';
        $this->data['active_menu'] = 'barangays';
        $this->data['barangay'] = $barangay;
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['current_region_id'] = $province->region_id ?? null;
        $this->data['current_province_id'] = $municipality->province_id ?? null;
        $this->data['locked_municipality'] = null;

        if ($restricted_id !== null) {
            $this->data['locked_municipality'] = $municipality;
        } else {
            $this->data['regions'] = $this->region_model->get_all();
            $this->data['provinces'] = $province ? $this->province_model->get_by_region($province->region_id) : [];
            $this->data['municipalities'] = $municipality ? $this->municipality_model->get_by_province($municipality->province_id) : [];
        }

        $this->form_validation->set_rules('name', 'Barangay Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('prefix', 'Prefix', 'trim|max_length[10]');
        $this->form_validation->set_rules('description', 'Description', 'trim');
        $this->form_validation->set_rules('total_puroks', 'Total Number of Puroks', 'trim|numeric');
        $this->form_validation->set_rules('day_care_centers_public', 'Day Care Centers (Public)', 'trim|numeric');
        $this->form_validation->set_rules('day_care_centers_private', 'Day Care Centers (Private)', 'trim|numeric');
        $this->form_validation->set_rules('elementary_schools_public', 'Elementary Schools (Public)', 'trim|numeric');
        $this->form_validation->set_rules('elementary_schools_private', 'Elementary Schools (Private)', 'trim|numeric');
        if ($restricted_id === null) {
            $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
            $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
            $this->form_validation->set_rules('municipality_id', 'Municipality', 'required|trim|numeric');
        }

        if ($this->form_validation->run() === true) {
            $municipality_id = $restricted_id !== null ? $restricted_id : $this->input->post('municipality_id');
            if ($this->barangay_model->name_exists($this->input->post('name'), $municipality_id, $id)) {
                $this->session->set_flashdata('error', 'A barangay with that name already exists in the selected municipality.');
            } else {
                $this->barangay_model->update($id, [
                    'municipality_id' => $municipality_id,
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'prefix' => strtoupper(trim((string) $this->input->post('prefix'))) ?: null,
                    'description' => $this->input->post('description'),
                    'poblacion' => $this->input->post('poblacion') ? 1 : 0,
                    'total_puroks' => (int) $this->input->post('total_puroks'),
                    'day_care_centers_public' => (int) $this->input->post('day_care_centers_public'),
                    'day_care_centers_private' => (int) $this->input->post('day_care_centers_private'),
                    'elementary_schools_public' => (int) $this->input->post('elementary_schools_public'),
                    'elementary_schools_private' => (int) $this->input->post('elementary_schools_private'),
                ]);
                $this->session->set_flashdata('success', 'Barangay updated successfully.');
                redirect('barangays');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('barangays/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        $barangay = $this->barangay_model->get_by_id($id);
        if (!$barangay) {
            show_404();
        }
        if ($this->restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $this->barangay_model->archive($id);
        $this->session->set_flashdata('success', 'Barangay deleted successfully.');
        redirect('barangays');
    }

    /** JSON: list of active barangays for a given municipality (used by cascading dropdowns) */
    public function by_municipality($municipality_id)
    {
        $barangays = $this->barangay_model->get_by_municipality($municipality_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($barangays));
    }
}
