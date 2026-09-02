<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Municipalities extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin']);
        $this->load->model('municipality_model');
        $this->load->model('province_model');
        $this->load->model('region_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['page_title'] = 'Municipalities';
        $this->data['active_menu'] = 'municipalities';
        $this->data['regions'] = $this->region_model->get_all();

        $this->load->view('templates/header', $this->data);
        $this->load->view('municipalities/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $province_id = $this->input->get('province_id');
        $region_id = $this->input->get('region_id');
        $restricted_id = $this->restricted_municipality_id;

        $result = $this->datatable->response(
            function ($db) use ($province_id, $region_id, $restricted_id) {
                $db->select('address_municipality.id, address_municipality.name, address_municipality.code, address_municipality.prefix, address_province.name AS province_name, address_region.name AS region_name')
                    ->from('address_municipality')
                    ->join('address_province', 'address_province.id = address_municipality.province_id')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('address_municipality.archive', 0);
                if ($restricted_id !== null) {
                    $db->where('address_municipality.id', $restricted_id);
                } elseif ($province_id) {
                    $db->where('address_municipality.province_id', $province_id);
                } elseif ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['address_municipality.name', 'address_municipality.code', 'address_municipality.prefix'],
            ['address_municipality.name', 'address_municipality.prefix', 'address_province.name', 'address_region.name', null],
            function ($row) use ($restricted_id) {
                $actions = '<a href="' . base_url('municipalities/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> ';
                if ($restricted_id === null) {
                    $actions .= '<a href="' . base_url('municipalities/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this municipality?\');"><i class="bi bi-trash"></i></a>';
                }
                return [
                    'name' => html_escape($row->name),
                    'prefix' => html_escape($row->prefix ?? ''),
                    'province_name' => html_escape($row->province_name),
                    'region_name' => html_escape($row->region_name),
                    'actions' => $actions,
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        if ($this->restricted_municipality_id !== null) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $this->data['page_title'] = 'Add Municipality';
        $this->data['active_menu'] = 'municipalities';
        $this->data['municipality'] = null;
        $this->data['regions'] = $this->region_model->get_all();
        $this->data['provinces'] = [];

        $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
        $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
        $this->form_validation->set_rules('name', 'Municipality/City Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('prefix', 'Prefix', 'trim|max_length[10]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->municipality_model->name_exists($this->input->post('name'), $this->input->post('province_id'))) {
                $this->session->set_flashdata('error', 'A municipality with that name already exists in the selected province.');
            } else {
                $this->municipality_model->create([
                    'province_id' => $this->input->post('province_id'),
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'prefix' => strtoupper(trim((string) $this->input->post('prefix'))) ?: null,
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Municipality created successfully.');
                redirect('municipalities');
            }
        }

        if ($this->input->post('region_id')) {
            $this->data['provinces'] = $this->province_model->get_by_region($this->input->post('region_id'));
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('municipalities/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $municipality = $this->municipality_model->get_by_id($id);
        if (!$municipality) {
            show_404();
        }
        if ($this->restricted_municipality_id !== null && (int) $id !== (int) $this->restricted_municipality_id) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $province = $this->province_model->get_by_id($municipality->province_id);

        $this->data['page_title'] = 'Edit Municipality';
        $this->data['active_menu'] = 'municipalities';
        $this->data['municipality'] = $municipality;
        $this->data['regions'] = $this->region_model->get_all();
        $this->data['current_region_id'] = $province->region_id ?? null;
        $this->data['provinces'] = $province ? $this->province_model->get_by_region($province->region_id) : [];

        $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
        $this->form_validation->set_rules('province_id', 'Province', 'required|trim|numeric');
        $this->form_validation->set_rules('name', 'Municipality/City Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('prefix', 'Prefix', 'trim|max_length[10]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->municipality_model->name_exists($this->input->post('name'), $this->input->post('province_id'), $id)) {
                $this->session->set_flashdata('error', 'A municipality with that name already exists in the selected province.');
            } else {
                $this->municipality_model->update($id, [
                    'province_id' => $this->input->post('province_id'),
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'prefix' => strtoupper(trim((string) $this->input->post('prefix'))) ?: null,
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Municipality updated successfully.');
                redirect('municipalities');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('municipalities/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        if ($this->restricted_municipality_id !== null) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }

        $municipality = $this->municipality_model->get_by_id($id);
        if (!$municipality) {
            show_404();
        }

        if ($this->municipality_model->has_barangays($id)) {
            $this->session->set_flashdata('error', 'Cannot delete a municipality that still has barangays under it.');
        } else {
            $this->municipality_model->archive($id);
            $this->session->set_flashdata('success', 'Municipality deleted successfully.');
        }

        redirect('municipalities');
    }

    /** JSON: list of active municipalities for a given province (used by cascading dropdowns) */
    public function by_province($province_id)
    {
        $municipalities = $this->municipality_model->get_by_province($province_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($municipalities));
    }
}
