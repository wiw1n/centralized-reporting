<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin']);
        $this->load->model('region_model');
    }

    public function index()
    {
        $this->data['page_title'] = 'System Settings';
        $this->data['active_menu'] = 'settings';
        $this->data['regions'] = $this->region_model->get_all();
        $this->data['current_active'] = $this->municipality_model->get_active();

        $this->load->view('templates/header', $this->data);
        $this->load->view('settings/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $province_id = $this->input->get('province_id');
        $region_id = $this->input->get('region_id');

        $result = $this->datatable->response(
            function ($db) use ($province_id, $region_id) {
                $db->select('address_municipality.id, address_municipality.name, address_municipality.active, address_province.name AS province_name, address_region.name AS region_name')
                    ->from('address_municipality')
                    ->join('address_province', 'address_province.id = address_municipality.province_id')
                    ->join('address_region', 'address_region.id = address_province.region_id')
                    ->where('address_municipality.archive', 0);
                if ($province_id) {
                    $db->where('address_municipality.province_id', $province_id);
                } elseif ($region_id) {
                    $db->where('address_province.region_id', $region_id);
                }
            },
            ['address_municipality.name'],
            ['address_municipality.name', 'address_province.name', 'address_region.name', 'address_municipality.active', null],
            function ($row) {
                $action = $row->active
                    ? '<span class="text-muted small">Currently active</span>'
                    : '<a href="' . base_url('settings/activate/' . $row->id) . '" class="btn btn-sm btn-primary" onclick="return confirm(\'Set this as the active municipality? All other municipalities will be deactivated.\');"><i class="bi bi-check2-circle"></i> Set Active</a>';

                return [
                    'name' => html_escape($row->name),
                    'province_name' => html_escape($row->province_name),
                    'region_name' => html_escape($row->region_name),
                    'status' => $row->active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>',
                    'actions' => $action,
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function activate($id)
    {
        $municipality = $this->municipality_model->get_by_id($id);
        if (!$municipality || $municipality->archive) {
            show_404();
        }

        $this->municipality_model->set_active_exclusive($id);
        $this->session->set_flashdata('success', 'Active municipality set to ' . $municipality->name . '.');
        redirect('settings');
    }

    public function deactivate($id)
    {
        $municipality = $this->municipality_model->get_by_id($id);
        if (!$municipality) {
            show_404();
        }

        $this->municipality_model->update($id, ['active' => 0]);
        $this->session->set_flashdata('success', 'Municipality deactivated. No municipality is currently active.');
        redirect('settings');
    }
}
