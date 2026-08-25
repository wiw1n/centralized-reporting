<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Health module reports -- standalone, barangay-scoped reports separate from the generic Reports builder. */
class Health extends MY_Controller
{
    /** null = unrestricted (super_admin/admin); int = locked to this barangay; 0 = encoder has no barangay assigned */
    protected $restricted_barangay_id;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin', 'encoder']);
        $this->load->model('resident_household_model');
        $this->load->model('resident_data_survey_model');
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

    /** BHW Family Profiling Form -- household roster with health/pregnancy fields, for a barangay or a whole municipality. */
    public function household_report()
    {
        $this->data['page_title'] = 'Household Report';
        $this->data['active_menu'] = 'health';
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_barangay'] = null;
        $this->data['selected_region_id'] = null;
        $this->data['selected_province_id'] = null;
        $this->data['selected_municipality_id'] = null;
        $this->data['barangay'] = null;
        $this->data['municipality'] = null;
        $this->data['province'] = null;
        $this->data['households'] = [];
        $this->data['show_barangay_column'] = false;

        if ($this->restricted_barangay_id !== null) {
            $barangay_id = $this->restricted_barangay_id ?: null;
            $this->data['locked_barangay'] = $barangay_id ? $this->barangay_model->get_by_id($barangay_id) : null;

            if ($barangay_id) {
                $this->load_barangay_scope($barangay_id);
            }

            $this->render_household_report();
            return;
        }

        if ($this->restricted_municipality_id !== null) {
            $municipality_id = $this->restricted_municipality_id ?: null;
        } else {
            $this->data['regions'] = $this->region_model->get_all();

            $municipality_id_param = $this->input->get('municipality_id');
            $municipality_id = $municipality_id_param !== null
                ? ($municipality_id_param ?: null)
                : ($this->active_municipality->id ?? null);
        }

        if ($municipality_id) {
            $municipality = $this->municipality_model->get_by_id($municipality_id);
            if ($municipality) {
                if ($this->restricted_municipality_id !== null && (int) $municipality->id !== (int) $this->restricted_municipality_id) {
                    show_error('You are not authorized to access this page.', 403, 'Access Denied');
                }

                $province = $this->province_model->get_by_id($municipality->province_id);

                $this->data['municipality'] = $municipality;
                $this->data['province'] = $province;
                $this->data['barangays'] = $this->barangay_model->get_by_municipality($municipality_id);

                if ($this->restricted_municipality_id === null && $province) {
                    $this->data['selected_region_id'] = $province->region_id;
                    $this->data['provinces'] = $this->province_model->get_by_region($province->region_id);
                    $this->data['selected_province_id'] = $municipality->province_id;
                    $this->data['municipalities'] = $this->municipality_model->get_by_province($municipality->province_id);
                    $this->data['selected_municipality_id'] = $municipality_id;
                }

                $barangay_id = $this->input->get('barangay_id');
                if ($barangay_id) {
                    $barangay = $this->barangay_model->get_by_id($barangay_id);
                    if (!$barangay || (int) $barangay->municipality_id !== (int) $municipality_id) {
                        show_error('You are not authorized to access this page.', 403, 'Access Denied');
                    }
                    $this->data['barangay'] = $barangay;
                    $this->data['households'] = $this->resident_household_model->get_household_roster_by_barangay($barangay_id);
                } else {
                    $this->data['show_barangay_column'] = true;
                    $this->data['households'] = $this->resident_household_model->get_household_roster_by_municipality($municipality_id);
                }
            }
        }

        $this->render_household_report();
    }

