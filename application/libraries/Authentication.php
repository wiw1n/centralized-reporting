<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Session-based authentication + role/scope helper.
 */
class Authentication
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('user_model');
        $this->ci->load->model('user_area_model');
    }

    public function attempt($username, $password)
    {
        $user = $this->ci->user_model->get_by_username($username);

        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }

        if ((int) $user->status !== 1) {
            return 'inactive';
        }

        $this->ci->session->set_userdata([
            'user_id' => $user->id,
            'username' => $user->username,
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'role_label' => $user->role_label,
            'logged_in' => true,
        ]);

        $this->ci->user_model->touch_last_login($user->id);

        return true;
    }

    public function logout()
    {
        $this->ci->session->sess_destroy();
    }

    public function is_logged_in()
    {
        return (bool) $this->ci->session->userdata('logged_in');
    }

    public function user()
    {
        if (!$this->is_logged_in()) {
            return null;
        }

        return (object) [
            'id' => $this->ci->session->userdata('user_id'),
            'username' => $this->ci->session->userdata('username'),
            'full_name' => $this->ci->session->userdata('full_name'),
            'role_id' => $this->ci->session->userdata('role_id'),
            'role_name' => $this->ci->session->userdata('role_name'),
            'role_label' => $this->ci->session->userdata('role_label'),
        ];
    }

    public function has_role($roles)
    {
        $current = $this->ci->session->userdata('role_name');
        return in_array($current, (array) $roles, true);
    }

    public function assigned_areas()
    {
        $user = $this->user();
        if (!$user) {
            return [];
        }
        return $this->ci->user_area_model->get_by_user($user->id);
    }
}
