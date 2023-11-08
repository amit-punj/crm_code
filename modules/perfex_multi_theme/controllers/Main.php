<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('main_model');
    }
    public function index()
    {
        $this->load->view('perfex_multi_theme_settings');
        $login_bg_Uploaded = (handle_login_bg_image_upload() ? true : false);
        $dashboard_bg_Uploaded = (handle_dashboard_bg_image_upload() ? true : false);
        if ($dashboard_bg_Uploaded) {
            set_alert('success', _l('settings_updated'));
        }
        if ($login_bg_Uploaded) {
            set_alert('success', _l('settings_updated'));
        }

        if ($this->input->post()) {
            $val =  $this->input->post('is_mt_client');
            update_option('perfex_multi_theme_clients', $val, 1);
            if (true) {
                set_alert('success', _l('settings_updated'));
                //redirect(admin_url('perfex_multi_theme/main'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        }
    }
    public function update_color()
    {
        if ($this->input->is_ajax_request()) {
            $success = $this->main_model->update_color($this->input->get());
            echo $success;
        }
    }

    /* Remove login image from settings / ajax */
    public function remove_login_bg_image($type = '')
    {
        hooks()->do_action('before_remove_company_logo');

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $logoName = get_option('login_bg_image');
        $path = get_upload_path_by_type('company') . '/' . $logoName;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option('login_bg_image', '');
        redirect($_SERVER['HTTP_REFERER']);
    }
    /* Remove dashboard image from settings / ajax */
    public function remove_dashboard_bg_image($type = '')
    {
        hooks()->do_action('before_remove_company_logo');

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $logoName = get_option('dashboard_bg_image');
        $path = get_upload_path_by_type('company') . '/' . $logoName;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option('dashboard_bg_image', '');
        redirect($_SERVER['HTTP_REFERER']);
    }
}
