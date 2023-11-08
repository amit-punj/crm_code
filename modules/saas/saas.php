<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Perfect SaaS - Powerful Multi-Tenancy Module for Perfex CRM
Description: this is a module for Perfex CRM that allows you to create a SaaS or multi-company enabled setup.
Version: 1.1.3
Requires at least: 2.3.*
*/

define('SaaS_MODULE', 'saas');

$CI = &get_instance();
/**
 * Load the module helper
 */
$CI->load->helper(SaaS_MODULE . '/saas');

// load libraries for saas
$CI->load->library(SaaS_MODULE . '/mails/saas_mail_template');
/**
 * Register activation module hook
 */
register_activation_hook(SaaS_MODULE, 'saas_activation_hook');

register_deactivation_hook(SaaS_MODULE, 'saas_deactivation_hook');
register_uninstall_hook(SaaS_MODULE, 'saas_uninstall_hook');

hooks()->add_filter('module_saas_action_links', 'module_saas_action_links');

/**
 * Add additional settings for this module in the module list area
 * @param array $actions current actions
 * @return array
 */
function module_saas_action_links($actions)
{
    $actions[] = '<a href="' . saas_url('settings/server') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="https://docs.coderitems.com/perfectsaas/" target="_blank">' . _l('help') . '</a>';
    return $actions;
}

function saas_deactivation_hook()
{
    require_once(__DIR__ . '/deactivate.php');
}

function saas_uninstall_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

function saas_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

register_language_files(SaaS_MODULE, [SaaS_MODULE]);

hooks()->add_action('admin_init', 'saas_init_menu_items');
hooks()->add_action('clients_init', 'saas_init_client_items');
hooks()->add_action('app_init', 'saas_init');
hooks()->add_action('after_staff_login', 'check_login');
register_merge_fields('saas/merge_fields/saas_company_merge_fields');
register_merge_fields('saas/merge_fields/affiliate_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'saas_register_other_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'affiliate_register_other_merge_fields');
hooks()->add_action('after_email_templates', 'saas_email_templates');
hooks()->add_action('before_start_render_dashboard_content', 'saas_dashboard_content');
hooks()->add_action('before_payment_recorded', 'saas_payment_recorded');
hooks()->add_action('after_admin_login_form_start', 'saas_admin_login_form_start');
hooks()->add_action('before_admin_login_form_close', 'saas_admin_login_form_close');
hooks()->add_action('before_login', 'saas_before_staff_login');
hooks()->add_action('sidebar_menu_items', 'saas_sidebar_menu_items');
hooks()->add_action('pre_activate_module', 'saas_pre_activate_module');
hooks()->add_action('pre_deactivate_module', 'saas_pre_deactivate_module');
hooks()->add_action('pre_uninstall_module', 'saas_pre_uninstall_module');
hooks()->add_filter('get_media_folder', 'saas_set_media_folder', PHP_INT_MAX);
hooks()->add_filter('get_upload_path_by_type', 'saas_set_upload_path_by_type', PHP_INT_MAX);
hooks()->add_filter('after_render_aside_menu', 'saas_after_render_single_aside_menu');


function saas_after_render_single_aside_menu($item)
{
    if (!empty(subdomain())) {
        // remove badge from li#setup-menu-item a then span then span using css class
        $html = '';
        $html .= '<style>';
        $html .= 'li#setup-menu-item a span span.badge {';
        $html .= 'display: none;';
        $html .= '}';
        $html .= '</style>';
        echo $html;
    }


}

/**
 * @throws Exception
 */
function saas_admin_login_form_start()
{
    $html = '';
    if (subdomain() == '') {
        $html .= '<div class="form-group">';
        // append input group
        $html .= '<div class="input-group">';
        $html .= '<input type="text" class="form-control" name="account" placeholder="' . lang('company') . '" autocomplete="off">';
        $html .= '<div class="input-group-addon">';
        $html .= '<span>.' . saas_base_url() . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        // help block for subdomain
        $html .= '<p class="help-block small">' . lang('subdomain_help') . '</p>';
        $html .= '</div>';
    }
    echo $html;
}

/**
 * @throws Exception
 */
function saas_admin_login_form_close()
{
    if (empty(subdomain())) {
        $html = '';
        $html .= '<div class="form-group">';
        $html .= '<a class="btn btn-default btn-block" href="' . site_url('find-my-company') . '">' . _l('find_my_company') . '</a>';
        $html .= '</div>';

        echo $html;
    }
}