    private function load_barangay_scope($barangay_id)
    {
        $barangay = $this->barangay_model->get_by_id($barangay_id);
        if (!$barangay) {
            return;
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

        $this->data['barangay'] = $barangay;
        $this->data['municipality'] = $municipality;
        $this->data['province'] = $province;
        $this->data['households'] = $this->resident_household_model->get_household_roster_by_barangay($barangay_id);
    }

    private function render_household_report()
    {
        $this->load->view('templates/header', $this->data);
        $this->load->view('health/household_report', $this->data);
        $this->load->view('templates/footer');
    }

    /** Data Survey Tool -- immunization, COVID vaccine, Schisto MDA, food intake, exercise, and recreational status per resident. */
    public function data_survey_report()
    {
        $this->data['page_title'] = 'Data Survey Tool';
        $this->data['active_menu'] = 'health';
        $this->data['regions'] = [];
        $this->data['provinces'] = [];
        $this->data['municipalities'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_barangay'] = null;
        $this->data['selected_region_id'] = null;
        $this->data['selected_province_id'] = null;
        $this->data['selected_municipality_id'] = null;
        $this->data['barangay'] = null;
        $this->data['municipality'] = null;
        $this->data['province'] = null;
        $this->data['survey_rows'] = [];
        $this->data['show_barangay_column'] = false;

        if ($this->restricted_barangay_id !== null) {
            $barangay_id = $this->restricted_barangay_id ?: null;
            $this->data['locked_barangay'] = $barangay_id ? $this->barangay_model->get_by_id($barangay_id) : null;

            if ($barangay_id) {
                $this->load_barangay_survey_scope($barangay_id);
            }

            $this->render_data_survey_report();
            return;
        }

        if ($this->restricted_municipality_id !== null) {
            $municipality_id = $this->restricted_municipality_id ?: null;
        } else {
            $this->data['regions'] = $this->region_model->get_all();

            $municipality_id_param = $this->input->get('municipality_id');
            $municipality_id = $municipality_id_param !== null
                ? ($municipality_id_param ?: null)
                : ($this->active_municipality->id ?? null);
        }

        if ($municipality_id) {
            $municipality = $this->municipality_model->get_by_id($municipality_id);
            if ($municipality) {
                if ($this->restricted_municipality_id !== null && (int) $municipality->id !== (int) $this->restricted_municipality_id) {
                    show_error('You are not authorized to access this page.', 403, 'Access Denied');
                }

                $province = $this->province_model->get_by_id($municipality->province_id);

                $this->data['municipality'] = $municipality;
                $this->data['province'] = $province;
                $this->data['barangays'] = $this->barangay_model->get_by_municipality($municipality_id);

                if ($this->restricted_municipality_id === null && $province) {
                    $this->data['selected_region_id'] = $province->region_id;
                    $this->data['provinces'] = $this->province_model->get_by_region($province->region_id);
                    $this->data['selected_province_id'] = $municipality->province_id;
                    $this->data['municipalities'] = $this->municipality_model->get_by_province($municipality->province_id);
                    $this->data['selected_municipality_id'] = $municipality_id;
                }

                $barangay_id = $this->input->get('barangay_id');
                if ($barangay_id) {
                    $barangay = $this->barangay_model->get_by_id($barangay_id);
                    if (!$barangay || (int) $barangay->municipality_id !== (int) $municipality_id) {
                        show_error('You are not authorized to access this page.', 403, 'Access Denied');
                    }
                    $this->data['barangay'] = $barangay;
                    $this->data['survey_rows'] = $this->resident_data_survey_model->get_survey_roster_by_barangay($barangay_id);
                } else {
                    $this->data['show_barangay_column'] = true;
                    $this->data['survey_rows'] = $this->resident_data_survey_model->get_survey_roster_by_municipality($municipality_id);
                }
            }
        }

        $this->render_data_survey_report();
    }

    private function load_barangay_survey_scope($barangay_id)
    {
        $barangay = $this->barangay_model->get_by_id($barangay_id);
        if (!$barangay) {
            return;
        }

        $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
        $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

        $this->data['barangay'] = $barangay;
        $this->data['municipality'] = $municipality;
        $this->data['province'] = $province;
        $this->data['survey_rows'] = $this->resident_data_survey_model->get_survey_roster_by_barangay($barangay_id);
    }

    private function render_data_survey_report()
    {
        $this->load->view('templates/header', $this->data);
        $this->load->view('health/data_survey_report', $this->data);
        $this->load->view('templates/footer');
    }
}
