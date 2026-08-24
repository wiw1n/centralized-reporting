<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Barangay_model extends CI_Model
{
    protected $table = 'address_barangay';

    public function get_all($search = '', $municipality_id = null)
    {
        $this->db->select('address_barangay.*, address_municipality.name AS municipality_name, address_province.name AS province_name, address_region.name AS region_name')
            ->from($this->table)
            ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id')
            ->join('address_province', 'address_province.id = address_municipality.province_id')
            ->join('address_region', 'address_region.id = address_province.region_id')
            ->where('address_barangay.archive', 0)
            ->order_by('address_barangay.name', 'ASC');

        if ($municipality_id) {
            $this->db->where('address_barangay.municipality_id', $municipality_id);
        }
        if ($search !== '') {
            $this->db->group_start()
                ->like('address_barangay.name', $search)
                ->or_like('address_barangay.code', $search)
                ->group_end();
        }
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function get_by_municipality($municipality_id)
    {
        return $this->db->where('municipality_id', $municipality_id)->where('archive', 0)->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function name_exists($name, $municipality_id, $exclude_id = null)
    {
        $this->db->where('name', $name)->where('municipality_id', $municipality_id)->where('archive', 0);
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
}