const insert_hook_data = [
    'before_invoice_added' => 'invoices',
    'before_estimate_added' => 'estimates',
    'before_create_credit_note' => 'credit_notes',
    'before_create_proposal' => 'proposals',
    'before_client_added' => 'customers',
    'before_create_contact' => 'contacts',
    'before_create_staff_member' => 'staff',
    'before_add_project' => 'projects',
    'before_add_task' => 'tasks',
    'before_ticket_created' => 'support',
    'before_lead_added' => 'leads',
    'before_expense_added' => 'expenses',
    'before_contract_added' => 'contracts',
    'before_item_created' => 'items',
];
foreach (insert_hook_data as $event => $table) {
    // Set priority to 0 as we want this to run before any other attached hooks to the filter.
    hooks()->add_filter($event, 'saas_insert_data', 0);
}
/**
 * @throws Exception
 */
function saas_insert_data($data)
{
    $is_subdomain = subdomain();
    $subscription = get_company_subscription(null, 'running');

    if (!empty($is_subdomain) && !empty($subscription)) {
        $filter = hooks()->current_filter();
        $slug = insert_hook_data[$filter];
        $usages = get_usages($subscription, $slug);
        if (!empty($usages)) {
            foreach ($usages as $usage) {
                if (!empty($usage['limit'])) {
                    $limit = $usage['limit'];
                    $count = $usage['total'];
                    // check if limit is numeric or string
                    // if string its means unlimited
                    // if numeric its means limited then check limit and count
                    if (is_numeric($usage['limit']) && $limit <= $count) {
                        set_alert('warning', _l('add_failed_you_have_reached_limit'));
                        redirect('checkoutPayment');
                    }
                } else {
                    set_alert('warning', _l('add_failed_you_have_reached_limit'));
                    redirect('checkoutPayment');
                }
            }
        }
    }

    return $data;
}

/**
 * @throws Exception
 */
function saas_sidebar_menu_items($items)
{
    if (!empty(subdomain())) {
        $company_info = get_company_subscription(null, 'running');
        $usages = get_usages($company_info);

        // check $usages array slug  and items array slug if slug is same then add class active
        $allUses = [];
        if (!empty($usages)) {
            foreach ($usages as $usage) {
                if (empty($usage['limit'])) {
                    $allUses[] = $usage['slug'];
                }
            }
        }
        if (!empty($items)) {
            foreach ($items as $key => $item) {
                if (in_array($item['slug'], $allUses)) {
                    $items[$key]['slug'] = $item['slug'] . ' hidden';
                }
                // $item have children then check children slug and $usages array slug if slug is same then add class active
                if (!empty($item['children'])) {
                    foreach ($item['children'] as $k => $child) {
                        if (in_array($child['slug'], $allUses)) {
                            $items[$key]['children'][$k]['slug'] = $child['slug'] . ' hidden';
                        }
                    }
                }
            }
        }
    }
    return $items;
}

/**
 * @throws Exception
 */
