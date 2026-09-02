<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Builds and runs custom resident reports off the field registry in
 * application/config/report_fields.php. Client code only ever passes field
 * *keys* in from the request; the SQL column/expression for a key always
 * comes from the registry, never from the request, so arbitrary SQL can't
 * be injected through the field/filter selection.
 */
class Report_model extends CI_Model
{
    /** Hard cap on preview rows so a "select everything" report can't hang the page; exports are never truncated. */
    const PREVIEW_LIMIT = 500;

    protected $fields_by_key = [];

    public function __construct()
    {
        parent::__construct();
        $this->config->load('report_fields');
        foreach ($this->config->item('report_fields') as $group) {
            foreach ($group['fields'] as $key => $field) {
                $this->fields_by_key[$key] = $field;
            }
        }
    }

    /** Field registry grouped for the field-picker UI. */
    public function get_field_groups()
    {
        return $this->config->item('report_fields');
    }

    /** @return array<string,array> only the keys present in $keys that exist in the registry, in registry order */
    public function filter_valid_keys(array $keys)
    {
        return array_values(array_intersect(array_keys($this->fields_by_key), $keys));
    }

    /** @return array<string,array> field key => registry field def, for the given (validated) selected keys */
    public function columns_for(array $selected_fields)
    {
        $columns = [];
        foreach ($this->filter_valid_keys($selected_fields) as $key) {
            $columns[$key] = $this->fields_by_key[$key];
        }
        return $columns;
    }

    /**
     * @param array $selected_fields  field keys (validated against the registry)
     * @param array $filters          ['field_key' => scalar|array|['min'=>,'max'=>]|['from'=>,'to'=>]]
     * @param array $scope            ['restricted_barangay_id'=>, 'restricted_municipality_id'=>, 'region_id'=>, 'province_id'=>, 'municipality_id'=>, 'barangay_id'=>]
     */
    public function run(array $selected_fields, array $filters, array $scope)
    {
        $columns = $this->columns_for($selected_fields);
        if (empty($columns)) {
            return ['columns' => [], 'rows' => [], 'total' => 0, 'truncated' => false];
        }

        $this->base_query($filters, $scope);
        // reset=false: count_all_results() only clears ORDER BY for the COUNT(*) query and
        // otherwise leaves qb_where/qb_join/qb_from in place so we can reuse them below without
        // rebuilding the query (rebuilding would re-add the same JOINs and error as duplicate aliases).
        $total = (int) $this->db->count_all_results('', false);

        $select = implode(', ', array_map(function ($key) use ($columns) {
            return $columns[$key]['column'] . ' AS `' . $key . '`';
        }, array_keys($columns)));
        $this->db->select($select, false);
        $this->db->order_by('address_barangay.name', 'ASC')->order_by('residents.last_name', 'ASC')->order_by('residents.first_name', 'ASC');
        $this->db->limit(self::PREVIEW_LIMIT + 1);
        $rows = $this->db->get()->result_array();

        $truncated = count($rows) > self::PREVIEW_LIMIT;
        if ($truncated) {
            $rows = array_slice($rows, 0, self::PREVIEW_LIMIT);
        }

        return [
            'columns' => array_map(function ($key, $field) {
                return ['key' => $key, 'label' => $field['label'], 'type' => $field['type']];
            }, array_keys($columns), $columns),
            'rows' => $rows,
            'total' => $total,
            'truncated' => $truncated,
        ];
    }

    /**
     * Streams every matching row (no preview cap) to $callback as an assoc array, for CSV export.
     * @param array<string,array> $columns  from columns_for() -- resolved once by the caller so it
     *                                      can write the header row before any data rows arrive
     */
    public function stream_rows(array $columns, array $filters, array $scope, callable $callback)
    {
        if (empty($columns)) {
            return;
        }

        $this->base_query($filters, $scope);
        $select = implode(', ', array_map(function ($key) use ($columns) {
            return $columns[$key]['column'] . ' AS `' . $key . '`';
        }, array_keys($columns)));
        $this->db->select($select, false);
        $this->db->order_by('address_barangay.name', 'ASC')->order_by('residents.last_name', 'ASC')->order_by('residents.first_name', 'ASC');

        $query = $this->db->get();
        while ($row = $query->unbuffered_row('array')) {
            $callback($row);
        }
    }

