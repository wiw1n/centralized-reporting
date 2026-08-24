<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(['authentication', 'form_validation']);
    }

    public function login()
    {
        if ($this->authentication->is_logged_in()) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === true) {
            $result = $this->authentication->attempt(
                $this->input->post('username'),
                $this->input->post('password')
            );

            if ($result === true) {
                redirect('dashboard');
            } elseif ($result === 'inactive') {
                $this->session->set_flashdata('error', 'Your account has been deactivated. Please contact an administrator.');
            } else {
                $this->session->set_flashdata('error', 'Invalid username or password.');
            }

            redirect('auth/login');
        }

        $this->load->view('auth/login');
    }

    public function logout()
    {
        $this->authentication->logout();
        redirect('auth/login');
    }
}