function saas_dashboard_content()
{
    $html = '';
    if (!empty(subdomain())) {
        $subs = get_company_subscription(null, 'running');
        $result = is_account_running($subs, true);
        if (!empty($result['trial'])) {
            $trial_period = $result['trial'];
            $type = 'trial';
            $b_text = _l('you_are_using_trial_version', $subs->package_name) . ' ' . $trial_period . ' ' . _l('days');
        } else {
            $trial_period = $result['running'];
            $type = 'running';
            $b_text = _l('your_pricing_plan_will_expired', $subs->package_name) . ' ' . $trial_period . ' ' . _l('days');
        }
        if ($trial_period <= 0) {
            redirect('upgrade');
        }
        if ($type == 'trial' || $trial_period < 3) {
            // make a alert for trial period
            $html .= '<div class="col-md-12 mtop20" role="alert">';
            $html .= '<div class="alert alert-danger " role="alert">';
            $html .= '<span class="text-sm text-danger">' . $b_text . '</span>';
            $html .= '<strong class=""><a href="' . base_url('checkoutPayment') . '"> ' . _l('upgrade') . '</a></strong>';
            $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            $html .= '<span aria-hidden="true">&times;</span>';
            $html .= '</button>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }
    echo $html;
}

/**
 * @throws Exception
 */
function saas_init_client_items()
{
    is_complete_setup();
    if (empty(subdomain())) {
        add_theme_menu_item('packages', [
            'name' => _l('packages'),
            'href' => site_url('pricing'),
            'position' => 2,
        ]);
        // affiliate
        add_theme_menu_item('affiliate', [
            'name' => _l('affiliate'),
            'href' => site_url('affiliate'),
            'position' => 3,
        ]);
        add_theme_menu_item('find_my_company', [
            'name' => _l('find_my_company'),
            'href' => site_url('find-my-company'),
            'position' => 3,
        ]);
    }
}

/**
 * @throws Exception
 */
function saas_pre_uninstall_module()
{
    if (!empty(subdomain())) {
        access_denied();
    }
}

/**
 * @throws Exception
 */
function saas_pre_deactivate_module($module)
{
    $moduleName = $module['system_name'];
    if (!empty(subdomain())) {
        if ($moduleName == 'saas') {
            access_denied();
        }
        $subs = get_company_subscription(null, 'running');
        if (!empty($subs) && !empty($subs->modules)) {
            $modules = unserialize($subs->modules);
            if (!empty($modules) && in_array($moduleName, $modules)) {
                return true;
            } else {
                access_denied();
            }
        } else {
            access_denied();
        }
    }

}

function saas_pre_activate_module($module)
{
    $moduleName = $module['system_name'];
    if (function_exists('subdomain') && function_exists('is_subdomain') && !empty(subdomain())) {
        if ($moduleName == 'saas') {
            access_denied();
        }
        $subs = get_company_subscription(null, 'running');
        if (!empty($subs) && !empty($subs->modules)) {
            $modules = unserialize($subs->modules);
            if (!empty($modules) && in_array($moduleName, $modules)) {
                return true;
            } else {
                access_denied();
            }
        } else {
            access_denied();
        }
    }

}

function saas_register_other_merge_fields($for)
{
    $for[] = 'saas';
    return $for;
}

function affiliate_register_other_merge_fields($for)
{
    $for[] = 'affiliate';
    return $for;
}

/**
 * @throws Exception
 */
function saas_email_templates()
{
    if (!empty(is_super_admin()) && empty(subdomain())) {
        $CI = &get_instance();
        $CI->load->model('emails_model');
        $data['saas'] = $CI->emails_model->get([
            'type' => 'saas',
            'language' => 'english',
        ]);
        $data['affiliate'] = $CI->emails_model->get([
            'type' => 'affiliate',
            'language' => 'english',
        ]);
        $CI->load->view('saas/settings/email_templates', $data);
    }
}

function check_login()
{
    $is_super_admin = is_super_admin();
    if (!empty($is_super_admin)) {
        redirect(saas_url('dashboard'));
    }
}

/**
 * @throws Exception
 */
function saas_init_menu_items()
{
    /**
     * If the logged in user is administrator, add custom menu in Setup
     */
    is_complete_setup();
    $CI = &get_instance();
    if (!empty(is_super_admin()) && empty(subdomain())) {
        $CI->app_menu->add_sidebar_menu_item('saas', [
            'name' => '<span class="text-danger">' . _l('saas_management') . '</span>',
            'position' => 0,
            'icon' => 'fa-solid fa-receipt menu-icon text-danger',
            'href' => saas_url('dashboard'),
        ]);
    }
    $db_name = $CI->session->userdata('db_name');
    if (!empty(is_admin()) && !empty(subdomain()) || !empty(is_admin()) && !empty($db_name)) {
        $subs = get_company_subscription(null, 'running');
        $all_themes = (!empty($subs->allowed_themes) ? unserialize($subs->allowed_themes) : array());
        if ($subs->maintenance_mode == 'Yes') {
            $maintenance_message = $subs->maintenance_mode_message;
            $account_status = $subs->status;
            include_once module_dir_path('saas') . 'views/maintenance.php';
            die();
        }

        // Reserved routes
        $restricted_menus = ['modules'];
        foreach ($restricted_menus as $menu) {
            $CI->app_menu->add_setup_menu_item($menu, ['name' => '', 'href' => '', 'disabled' => true]);
        }

        $restricted_classes = ['mods'];
        $controller = $CI->router->fetch_class();
        if (in_array($controller, $restricted_classes)) {
            access_denied();
        }

        $CI->app_menu->add_sidebar_menu_item('saas', [
            'name' => '<span class="text-danger">' . _l('saas_billings') . '</span>',
            'position' => 80,
            'icon' => 'fa-solid fa-receipt menu-icon text-danger',
            'badge' => [],
        ]);

        $CI->app_menu->add_sidebar_children_item('saas', [
            'slug' => 'dashboard',
            'name' => _l('dashboard'),
            'href' => admin_url('billings'),
            'position' => 1,
            'badge' => [],
        ]);

        $CI->app_menu->add_sidebar_children_item('saas', [
            'slug' => 'customizePackages',
            'name' => _l('customize_packages'),
            'href' => admin_url('customizePackages'),
            'position' => 2,
            'badge' => [],
        ]);

        if ($subs->custom_domain == 'Yes') {
            $CI->app_menu->add_sidebar_children_item('saas', [
                'slug' => 'custom_domain',
                'name' => _l('custom_domain'),
                'href' => admin_url('custom_domain'),
                'position' => 2,
                'badge' => [],
            ]);
        }
        if (count($all_themes) > 0) {
            $CI->app_menu->add_sidebar_children_item('saas', [
                'slug' => 'theme_builder',
                'name' => _l('theme_builder'),
                'href' => admin_url('themebuilder'),
                'position' => 3,
                'badge' => [],
            ]);
        }

    }
}

