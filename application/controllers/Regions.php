<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Regions extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin']);
        $this->load->model('region_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['page_title'] = 'Regions';
        $this->data['active_menu'] = 'regions';

        $this->load->view('templates/header', $this->data);
        $this->load->view('regions/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $result = $this->datatable->response(
            function ($db) {
                $db->select('id, name, code, description')
                    ->from('address_region')
                    ->where('archive', 0);
            },
            ['name', 'code'],
            ['name', 'code', 'description', null],
            function ($row) {
                return [
                    'name' => html_escape($row->name),
                    'code' => html_escape($row->code),
                    'description' => html_escape($row->description),
                    'actions' => '<a href="' . base_url('regions/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> '
                        . '<a href="' . base_url('regions/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this region?\');"><i class="bi bi-trash"></i></a>',
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        $this->data['page_title'] = 'Add Region';
        $this->data['active_menu'] = 'regions';
        $this->data['region'] = null;

        $this->form_validation->set_rules('name', 'Region Name', 'required|trim|max_length[45]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->region_model->name_exists($this->input->post('name'))) {
                $this->session->set_flashdata('error', 'A region with that name already exists.');
            } else {
                $this->region_model->create([
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Region created successfully.');
                redirect('regions');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('regions/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $region = $this->region_model->get_by_id($id);
        if (!$region) {
            show_404();
        }

        $this->data['page_title'] = 'Edit Region';
        $this->data['active_menu'] = 'regions';
        $this->data['region'] = $region;

        $this->form_validation->set_rules('name', 'Region Name', 'required|trim|max_length[45]');
        $this->form_validation->set_rules('code', 'Code', 'trim|max_length[45]');
        $this->form_validation->set_rules('description', 'Description', 'trim');

        if ($this->form_validation->run() === true) {
            if ($this->region_model->name_exists($this->input->post('name'), $id)) {
                $this->session->set_flashdata('error', 'A region with that name already exists.');
            } else {
                $this->region_model->update($id, [
                    'name' => $this->input->post('name'),
                    'code' => $this->input->post('code'),
                    'description' => $this->input->post('description'),
                ]);
                $this->session->set_flashdata('success', 'Region updated successfully.');
                redirect('regions');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('regions/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        $region = $this->region_model->get_by_id($id);
        if (!$region) {
            show_404();
        }

        if ($this->region_model->has_provinces($id)) {
            $this->session->set_flashdata('error', 'Cannot delete a region that still has provinces under it.');
        } else {
            $this->region_model->archive($id);
            $this->session->set_flashdata('success', 'Region deleted successfully.');
        }

        redirect('regions');
    }
}
