<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin']);
        $this->load->model('user_model');
        $this->load->model('role_model');
        $this->load->model('user_area_model');
        $this->load->model('region_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->data['page_title'] = 'Users';
        $this->data['active_menu'] = 'users';

        $this->load->view('templates/header', $this->data);
        $this->load->view('users/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function datatable()
    {
        $current_id = (int) $this->current_user->id;

        $result = $this->datatable->response(
            function ($db) {
                $db->select('users.id, users.username, users.first_name, users.last_name, users.email, users.status, users.last_login, roles.label AS role_label')
                    ->from('users')
                    ->join('roles', 'roles.id = users.role_id')
                    ->where('users.archive', 0);
            },
            ['users.username', 'users.email', 'users.first_name', 'users.last_name'],
            ['users.username', null, 'users.email', 'roles.label', 'users.status', 'users.last_login', null],
            function ($row) use ($current_id) {
                $actions = '<a href="' . base_url('users/edit/' . $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a> ';
                if ((int) $row->id !== $current_id) {
                    $toggleIcon = $row->status ? 'bi-toggle-on' : 'bi-toggle-off';
                    $toggleLabel = $row->status ? 'Deactivate' : 'Activate';
                    $actions .= '<a href="' . base_url('users/toggle_status/' . $row->id) . '" class="btn btn-sm btn-outline-warning" onclick="return confirm(\'' . $toggleLabel . ' this user?\');"><i class="bi ' . $toggleIcon . '"></i></a> '
                        . '<a href="' . base_url('users/delete/' . $row->id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this user?\');"><i class="bi bi-trash"></i></a>';
                }

                return [
                    'username' => html_escape($row->username),
                    'name' => html_escape($row->first_name . ' ' . $row->last_name),
                    'email' => html_escape($row->email),
                    'role_label' => html_escape($row->role_label),
                    'status' => $row->status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>',
                    'last_login' => $row->last_login ? html_escape($row->last_login) : '<span class="text-muted">Never</span>',
                    'actions' => $actions,
                ];
            }
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function create()
    {
        $this->data['page_title'] = 'Add User';
        $this->data['active_menu'] = 'users';
        $this->data['user'] = null;
        $this->data['assigned_areas'] = [];
        $this->data['roles'] = $this->role_model->all();
        $this->data['regions'] = $this->region_model->get_all();

        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_dash|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('role_id', 'Role', 'required|trim|numeric');

        if ($this->form_validation->run() === true) {
            if ($this->user_model->username_exists($this->input->post('username'))) {
                $this->session->set_flashdata('error', 'That username is already taken.');
            } elseif ($this->user_model->email_exists($this->input->post('email'))) {
                $this->session->set_flashdata('error', 'That email is already registered.');
            } else {
                $user_id = $this->user_model->create([
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'role_id' => $this->input->post('role_id'),
                    'status' => $this->input->post('status') ? 1 : 0,
                    'created_by' => $this->current_user->id,
                ]);

                $role = $this->role_model->find($this->input->post('role_id'));
                if ($role && $role->name === 'encoder') {
                    $this->user_area_model->replace_for_user($user_id, $this->parse_assignments());
                }

                $this->session->set_flashdata('success', 'User created successfully.');
                redirect('users');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('users/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function edit($id)
    {
        $user = $this->user_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $this->data['page_title'] = 'Edit User';
        $this->data['active_menu'] = 'users';
        $this->data['user'] = $user;
        $this->data['assigned_areas'] = $this->user_area_model->get_by_user($id);
        $this->data['roles'] = $this->role_model->all();
        $this->data['regions'] = $this->region_model->get_all();

        $this->form_validation->set_rules('username', 'Username', 'required|trim|alpha_dash|min_length[4]|max_length[50]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|max_length[100]');
        $this->form_validation->set_rules('first_name', 'First Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('role_id', 'Role', 'required|trim|numeric');
        if ($this->input->post('password')) {
            $this->form_validation->set_rules('password', 'Password', 'min_length[8]');
            $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'matches[password]');
        }

        if ($this->form_validation->run() === true) {
            if ($this->user_model->username_exists($this->input->post('username'), $id)) {
                $this->session->set_flashdata('error', 'That username is already taken.');
            } elseif ($this->user_model->email_exists($this->input->post('email'), $id)) {
                $this->session->set_flashdata('error', 'That email is already registered.');
            } else {
                $update = [
                    'username' => $this->input->post('username'),
                    'email' => $this->input->post('email'),
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'role_id' => $this->input->post('role_id'),
                ];

                if ((int) $id !== (int) $this->current_user->id) {
                    $update['status'] = $this->input->post('status') ? 1 : 0;
                }

                if ($this->input->post('password')) {
                    $update['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                }

                $this->user_model->update($id, $update);

                $role = $this->role_model->find($this->input->post('role_id'));
                if ($role && $role->name === 'encoder') {
                    $this->user_area_model->replace_for_user($id, $this->parse_assignments());
                } else {
                    $this->user_area_model->delete_by_user($id);
                }

                $this->session->set_flashdata('success', 'User updated successfully.');
                redirect('users');
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('users/form', $this->data);
        $this->load->view('templates/footer');
    }

    public function delete($id)
    {
        if ((int) $id === (int) $this->current_user->id) {
            $this->session->set_flashdata('error', 'You cannot delete your own account.');
            redirect('users');
        }

        $user = $this->user_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $this->user_model->archive($id);
        $this->session->set_flashdata('success', 'User deleted successfully.');
        redirect('users');
    }

    public function toggle_status($id)
    {
        if ((int) $id === (int) $this->current_user->id) {
            $this->session->set_flashdata('error', 'You cannot deactivate your own account.');
            redirect('users');
        }

        $user = $this->user_model->get_by_id($id);
        if (!$user) {
            show_404();
        }

        $this->user_model->toggle_status($id, $user->status ? 0 : 1);
        $this->session->set_flashdata('success', 'User status updated.');
        redirect('users');
    }

    private function parse_assignments()
    {
        $raw = $this->input->post('assignments');
        if (!is_array($raw)) {
            return [];
        }

        $assignments = [];
        foreach ($raw as $row) {
            if (empty($row['scope_type']) || empty($row['area_id'])) {
                continue;
            }
            if (!in_array($row['scope_type'], ['region', 'province', 'municipality', 'barangay'], true)) {
                continue;
            }
            $assignments[] = [
                'scope_type' => $row['scope_type'],
                'area_id' => (int) $row['area_id'],
            ];
        }

        return $assignments;
    }
}
