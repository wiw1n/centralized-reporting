<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Municipality_model extends CI_Model
{
    protected $table = 'address_municipality';

    public function get_all($search = '', $province_id = null)
    {
        $this->db->select('address_municipality.*, address_province.name AS province_name, address_region.name AS region_name')
            ->from($this->table)
            ->join('address_province', 'address_province.id = address_municipality.province_id')
            ->join('address_region', 'address_region.id = address_province.region_id')
            ->where('address_municipality.archive', 0)
            ->order_by('address_municipality.name', 'ASC');

        if ($province_id) {
            $this->db->where('address_municipality.province_id', $province_id);
        }
        if ($search !== '') {
            $this->db->group_start()
                ->like('address_municipality.name', $search)
                ->or_like('address_municipality.code', $search)
                ->group_end();
        }
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    /** The single municipality currently flagged active, or null if none is set. */
    public function get_active()
    {
        return $this->db->select('address_municipality.*, address_province.name AS province_name, address_region.name AS region_name')
            ->from($this->table)
            ->join('address_province', 'address_province.id = address_municipality.province_id')
            ->join('address_region', 'address_region.id = address_province.region_id')
            ->where('address_municipality.active', 1)
            ->where('address_municipality.archive', 0)
            ->get()->row();
    }

    /** Marks $id as the only active municipality, deactivating all others. */
    public function set_active_exclusive($id)
    {
        $this->db->trans_start();
        $this->db->update($this->table, ['active' => 0]);
        $this->db->where('id', $id)->update($this->table, ['active' => 1]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_by_province($province_id)
    {
        return $this->db->where('province_id', $province_id)->where('archive', 0)->order_by('name', 'ASC')->get($this->table)->result();
    }

    public function name_exists($name, $province_id, $exclude_id = null)
    {
        $this->db->where('name', $name)->where('province_id', $province_id)->where('archive', 0);
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

    public function has_barangays($id)
    {
        return $this->db->where('municipality_id', $id)->where('archive', 0)->get('address_barangay')->num_rows() > 0;
    }
}
