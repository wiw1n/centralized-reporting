<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_area_model extends CI_Model
{
    protected $table = 'user_area_assignments';

    public function get_by_user($user_id)
    {
        // Each row only stores the FK for its own scope level, so the
        // ancestor joins walk up the hierarchy via COALESCE: e.g. a
        // barangay-scoped row has no municipality_id of its own, so the
        // municipality join falls back to the barangay's municipality_id.
        $this->db->select('user_area_assignments.*,
                r.name AS region_name,
                p.name AS province_name,
                m.name AS municipality_name,
                b.name AS barangay_name')
            ->from($this->table)
            ->join('address_barangay b', 'b.id = user_area_assignments.barangay_id', 'left')
            ->join('address_municipality m', 'm.id = COALESCE(user_area_assignments.municipality_id, b.municipality_id)', 'left')
            ->join('address_province p', 'p.id = COALESCE(user_area_assignments.province_id, m.province_id)', 'left')
            ->join('address_region r', 'r.id = COALESCE(user_area_assignments.region_id, p.region_id)', 'left')
            ->where('user_area_assignments.user_id', $user_id)
            ->order_by('user_area_assignments.id', 'ASC');

        $rows = $this->db->get()->result();

        foreach ($rows as $row) {
            switch ($row->scope_type) {
                case 'region':
                    $row->area_label = $row->region_name;
                    break;
                case 'province':
                    $row->area_label = $row->province_name . ', ' . $row->region_name;
                    break;
                case 'municipality':
                    $row->area_label = $row->municipality_name . ', ' . $row->province_name;
                    break;
                case 'barangay':
                    $row->area_label = 'Brgy. ' . $row->barangay_name . ', ' . $row->municipality_name;
                    break;
                default:
                    $row->area_label = '';
            }
        }

        return $rows;
    }

    public function replace_for_user($user_id, array $assignments)
    {
        $this->db->where('user_id', $user_id)->delete($this->table);

        if (empty($assignments)) {
            return;
        }

        $rows = [];
        foreach ($assignments as $assignment) {
            $rows[] = [
                'user_id' => $user_id,
                'scope_type' => $assignment['scope_type'],
                'region_id' => $assignment['scope_type'] === 'region' ? $assignment['area_id'] : null,
                'province_id' => $assignment['scope_type'] === 'province' ? $assignment['area_id'] : null,
                'municipality_id' => $assignment['scope_type'] === 'municipality' ? $assignment['area_id'] : null,
                'barangay_id' => $assignment['scope_type'] === 'barangay' ? $assignment['area_id'] : null,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->insert_batch($this->table, $rows);
    }

    public function delete_by_user($user_id)
    {
        return $this->db->where('user_id', $user_id)->delete($this->table);
    }
}
