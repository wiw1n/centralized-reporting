<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Provinces extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin']);
        $this->load->model('province_model');
        $this->load->model('region_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['page_title'] = 'Provinces';
        $this->data['active_menu'] = 'provinces';
        $this->data['regions'] = $this->region_model->get_all();

        $this->load->view('templates/header', $this->data);
        $this->load->view('provinces/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $region_id = $this->input->get('region_id');

        $result = $this->datatable->response(
            function ($db) use ($region_id) {
                $db->select('address_province.id, address_province.name, address_province.code, address_region.name AS region_name')
                    ->from('address_province')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('address_province.archive', 0);
                if ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['address_province.name', 'address_province.code'],
            ['address_province.name', 'address_region.name', 'address_province.code', null],
            function ($row) {
                return [
                    'name' => html_escape($row->name),
                    'region_name' => html_escape($row->region_name),
                    'code' => html_escape($row->code),
                    'actions' => '<a href="' . base_url('provinces/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> '
                        . '<a href="' . base_url('provinces/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this province?\');"><i class="bi bi-trash"></i></a>',
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        $this->data['page_title'] = 'Add Province';
        $this->data['active_menu'] = 'provinces';
        $this->data['province'] = null;
        $this->data['regions'] = $this->region_model->get_all();

        $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
        $this->form_validation->set_rules('name', 'Province Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->province_model->name_exists($this->input->post('name'), $this->input->post('region_id'))) {
                $this->session->set_flashdata('error', 'A province with that name already exists in the selected region.');
            } else {
                $this->province_model->create([
                    'region_id' => $this->input->post('region_id'),
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Province created successfully.');
                redirect('provinces');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('provinces/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $province = $this->province_model->get_by_id($id);
        if (!$province) {
            show_404();
        }

        $this->data['page_title'] = 'Edit Province';
        $this->data['active_menu'] = 'provinces';
        $this->data['province'] = $province;
        $this->data['regions'] = $this->region_model->get_all();

        $this->form_validation->set_rules('region_id', 'Region', 'required|trim|numeric');
        $this->form_validation->set_rules('name', 'Province Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->province_model->name_exists($this->input->post('name'), $this->input->post('region_id'), $id)) {
                $this->session->set_flashdata('error', 'A province with that name already exists in the selected region.');
            } else {
                $this->province_model->update($id, [
                    'region_id' => $this->input->post('region_id'),
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Province updated successfully.');
                redirect('provinces');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('provinces/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        $province = $this->province_model->get_by_id($id);
        if (!$province) {
            show_404();
        }

        if ($this->province_model->has_municipalities($id)) {
            $this->session->set_flashdata('error', 'Cannot delete a province that still has municipalities under it.');
        } else {
            $this->province_model->archive($id);
            $this->session->set_flashdata('success', 'Province deleted successfully.');
        }

        redirect('provinces');
    }

    /** JSON: list of active provinces for a given region (used by cascading dropdowns) */
    public function by_region($region_id)
    {
        $provinces = $this->province_model->get_by_region($region_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($provinces));
    }
}
