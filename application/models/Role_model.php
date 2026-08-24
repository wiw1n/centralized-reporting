<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model
{
    protected $table = 'roles';

    public function all()
    {
        return $this->db->order_by('id', 'ASC')->get($this->table)->result();
    }

    public function find($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function find_by_name($name)
    {
        return $this->db->where('name', $name)->get($this->table)->row();
    }
}
