<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function index()
    {
        $this->data['page_title'] = 'Dashboard';
        $this->data['active_menu'] = 'dashboard';

        if ($this->authentication->has_role('encoder')) {
            $this->data['assigned_areas'] = $this->authentication->assigned_areas();
        }

        if ($this->restricted_municipality_id) {
            $this->load->model('barangay_model');
            $this->data['municipality_barangay_count'] = count($this->barangay_model->get_by_municipality($this->restricted_municipality_id));
        }

        if ($this->active_municipality) {
            $this->load->model('resident_model');
            $this->data['town_summary'] = $this->resident_model->get_town_summary($this->active_municipality->id);
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('dashboard/index', $this->data);
        $this->load->view('templates/footer');
    }
}
