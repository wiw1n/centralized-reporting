<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Region_model extends CI_Model
{
    protected $table = 'address_region';

    public function get_all($search = '')
    {
        $this->db->where('archive', 0)->order_by('name', 'ASC');
        if ($search !== '') {
            $this->db->group_start()
                ->like('name', $search)
                ->or_like('code', $search)
                ->group_end();
        }
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function name_exists($name, $exclude_id = null)
    {
        $this->db->where('name', $name)->where('archive', 0);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, array $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function archive($id)
    {
        return $this->db->where('id', $id)->update($this->table, ['archive' => 1]);
    }

    public function has_provinces($id)
    {
        return $this->db->where('region_id', $id)->where('archive', 0)->get('address_province')->num_rows() > 0;
    }
}
