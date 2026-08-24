<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    /** null = unrestricted (super_admin/admin); int = locked to this barangay; 0 = encoder has no barangay assigned */
    protected $restricted_barangay_id;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin', 'encoder']);
        $this->load->model('report_model');
        $this->load->model('report_template_model');
        $this->load->model('barangay_model');
        $this->load->model('municipality_model');
        $this->load->model('province_model');
        $this->load->model('region_model');

        $this->restricted_barangay_id = null;
        if ($this->current_user->role_name === 'encoder') {
            $barangay_assignment = null;
            foreach ($this->authentication->assigned_areas() as $area) {
                if ($area->scope_type === 'barangay') {
                    $barangay_assignment = $area;
                    break;
                }
            }
            $this->restricted_barangay_id = $barangay_assignment->barangay_id ?? 0;
        }
        $this->data['restricted_barangay_id'] = $this->restricted_barangay_id;
    }

    public function index()
    {
        $this->data['page_title'] = 'Reports';
        $this->data['active_menu'] = 'reports';
        $this->data['field_groups'] = $this->report_model->get_field_groups();
        $this->data['templates'] = $this->report_template_model->get_by_user($this->current_user->id);
        $this->data['regions'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_barangay'] = null;

        if ($this->restricted_barangay_id !== null) {
            $this->data['locked_barangay'] = $this->restricted_barangay_id
                ? $this->barangay_model->get_by_id($this->restricted_barangay_id)
                : null;
        } elseif ($this->restricted_municipality_id !== null) {
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($this->restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('reports/index', $this->data);
        $this->load->view('templates/footer');
    }

    public function preview()
    {
        [$selected_fields, $filters, $scope] = $this->collect_report_params();
        $result = $this->report_model->run($selected_fields, $filters, $scope);

        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }

    public function export_csv()
    {
        [$selected_fields, $filters, $scope] = $this->collect_report_params();
        $columns = $this->report_model->columns_for($selected_fields);

        if (empty($columns)) {
            $this->session->set_flashdata('error', 'Select at least one field before exporting.');
            redirect('reports');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="report_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, array_map(function ($field) {
            return $field['label'];
        }, $columns));

        $this->report_model->stream_rows($columns, $filters, $scope, function ($row) use ($out) {
            fputcsv($out, $row);
        });

        fclose($out);
        exit;
    }

    public function save_template()
    {
        $name = trim((string) $this->input->post('name'));
        [$selected_fields, $filters] = $this->collect_report_params(false);

        if ($name === '' || empty($selected_fields)) {
            $this->output->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Give the report a name and select at least one field.']));
            return;
        }

        $id = $this->report_template_model->create($this->current_user->id, $name, $selected_fields, $filters);
        $this->output->set_content_type('application/json')->set_output(json_encode(['id' => $id, 'name' => $name]));
    }

    public function load_template($id)
    {
        $template = $this->report_template_model->get_by_id($id);
        if (!$template || (int) $template->user_id !== (int) $this->current_user->id) {
            $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['error' => 'Not found.']));
            return;
        }

        $this->output->set_content_type('application/json')->set_output(json_encode([
            'fields' => json_decode($template->fields, true) ?: [],
            'filters' => json_decode($template->filters, true) ?: [],
        ]));
    }

    public function delete_template($id)
    {
        $this->report_template_model->delete($id, $this->current_user->id);
        $this->session->set_flashdata('success', 'Report template deleted.');
        redirect('reports');
    }

    /**
     * Reads fields[]/filters[]/location scope from the POST body (both the AJAX preview and the
     * plain form-submit export send the report builder form the same way).
     * @return array [selected_fields, filters, scope] or [selected_fields, filters] when $with_scope is false
     */
    private function collect_report_params($with_scope = true)
    {
        $selected_fields = (array) $this->input->post('fields');
        $filters = (array) $this->input->post('filters');

        if (!$with_scope) {
            return [$selected_fields, $filters];
        }

        $scope = [
            'restricted_barangay_id' => $this->restricted_barangay_id,
            'restricted_municipality_id' => $this->restricted_municipality_id,
            'region_id' => $this->input->post('region_id'),
            'province_id' => $this->input->post('province_id'),
            'municipality_id' => $this->input->post('municipality_id'),
            'barangay_id' => $this->input->post('barangay_id'),
        ];

        return [$selected_fields, $filters, $scope];
    }
}
