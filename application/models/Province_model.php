<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Province_model extends CI_Model
{
    protected $table = 'address_province';

    public function get_all($search = '', $region_id = null)
    {
        $this->db->select('address_province.*, address_region.name AS region_name')
            ->from($this->table)
            ->join('address_region', 'address_region.id = address_province.region_id')
            ->where('address_province.archive', 0)
            ->order_by('address_province.name', 'ASC');

        if ($region_id) {
            $this->db->where('address_province.region_id', $region_id);
        }
        if ($search !== '') {
            $this->db->group_start()
                ->like('address_province.name', $search)
                ->or_like('address_province.code', $search)
                ->group_end();
        }
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_by_region($region_id)
    {
        return $this->db->where('region_id', $region_id)->where('archive', 0)->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function name_exists($name, $region_id, $exclude_id = null)
    {
        $this->db->where('name', $name)->where('region_id', $region_id)->where('archive', 0);
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

    public function has_municipalities($id)
    {
        return $this->db->where('province_id', $id)->where('archive', 0)->get('address_municipality')->num_rows() > 0;
    }
}
