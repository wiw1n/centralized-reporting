<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Minimal jQuery DataTables server-side processing helper built on CI3's
 * query builder. $base_query is invoked fresh for each of the three
 * internal queries (total count / filtered count / page of data) because
 * CI3's query builder resets its state after every terminal call
 * (get(), count_all_results()) -- that's what lets us reuse $this->db
 * instead of cloning it.
 */
class Datatable
{
    protected $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    /**
     * @param callable $base_query      function(CI_DB_query_builder $db): void - applies select/from/join/base-where
     * @param array    $searchable_cols fully-qualified column names eligible for the global search box
     * @param array    $order_cols      fully-qualified column names, indexed to match the client-side `columns` order
     * @param callable $row_formatter   function($row): array
     */
    public function response(callable $base_query, array $searchable_cols, array $order_cols, callable $row_formatter)
    {
        $input = $this->ci->input;
        $db = $this->ci->db;

        $draw = (int) $input->get('draw');
        $start = (int) $input->get('start');
        $length = (int) $input->get('length');
        $search = $input->get('search');
        $searchValue = is_array($search) ? trim((string) $search['value']) : '';
        $order = $input->get('order');
        $orderColIndex = (is_array($order) && isset($order[0]['column'])) ? (int) $order[0]['column'] : 0;
        $orderDir = (is_array($order) && isset($order[0]['dir']) && strtolower($order[0]['dir']) === 'desc') ? 'DESC' : 'ASC';

        $base_query($db);
        $recordsTotal = (int) $db->count_all_results();

        $base_query($db);
        $this->apply_search($db, $searchable_cols, $searchValue);
        $recordsFiltered = (int) $db->count_all_results();

        $base_query($db);
        $this->apply_search($db, $searchable_cols, $searchValue);
        if (isset($order_cols[$orderColIndex]) && $order_cols[$orderColIndex] !== null) {
            $db->order_by($order_cols[$orderColIndex], $orderDir);
        }
        if ($length > 0) {
            $db->limit($length, $start);
        }
        $rows = $db->get()->result();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => array_map($row_formatter, $rows),
        ];
    }

    protected function apply_search($db, array $columns, $value)
    {
        if ($value === '' || empty($columns)) {
            return;
        }
        $db->group_start();
        foreach ($columns as $i => $col) {
            if ($i === 0) {
                $db->like($col, $value);
            } else {
                $db->or_like($col, $value);
            }
        }
        $db->group_end();
    }
}
