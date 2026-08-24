<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base controller for all authenticated pages. Redirects to the login
 * page when there is no active session, and exposes the current user
 * plus a role-guard helper to subclasses.
 */
class MY_Controller extends CI_Controller
{
    protected $current_user;
    protected $data = [];

    /** The municipality currently flagged active in System Settings, or null if none is set. */
    protected $active_municipality;

    /**
     * Null for super_admin (unrestricted). For every other role this holds
     * the active municipality's id, or 0 when no municipality is active
     * (meaning the user should see no address/profiling data at all).
     */
    protected $restricted_municipality_id;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('authentication');

        if (!$this->authentication->is_logged_in()) {
            redirect('auth/login');
            return;
        }

        $this->current_user = $this->authentication->user();
        $this->data['current_user'] = $this->current_user;

        $this->load->model('municipality_model');
        $this->active_municipality = $this->municipality_model->get_active();
        $this->data['active_municipality'] = $this->active_municipality;

        if ($this->current_user->role_name !== 'super_admin') {
            $this->restricted_municipality_id = $this->active_municipality->id ?? 0;
        }
        $this->data['restricted_municipality_id'] = $this->restricted_municipality_id;
    }

    /**
     * Aborts with a 403 unless the current user has one of the given roles.
     *
     * @param string|array $roles
     */
    protected function require_role($roles)
    {
        if (!$this->authentication->has_role($roles)) {
            show_error('You are not authorized to access this page.', 403, 'Access Denied');
        }
    }
}
