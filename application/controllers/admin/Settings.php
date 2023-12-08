<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_modes_model');
        $this->load->model('settings_model');
    }

    /* View all settings */
    public function index()
    {
        if (!has_permission('settings', '', 'view')) {
            access_denied('settings');
        }

        $tab = $this->input->get('group');
        $gst_id = $this->input->get('id') ?? '';
        $einvoice_id = $this->input->get('einvoice_id') ?? '';
        if ($this->input->post()) {
            // pr($this->input->post());
            if (!has_permission('settings', '', 'edit')) {
                access_denied('settings');
            }
            $logo_uploaded     = (handle_company_logo_upload() ? true : false);
            $favicon_uploaded  = (handle_favicon_upload() ? true : false);
            $signatureUploaded = (handle_company_signature_upload() ? true : false);

            $post_data = $this->input->post();
            // pr($post_data);
            $gst_data = [];
            $einvoice_data = [];
            $tmpData   = $this->input->post(null, false);

            if (isset($post_data['settings']['email_header'])) {
                $post_data['settings']['email_header'] = $tmpData['settings']['email_header'];
            }

            if (isset($post_data['settings']['email_footer'])) {
                $post_data['settings']['email_footer'] = $tmpData['settings']['email_footer'];
            }

            if (isset($post_data['settings']['email_signature'])) {
                $post_data['settings']['email_signature'] = $tmpData['settings']['email_signature'];
            }

            if (isset($post_data['settings']['smtp_password'])) {
                $post_data['settings']['smtp_password'] = $tmpData['settings']['smtp_password'];
            }

            //Insert Data in GST Table
            if (!empty($post_data['settings']['gst_number'])) {
                $gst_data['gst_number'] = $tmpData['settings']['gst_code'].$tmpData['settings']['gst_number'];
            }

            if (!empty($post_data['settings']['pan_number'])) {
                $gst_data['pan_number'] = $tmpData['settings']['pan_number'];
            }

            if (!empty($post_data['settings']['gst_user_id'])) {
                $gst_data['gst_user_id'] = $tmpData['settings']['gst_user_id'];
            }

            if (!empty($post_data['settings']['gst_password'])) {
                $gst_data['gst_password'] = $tmpData['settings']['gst_password'];
            }

            if (!empty($post_data['settings']['authorised_person_name'])) {
                $gst_data['authorised_person_name'] = $tmpData['settings']['authorised_person_name'];
            }

            if (!empty($post_data['settings']['father_name'])) {
                $gst_data['father_name'] = $tmpData['settings']['father_name'];
            }

            if (!empty($post_data['settings']['address'])) {
                $gst_data['address'] = $tmpData['settings']['address'];
            }

            if (!empty($post_data['settings']['din_number'])) {
                $gst_data['din_number'] = $tmpData['settings']['din_number'];
            }

            if (!empty($post_data['settings']['bank_name'])) {
                $gst_data['bank_name'] = $tmpData['settings']['bank_name'];
            }

            if (!empty($post_data['settings']['bank_account_number'])) {
                $gst_data['bank_account_number'] = $tmpData['settings']['bank_account_number'];
            }

            if (!empty($post_data['settings']['bank_ifsc_code'])) {
                $gst_data['bank_ifsc_code'] = $tmpData['settings']['bank_ifsc_code'];
            }

            if (!empty($post_data['settings']['bank_address'])) {
                $gst_data['bank_address'] = $tmpData['settings']['bank_address'];
            }
            
            if (!empty($post_data['settings']['gstin'])) {
                $gst_data['gstin'] = $tmpData['settings']['gstin'];
            }

            if (!empty($post_data['settings']['enable_default_gst'])) {
                $gst_data['enable_default_gst'] = $tmpData['settings']['enable_default_gst'];
                $mark_all_disable = $this->settings_model->mark_all_disable();
            } else {
                $gst_data['enable_default_gst'] = 0;
            }

            $gst_data['enable_default_gst'] = $tmpData['settings']['enable_default_gst'] ?? 0;
            $gst_data['user_id'] = get_staff_user_id();
            
            // pr($gst_data);
            $gst_id = $post_data['gst_id'] ?? '';
            // pr($gst_id,1);
            $einvoice_id = $post_data['einvoice_id'] ?? '';
            
            $einvoice_data['user_id'] = get_staff_user_id();
            $einvoice_data['gst_number'] = $tmpData['gst_number'];
            $einvoice_data['user_name'] = $tmpData['user_name'];
            $einvoice_data['password'] = $tmpData['password'];
            // $einvoice_data['einvoice_applicable'] = $tmpData['einvoice_applicable'];

            // pr($einvoice_data,1);
            
            $success = $this->settings_model->update($post_data);
            if(is_array( $gst_data ) && !empty( $gst_data )){
                $gst_success = $this->settings_model->gst_update($gst_data, $gst_id);
            }
            if(is_array( $einvoice_data ) && !empty( $einvoice_data )){
                $gst_success = $this->settings_model->einvoice_update($einvoice_data, $einvoice_id);
            }

            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            }

            if ($logo_uploaded || $favicon_uploaded) {
                set_debug_alert(_l('logo_favicon_changed_notice'));
            }

            // Do hard refresh on general for the logo
            if ($tab == 'general') {
                redirect(admin_url('settings?group=' . $tab), 'refresh');
            } elseif ($signatureUploaded) {
                redirect(admin_url('settings?group=pdf&tab=signature'));
            } else {
                $redUrl = admin_url('settings?group=' . $tab);
                if ($this->input->get('active_tab')) {
                    $redUrl .= '&tab=' . $this->input->get('active_tab');
                }
                redirect($redUrl);
            }
        }

        $this->load->model('taxes_model');
        $this->load->model('tickets_model');
        $this->load->model('leads_model');
        $this->load->model('currencies_model');
        $this->load->model('staff_model');
        $data['taxes']                                   = $this->taxes_model->get();
        $data['ticket_priorities']                       = $this->tickets_model->get_priority();
        $data['ticket_priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['roles']                                   = $this->roles_model->get();
        $data['gsts']                                    = $this->settings_model->get_all();
        $data['einvoices']                               = $this->settings_model->get_all_einvoice();
        $data['leads_sources']                           = $this->leads_model->get_source();
        $data['leads_statuses']                          = $this->leads_model->get_status();
        $data['title']                                   = _l('options');
        $data['staff']                                   = $this->staff_model->get('', ['active' => 1]);
        $data['states']                                  = $this->db->get(db_prefix() . '_state_gst_code')->result_array();

        $data['gst_details'] = $this->settings_model->get_default_gst();
        if(!empty($gst_id)){
            $data['gst_details'] = $this->settings_model->get_all($gst_id);
        }
        if(!empty($einvoice_id)){
            $data['einvoice_details'] = $this->settings_model->get_all_einvoice($einvoice_id);
        }

        foreach ($data['einvoices'] as $item) {
            $newArray[] = $item['gst_number'];
        }
        // pr($newArray,1);
        // pr($data['einvoices'],1);

        $data['exist_gsts'] = $newArray;
        $data['admin_tabs'] = ['update', 'info'];

        if (!$tab || (in_array($tab, $data['admin_tabs']) && !is_admin())) {
            $tab = 'general';
        }

        $data['tabs'] = $this->app_tabs->get_settings_tabs();
        if (!in_array($tab, $data['admin_tabs'])) {
            $data['tab'] = $this->app_tabs->filter_tab($data['tabs'], $tab);
        } else {
            // Core tabs are not registered
            $data['tab']['slug'] = $tab;
            $data['tab']['view'] = 'admin/settings/includes/' . $tab;
            $data['tab']['name'] = $tab === 'info' ? ' System/Server Info' : _l('settings_update');
        }

        if (!$data['tab']) {
            show_404();
        }

        if ($data['tab']['slug'] == 'update') {
            if (!extension_loaded('curl')) {
                $data['update_errors'][] = 'CURL Extension not enabled';
                $data['latest_version']  = 0;
                $data['update_info']     = json_decode('');
            } else {
                $data['update_info'] = $this->app->get_update_info();
                if (strpos($data['update_info'], 'Curl Error -') !== false) {
                    $data['update_errors'][] = $data['update_info'];
                    $data['latest_version']  = 0;
                    $data['update_info']     = json_decode('');
                } else {
                    $data['update_info']    = json_decode($data['update_info']);
                    $data['latest_version'] = $data['update_info']->latest_version;
                    $data['update_errors']  = [];
                }
            }

            if (!extension_loaded('zip')) {
                $data['update_errors'][] = 'ZIP Extension not enabled';
            }

            $data['current_version'] = $this->current_db_version;
        }

        $data['financial_years'] = [
                                    '23-24' => '2023-2024',
                                    '24-25' => '2024-2025',
                                    '25-26' => '2025-2026',
                                    '26-27' => '2026-2027',
                                ];

        $data['contacts_permissions'] = get_contact_permissions();
        $data['payment_gateways']     = $this->payment_modes_model->get_payment_gateways(true);

        $this->load->view('admin/settings/all', $data);
    }
    public function get_gstcode_byState(){
        $state= $this->input->post('state');
        $this->db->where('state', $state);
        $gstCode = $this->db->get(db_prefix() . '_state_gst_code')->row();
        echo json_encode([
            'success' => 'get successfully!',
            'data'=>$gstCode
        ]);
    }

    public function delete_gst($id)
    {
        if (!$id) {
            redirect(admin_url('settings?group=company&tab=gst'));
        }

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . '_gsts');

        set_alert('success', _l('GST Deleted!'));

        redirect(admin_url('settings?group=company&tab=gst'));
    }
    public function delete_einvoice($id){
        if (!$id) {
            redirect(admin_url('settings?group=company&tab=einvoice'));
        }

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . '_einvoices');

        set_alert('success', _l('GST Deleted!'));

        redirect(admin_url('settings?group=company&tab=einvoice'));
    }

    public function delete_tag($id)
    {
        if (!$id) {
            redirect(admin_url('settings?group=tags'));
        }

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tags');
        $this->db->where('tag_id', $id);
        $this->db->delete(db_prefix() . 'taggables');

        redirect(admin_url('settings?group=tags'));
    }

    public function remove_signature_image()
    {
        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $sImage = get_option('signature_image');
        if (file_exists(get_upload_path_by_type('company') . '/' . $sImage)) {
            unlink(get_upload_path_by_type('company') . '/' . $sImage);
        }

        update_option('signature_image', '');

        redirect(admin_url('settings?group=pdf&tab=signature'));
    }

    /* Remove company logo from settings / ajax */
    public function remove_company_logo($type = '')
    {
        hooks()->do_action('before_remove_company_logo');

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $logoName = get_option('company_logo');
        if ($type == 'dark') {
            $logoName = get_option('company_logo_dark');
        }

        $path = get_upload_path_by_type('company') . '/' . $logoName;
        if (file_exists($path)) {
            unlink($path);
        }

        update_option('company_logo' . ($type == 'dark' ? '_dark' : ''), '');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function remove_fv()
    {
        hooks()->do_action('before_remove_favicon');
        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }
        if (file_exists(get_upload_path_by_type('company') . '/' . get_option('favicon'))) {
            unlink(get_upload_path_by_type('company') . '/' . get_option('favicon'));
        }
        update_option('favicon', '');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_option($name)
    {
        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        echo json_encode([
            'success' => delete_option($name),
        ]);
    }

    public function clear_sessions()
    {
        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }
        $this->db->empty_table(db_prefix() . 'sessions');

        set_alert('success', 'Sessions Cleared');
            redirect(admin_url('settings?group=info'));
    }
}
