<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->render();
    }

    public function update_info()
    {
        $id = $this->current_user->id;

        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_dash|min_length[4]|max_length[50]');

        if ($this->form_validation->run() === true) {
            if ($this->user_model->username_exists($this->input->post('username'), $id)) {
                $this->session->set_flashdata('error', 'That username is already taken.');
                redirect('profile');
            }

            if ($this->user_model->email_exists($this->input->post('email'), $id)) {
                $this->session->set_flashdata('error', 'That email is already registered.');
                redirect('profile');
            }

            $first_name = $this->input->post('first_name');
            $last_name = $this->input->post('last_name');
            $username = $this->input->post('username');

            $this->user_model->update($id, [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $this->input->post('email'),
                'username' => $username,
            ]);

            $this->session->set_userdata([
                'username' => $username,
                'full_name' => $first_name . ' ' . $last_name,
            ]);

            $this->session->set_flashdata('success', 'Your profile has been updated successfully.');
            redirect('profile');
        }

        $this->render('info');
    }

    public function update_password()
    {
        $id = $this->current_user->id;
        $user = $this->user_model->get_by_id($id);

        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('password', 'New Password', 'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Confirm New Password', 'required|matches[password]');

        if ($this->form_validation->run() === true) {
            if (!password_verify($this->input->post('current_password'), $user->password)) {
                $this->session->set_flashdata('error', 'Your current password is incorrect.');
                redirect('profile#change-password');
            }

            $this->user_model->update($id, [
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            ]);

            $this->session->set_flashdata('success', 'Your password has been changed successfully.');
            redirect('profile#change-password');
        }

        $this->render('password');
    }

    private function render($active_tab = 'info')
    {
        $user = $this->user_model->get_by_id($this->current_user->id);
        if (!$user) {
            show_404();
        }

        $this->data['page_title'] = 'My Profile';
        $this->data['active_menu'] = '';
        $this->data['profile_user'] = $user;
        $this->data['active_tab'] = $active_tab;

        $this->load->view('templates/header', $this->data);
        $this->load->view('profile/edit', $this->data);
        $this->load->view('templates/footer');
    }
}
