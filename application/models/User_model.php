<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model
{
    protected $table = 'users';

    public function get_all($search = '')
    {
        $this->db->select('users.*, roles.name AS role_name, roles.label AS role_label')
            ->from($this->table)
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.archive', 0)
            ->order_by('users.id', 'DESC');

        if ($search !== '') {
            $this->db->group_start()
                ->like('users.username', $search)
                ->or_like('users.email', $search)
                ->or_like('users.first_name', $search)
                ->or_like('users.last_name', $search)
                ->group_end();
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->select('users.*, roles.name AS role_name, roles.label AS role_label')
            ->from($this->table)
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->get()
            ->row();
    }

    public function get_by_username($username)
    {
        return $this->db->select('users.*, roles.name AS role_name, roles.label AS role_label')
            ->from($this->table)
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.username', $username)
            ->where('users.archive', 0)
            ->get()
            ->row();
    }

    public function username_exists($username, $exclude_id = null)
    {
        $this->db->where('username', $username);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function email_exists($email, $exclude_id = null)
    {
        $this->db->where('email', $email);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function create(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function archive($id)
    {
        return $this->db->where('id', $id)->update($this->table, [
            'archive' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function toggle_status($id, $status)
    {
        return $this->db->where('id', $id)->update($this->table, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function touch_last_login($id)
    {
        return $this->db->where('id', $id)->update($this->table, [
            'last_login' => date('Y-m-d H:i:s'),
        ]);
    }
}
