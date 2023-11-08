<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * This class describes an application sale agent area constructor.
 */
class App_sale_agent_area_constructor
{
    private $ci;

    /**
     * Constructs a new instance.
     */
    public function __construct()
    {
        $this->ci = &get_instance();

        $this->ci->load->library('form_validation');
        $this->ci->form_validation->set_error_delimiters('<p class="text-danger alert-validation">', '</p>');

        $this->ci->form_validation->set_message('required', _l('form_validation_required'));
        $this->ci->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->ci->form_validation->set_message('matches', _l('form_validation_matches'));
        $this->ci->form_validation->set_message('is_unique', _l('form_validation_is_unique'));

        $this->ci->load->model('tickets_model');
        $this->ci->load->model('departments_model');
        $this->ci->load->model('currencies_model');
        $this->ci->load->model('invoices_model');
        $this->ci->load->model('estimates_model');
        $this->ci->load->model('proposals_model');
        $this->ci->load->model('projects_model');
        $this->ci->load->model('announcements_model');
        $this->ci->load->model('contracts_model');
        $this->ci->load->model('knowledge_base_model');
        $this->ci->load->model('sales_agent/sales_agent_model');
        $this->ci->load->model('clients_model');


        $vars = [];
        if (is_sale_agent_logged_in()) {
            $sale_agent            = $this->ci->clients_model->get(get_sale_agent_user_id());
            $GLOBALS['sale_agent'] = $sale_agent;

            $vars['sale_agent']                         = $sale_agent;
        }

        if (is_sale_agent_logged_in()) {
            $contact            = $this->ci->clients_model->get_contact(get_sa_contact_user_id());
            $GLOBALS['contact'] = $contact;

            if (!$contact || $contact->active == 0) {
                $this->ci->authentication_sale_agent_model->logout(true);
                redirect(site_url('sales_agent/authentication_sales_agent'));
            }

            $vars['total_undismissed_announcements'] = $this->ci->announcements_model->get_total_undismissed_announcements();
            $vars['client']                          = $this->ci->clients_model->get($contact->userid);
            $vars['contact']                         = $contact;
        }
        
        include_once(module_dir_path(SALES_AGENT_MODULE_NAME, 'views/portal/functions.php'));
        init_sale_agent_area_assets();

        hooks()->do_action('sale_agent_init');
        
        $vars['departments']     = $this->ci->departments_model->get(false, true);
        $vars['priorities']      = $this->ci->tickets_model->get_priority();
        $vars['ticket_statuses'] = $this->ci->tickets_model->get_ticket_status();
        $vars['currencies']      = $this->ci->currencies_model->get();
        $vars['menu']            = $this->ci->app_menu->get_theme_items();
        $vars['isRTL']           = 'false';

        if (get_option('services') == 1) {
            $vars['services'] = $this->ci->tickets_model->get_service();
        }

        $vars = hooks()->apply_filters('customers_area_autoloaded_vars', $vars);

        $this->ci->load->vars($vars);
    }
}
