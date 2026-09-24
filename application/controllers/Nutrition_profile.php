<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Barangay Nutrition Profile (BNS Form No. 1C) -- a standalone, barangay-scoped report, separate from the generic Reports builder. */
class Nutrition_profile extends MY_Controller
{
    /** null = unrestricted, or (for an encoder assigned at municipality/province/region level) scoped via restricted_municipality_id; int = locked to this barangay; 0 = encoder has no area assigned */
    protected $restricted_barangay_id;

    public function __construct()
    {
        parent::__construct();
        $this->require_role(['super_admin', 'admin', 'encoder']);
        $this->load->model('nutrition_profile_model');
        $this->load->model('barangay_model');
        $this->load->model('municipality_model');
        $this->load->model('province_model');
        $this->load->model('region_model');

        $this->restricted_barangay_id = null;
        if ($this->current_user->role_name === 'encoder') {
            $barangay_assignment = null;
            $has_wider_assignment = false;
            foreach ($this->authentication->assigned_areas() as $area) {
                if ($area->scope_type === 'barangay') {
                    $barangay_assignment = $area;
                    break;
                }
                if (in_array($area->scope_type, ['municipality', 'province', 'region'], true)) {
                    $has_wider_assignment = true;
                }
            }
            if ($barangay_assignment) {
                $this->restricted_barangay_id = $barangay_assignment->barangay_id;
            } elseif (!$has_wider_assignment) {
                $this->restricted_barangay_id = 0;
            }
            // Else: assigned at municipality/province/region level -- stays null so
            // callers fall back to restricted_municipality_id (any barangay in it).
        }
        $this->data['restricted_barangay_id'] = $this->restricted_barangay_id;
    }

    public function index()
    {
        $barangay_id = $this->restricted_barangay_id !== null
            ? ($this->restricted_barangay_id ?: null)
            : $this->input->get('barangay_id');

        $this->data['page_title'] = 'Nutrition Profile';
        $this->data['active_menu'] = 'nutrition_profile';
        $this->data['regions'] = [];
        $this->data['barangays'] = [];
        $this->data['locked_barangay'] = null;
        $this->data['barangay'] = null;
        $this->data['municipality'] = null;
        $this->data['province'] = null;
        $this->data['summary'] = null;

        if ($this->restricted_barangay_id !== null) {
            $this->data['locked_barangay'] = $this->restricted_barangay_id
                ? $this->barangay_model->get_by_id($this->restricted_barangay_id)
                : null;
        } elseif ($this->restricted_municipality_id !== null) {
            $this->data['barangays'] = $this->barangay_model->get_by_municipality($this->restricted_municipality_id);
        } else {
            $this->data['regions'] = $this->region_model->get_all();
        }

        if ($barangay_id) {
            $barangay = $this->barangay_model->get_by_id($barangay_id);
            if ($barangay) {
                if ($this->restricted_municipality_id !== null && (int) $barangay->municipality_id !== (int) $this->restricted_municipality_id) {
                    show_error('You are not authorized to access this page.', 403, 'Access Denied');
                }
                $municipality = $this->municipality_model->get_by_id($barangay->municipality_id);
                $province = $municipality ? $this->province_model->get_by_id($municipality->province_id) : null;

                $this->data['barangay'] = $barangay;
                $this->data['municipality'] = $municipality;
                $this->data['province'] = $province;
                $this->data['summary'] = $this->nutrition_profile_model->get_summary($barangay_id);
            }
        }

        $this->load->view('templates/header', $this->data);
        $this->load->view('nutrition_profile/index', $this->data);
        $this->load->view('templates/footer');
    }
}