    private function base_query(array $filters, array $scope)
    {
        $this->db->from('residents')
            ->join('address_barangay', 'address_barangay.id = residents.barangay_id')
            ->join('address_municipality', 'address_municipality.id = address_barangay.municipality_id')
            ->join('address_province', 'address_province.id = address_municipality.province_id')
            ->join('address_region', 'address_region.id = address_province.region_id')
            // Per-section 1:1 child tables (see application/config/report_fields.php).
            // Always left-joined: 1:1 on resident_id, negligible cost, and it keeps
            // the field registry free of per-field join bookkeeping.
            ->join('resident_personal', 'resident_personal.resident_id = residents.id', 'left')
            ->join('resident_contact', 'resident_contact.resident_id = residents.id', 'left')
            ->join('resident_work_education', 'resident_work_education.resident_id = residents.id', 'left')
            ->join('resident_government_ids', 'resident_government_ids.resident_id = residents.id', 'left')
            ->join('resident_program_flags', 'resident_program_flags.resident_id = residents.id', 'left')
            ->join('resident_remarks', 'resident_remarks.resident_id = residents.id', 'left')
            ->where('residents.archive', 0);

        $this->apply_scope($scope);
        $this->apply_filters($filters);
    }

    private function apply_scope(array $scope)
    {
        $restricted_barangay_id = $scope['restricted_barangay_id'] ?? null;
        $restricted_municipality_id = $scope['restricted_municipality_id'] ?? null;

        if ($restricted_barangay_id !== null) {
            $this->db->where('residents.barangay_id', $restricted_barangay_id);
            return;
        }

        if ($restricted_municipality_id !== null) {
            $this->db->where('address_barangay.municipality_id', $restricted_municipality_id);
            if (!empty($scope['barangay_id'])) {
                $this->db->where('residents.barangay_id', $scope['barangay_id']);
            }
            return;
        }

        if (!empty($scope['barangay_id'])) {
            $this->db->where('residents.barangay_id', $scope['barangay_id']);
        } elseif (!empty($scope['municipality_id'])) {
            $this->db->where('address_barangay.municipality_id', $scope['municipality_id']);
        } elseif (!empty($scope['province_id'])) {
            $this->db->where('address_municipality.province_id', $scope['province_id']);
        } elseif (!empty($scope['region_id'])) {
            $this->db->where('address_province.region_id', $scope['region_id']);
        }
    }

    private function apply_filters(array $filters)
    {
        foreach ($this->filter_valid_keys(array_keys($filters)) as $key) {
            $field = $this->fields_by_key[$key];
            if (empty($field['filterable'])) {
                continue;
            }
            $value = $filters[$key];
            $column = $field['column'];

            switch ($field['type']) {
                case 'boolean':
                    if (!empty($value)) {
                        if ($column === 'resident_program_flags.is_4ps_beneficiary') {
                            // Stored as a nullable varchar (the 4Ps ID Number);
                            // any non-NULL value marks the resident a beneficiary.
                            $this->db->where("$column IS NOT NULL", null, false);
                        } else {
                            $this->db->where($column, 1);
                        }
                    }
                    break;

                case 'enum':
                    $values = array_filter((array) $value, function ($v) use ($field) {
                        return $v !== '' && in_array($v, $field['options'], true);
                    });
                    if (!empty($values)) {
                        $this->db->where_in($column, array_values($values));
                    }
                    break;

                case 'number':
                    if (is_array($value)) {
                        if (isset($value['min']) && $value['min'] !== '') {
                            $this->db->where($column . ' >=', (float) $value['min']);
                        }
                        if (isset($value['max']) && $value['max'] !== '') {
                            $this->db->where($column . ' <=', (float) $value['max']);
                        }
                    }
                    break;

                case 'date':
                    if (is_array($value)) {
                        if (!empty($value['from'])) {
                            $this->db->where($column . ' >=', $value['from']);
                        }
                        if (!empty($value['to'])) {
                            $this->db->where($column . ' <=', $value['to']);
                        }
                    }
                    break;

                case 'text':
                    $needle = is_array($value) ? ($value['contains'] ?? '') : $value;
                    if (trim((string) $needle) !== '') {
                        $this->db->like($column, trim($needle));
                    }
                    break;
            }
        }
    }
}
