<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gb_admin extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('saas_model');
    }

    /**
     * @throws Exception
     */
    public function assignPackage()
    {
        $data['title'] = _l('assign_package');
        $view = '_layout_main';
        if (!empty(subdomain())) {
            $view = '_layout_open';
            $data['current_package'] = get_company_subscription()->package_id;
        }
        $data['all_packages'] = get_old_result('tbl_saas_packages', array('status' => 'published'));
        $data['subview'] = $this->load->view('saas/packages/assign_package', $data, TRUE);
        $this->load->view($view, $data);
    }

    /**
     * @throws Exception
     */
    public
    function checkoutPayment($package_id = null, $company_id = null)
    {

        $data['package_id'] = $package_id;
        $data['frequency'] = 'monthly';
        if (empty($data['package_id']) && !empty(subdomain())) {
            $subs_info = get_company_subscription(null, 'running');
            $data['package_id'] = $subs_info->package_id;
            $data['frequency'] = $subs_info->frequency;
        }
        $package_info = get_old_result('tbl_saas_packages', array('id' => $data['package_id']), false);
        $data['title'] = _l('checkout') . ' ' . _l('payment') . ' ' . _l('for') . ' ' . $package_info->name;
        $data['package_info'] = $package_info;
        $data['all_packages'] = get_old_result('tbl_saas_packages', array('status' => 'published'));
        $subview = 'checkoutPayment';
        if (!empty(subdomain())) {
            $front_end = true;
            $data['subs_info'] = get_company_subscription();
            $data['payment_modes'] = $this->saas_model->get_payment_modes();
            $subview = 'checkoutPaymentOpen';
        } else if (!empty($company_id)) {
            $company_id = url_decode($company_id);
            $data['company_info'] = $this->saas_model->company_info($company_id);
            $data['payment_modes'] = $this->saas_model->get_payment_modes();
            $subview = 'checkoutPaymentOpen';
        }
        $data['subview'] = $this->load->view('saas/packages/' . $subview, $data, TRUE);
        $user_id = get_staff_user_id();
        if (!empty($user_id) && empty($front_end)) {
            $this->load->view('_layout_main', $data); //page load
        } else {
            $this->load->view('_layout_open', $data); //page load
        }
    }

    public function billings()
    {
        $data['title'] = _l('billing');
        $data['company_info'] = get_company_subscription();
        $data['subview'] = $this->load->view('companies/billing', $data, TRUE);
        $this->load->view('_layout_open', $data); //page load
    }

    public
    function upgrade()
    {
        $data['title'] = _l('upgrade') . ' ' . _l('plan');
        if (!empty($type)) {
            $data['type'] = $type;
        }
        $data['payment_modes'] = $this->saas_model->get_payment_modes();
        $data['sub_info'] = get_company_subscription();
        $data['subview'] = $this->load->view('settings/upgrade', $data, TRUE);
        $this->load->view('_layout_open', $data); //page load
    }

    public
    function companyHistoryList($id = null)
    {
        // make datatable
        $this->db = config_db(null, true);
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_saas_companies_history';
        $column = array('package_name', 'amount', 'frequency', 'created_at', 'validity', 'payment_method', 'status');
        $this->datatables->column_order = $column;
        $this->datatables->column_search = $column;
        $this->datatables->order = array('id' => 'desc');
        if ($id) {
            $where = array('tbl_saas_companies_history.companies_id' => $id);
        } else {
            $where = array();
        }
        $fetch_data = make_datatables($where, null, true);
        $data = array();
        $access = super_admin_access();
        foreach ($fetch_data as $_key => $v_history) {
            if ($v_history->active == 1) {
                $label = 'success';
                $status = 'active';
            } else {
                $label = 'warning';
                $status = 'inactive';
            }
            if ($v_history->frequency == 'monthly') {
                $frequency = _l('mo');
            } else if ($v_history->frequency == 'lifetime') {
                $frequency = _l('lt');
            } else if ($v_history->frequency == 'yearly') {
                $frequency = _l('yr');
            }
            $action = null;
            $sub_array = array();
            $name = '<a href="' . base_url('subs_package_details/' . $v_history->id . '/1') . '"  data-toggle="modal" data-target="#myModal" >' . $v_history->package_name . '</a>';
            if (!empty($access)) {
                $name .= '<div class="row-options">';
                if (!empty($access) && $v_history->active != 1) {
                    $name .= '<a 
                    data-toggle="tooltip" data-placement="top"
                    href="' . base_url('saas/gb/delete_companies_history/' . $v_history->id) . '"  title="' . _l('delete') . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }
                $name .= '</div>';
            }
            $sub_array[] = $name;
            $sub_array[] = display_money($v_history->amount, default_currency()) . ' /' . $frequency;
            $sub_array[] = _dt($v_history->created_at);
            $sub_array[] = (!empty($v_history->validity) ? $v_history->validity : '-');
            $sub_array[] = $v_history->payment_method;
            if (!empty($access)) {
                $sub_array[] = '<span class="label label-' . $label . '">' . _l($status) . '</span>';
            }
            $data[] = $sub_array;
        }

        render_table_old($data, $where);
    }


    public
    function companyPaymentList($id = null)
    {
        // make datatable
        $this->db = config_db(null, true);
        $this->load->model('datatables');
        $this->datatables->table = 'tbl_saas_companies_payment';
        $this->datatables->join_table = array('tbl_saas_companies', 'tbl_saas_companies_history');
        $this->datatables->join_where = array('tbl_saas_companies.id=tbl_saas_companies_payment.companies_id', 'tbl_saas_companies_history.id=tbl_saas_companies_payment.companies_history_id');

        $column = array('tbl_saas_companies_history.package_name', 'transaction_id', 'total_amount', 'payment_date', 'payment_method');
        $this->datatables->column_order = $column;
        $this->datatables->column_search = $column;
        $this->datatables->order = array('id' => 'desc');
        $this->datatables->select = ('tbl_saas_companies_payment.*,tbl_saas_companies_history.package_name,tbl_saas_companies.name as company_name');
        // select tbl_saas_companies_history.name
        if (!empty($id)) {
            $where = array('tbl_saas_companies_payment.companies_id' => $id);
        } else {
            $where = array();
        }
        $fetch_data = make_datatables($where);
        $access = super_admin_access();
        $data = array();
        foreach ($fetch_data as $_key => $v_history) {
            $action = null;
            $sub_array = array();

            if (!empty($access)) {
                $name = $v_history->company_name;

                $name .= '<div class="row-options">';
                $name .= '<a 
                    data-toggle="tooltip" data-placement="top"
                    href="' . base_url('saas/gb/delete_companies_payment/' . $v_history->id) . '"  title="' . _l('delete') . '" class="text-danger _delete">' . _l('delete') . '</a>';
                $name .= '</div>';
                $sub_array[] = $name;
            }
            $sub_array[] = '<a href="' . base_url('subs_package_details/' . $v_history->companies_history_id . '/1') . '"  data-toggle="modal" data-target="#myModal" >' . $v_history->package_name . '</a>';
            $sub_array[] = $v_history->transaction_id;
            $sub_array[] = display_money($v_history->total_amount, default_currency());
            $sub_array[] = _dt($v_history->payment_date);
            $sub_array[] = $v_history->payment_method;
            $data[] = $sub_array;
        }
        render_table_old($data, $where);
    }

    /**
     * @throws Exception
     */
    public function custom_domain($action = null, $id = null)
    {

        $data['title'] = _l('custom_domain');
        $data['company_info'] = get_company_subscription();
        if (!empty($action)) {
            if (!empty($id)) {
                $data['domain_info'] = get_old_result('tbl_saas_domain_requests', array('request_id' => $id), false);
            }
            if ($action == 'update') {
                // check already exist the domain request
                $where = array('company_id' => $data['company_info']->companies_id, 'status' => 'pending');
                if (!empty($id)) {
                    $where['request_id !='] = $id;
                }

                $check = get_old_result('tbl_saas_domain_requests', $where, false);
                if (!empty($check)) {
                    set_alert('warning', _l('already_request'));
                    redirect('admin/custom_domain');
                }

                $pdata['custom_domain'] = $this->input->post('custom_domain', true);
                $pdata['status'] = 'pending';
                $pdata['company_id'] = $data['company_info']->companies_id;
                $this->saas_model->_table_name = 'tbl_saas_domain_requests';
                $this->saas_model->_primary_key = 'request_id';
                $this->saas_model->save_old($pdata, $id);

                $superadmin = get_old_result(db_prefix() . 'staff', array('admin' => 1, 'role' => 4));
                $users = [];
                foreach ($superadmin as $key => $value) {
                    add_notification([
                        'description' => _l('not_domain_request', $pdata['custom_domain']),
                        'touserid' => $value->staffid,
                        'fromcompany' => true,
                        'link' => 'saas/domain/requests/',
                    ]);
                    $users[] = $value->staffid;
                }
                pusher_trigger_notification(array_unique($users));

                set_alert('success', _l('domain_request_updated_successfully'));
                redirect('admin/custom_domain');
            }
            if ($action == 'delete') {
                if ($data['domain_info']->company_id == $data['company_info']->companies_id) {
                    if ($data['domain_info']->status == 'approved') {
                        $this->saas_model->_table_name = 'tbl_saas_domain_requests';
                        $this->saas_model->_primary_key = 'request_id';
                        $this->saas_model->delete_old($id);

                        $this->saas_model->_table_name = 'tbl_saas_companies';
                        $this->saas_model->_primary_key = 'id';
                        $this->saas_model->save_old(array('domain_url' => ''), $data['company_info']->companies_id);


                    } else {
                        $this->saas_model->_table_name = 'tbl_saas_domain_requests';
                        $this->saas_model->_primary_key = 'request_id';
                        $this->saas_model->delete_old($id);
                    }
                    set_alert('success', _l('domain_request_deleted_successfully'));


                } else {
                    set_alert('warning', _l('404_error'));
                }
                redirect('admin/custom_domain');
            }

        }
        $data['action'] = $action;
        $data['id'] = $id;
        $data['all_domain'] = get_old_result('tbl_saas_domain_requests', array('company_id' => $data['company_info']->companies_id));

        $data['subview'] = $this->load->view('domain/custom_domain', $data, TRUE);
        $this->load->view('_layout_open', $data); //page load
    }

    /**
     * @throws Exception
     */
    public function customizePackages()
    {
        $data['title'] = _l('customize_packages');
        $data['companyInfo'] = get_company_subscription();
        if (!empty($data['companyInfo'])) {
            $data['packageInfo'] = get_usages($data['companyInfo']);
            $company_id = $data['companyInfo']->companies_id;
            $data['company_id'] = $company_id;
            $data['moduleInfo'] = get_old_result('tbl_saas_package_module');
            $data['payment_modes'] = $this->saas_model->get_payment_modes();
        }
        $data['subview'] = $this->load->view('packages/customize_packages', $data, TRUE);
        $this->load->view('_layout_open', $data); //page load
    }

    public function proceedPayment($payment_method = null)
    {
        $subs_info = get_company_subscription(null, 'running');
        $data = $_POST;
        if (!empty($subs_info) && !empty($data['paymentmode'])) {
            $data['amount'] = $data['total'];
            $data['package_id'] = $subs_info->package_id;
            $data['billing_cycle'] = $subs_info->frequency . '_price';
            $data['expired_date'] = $subs_info->expired_date;

            $data['payment_method'] = $data['paymentmode'];
            $data['i_have_read_agree'] = 'on';
            $new_limit = $this->input->post('new_limit', true);
            $new_module = $this->input->post('new_module', true);
            if (!empty($new_module)) {
                $data['new_module'] = serialize($new_module);
            }
            if (!empty($new_limit)) {
                $data['new_limit'] = serialize($new_limit);
            }

            $payment_modes = $this->saas_model->get_payment_modes();
            $modes = array();
            foreach ($payment_modes as $mode) {
                $modes[$mode['id']] = $mode['name'];
            }
            $checkTemp = get_old_result('tbl_saas_temp_payment', array('companies_id' => $data['companies_id']), false);
            $pData = array();
            if (empty($checkTemp)) {
                $companyInfo = get_old_result('tbl_saas_companies', array('id' => $data['companies_id']), false);
                $c_data = array();
                $c_data['company'] = $companyInfo->name;
                $c_data['vat'] = null;
                $c_data['phonenumber'] = null;
                $c_data['website'] = null;
                $c_data['default_currency'] = 0;
                $c_data['default_language'] = null;
                $c_data['address'] = null;
                $c_data['city'] = null;
                $c_data['state'] = null;
                $c_data['zip'] = null;
                $c_data['country'] = 0;
                $c_data['billing_country'] = 0;
                $c_data['billing_street'] = null;
                $c_data['billing_city'] = null;
                $c_data['billing_zip'] = null;
                $c_data['billing_state'] = null;
                $c_data['shipping_country'] = 0;
                $c_data['shipping_street'] = null;
                $c_data['shipping_city'] = null;
                $c_data['shipping_state'] = null;
                $c_data['shipping_zip'] = null;
                $c_data['show_primary_contact'] = 0;
                $c_data['registration_confirmed'] = 1;

                $c_data['datecreated'] = date('Y-m-d H:i:s');
                $c_data['addedfrom'] = is_staff_logged_in() ? get_staff_user_id() : 0;

                $this->saas_model->_table_name = db_prefix() . 'clients';
                $this->saas_model->_primary_key = 'userid';
                $client_id = $this->saas_model->save($c_data);


                $i_data['clientid'] = $client_id;
                $i_data['number'] = get_option('next_invoice_number');
                $i_data['project_id'] = 0;
                $i_data['include_shipping'] = 0;
                $i_data['discount_type'] = '';
                $i_data['date'] = date('Y-m-d');
                $i_data['duedate'] = date('Y-m-d');
                $i_data['allowed_payment_modes'] = serialize(array_keys($modes));
                $i_data['currency'] = get_base_currency()->id;
                $i_data['sale_agent'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
                $i_data['subtotal'] = $data['amount'];
                $i_data['total'] = $data['amount'];
                $i_data['prefix'] = 'SaaS ' . get_option('invoice_prefix');
                $i_data['number_format'] = get_option('invoice_number_format');
                $i_data['datecreated'] = date('Y-m-d H:i:s');
                $i_data['addedfrom'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
                $i_data['hash'] = app_generate_hash();
                $this->saas_model->_table_name = db_prefix() . 'invoices';
                $this->saas_model->_primary_key = 'id';
                $invoice_id = $this->saas_model->save($i_data);


                $item_data = array();
                $item_data['description'] = 'Subscription';
                $item_data['long_description'] = 'Subscription';
                $item_data['qty'] = 1;
                $item_data['rate'] = number_format($data['amount'], get_decimal_places(), '.', '');
                $item_data['rel_id'] = $invoice_id;
                $item_data['rel_type'] = 'invoice';


                $this->saas_model->_table_name = db_prefix() . 'itemable';
                $this->saas_model->_primary_key = 'id';
                $this->saas_model->save($item_data);

                $temp_data = array();
                $temp_data['companies_id'] = $data['companies_id'];
                $temp_data['invoice_id'] = $invoice_id;
                $temp_data['package_id'] = $data['package_id'];
                $temp_data['billing_cycle'] = $data['billing_cycle'];
                $temp_data['expired_date'] = $data['expired_date'];
                $temp_data['coupon_code'] = $data['coupon_code'] ?? '';
                $temp_data['amount'] = $data['amount'];
                $temp_data['clientid'] = $client_id;
                $temp_data['hash'] = $i_data['hash'];
                $temp_data['new_module'] = $data['new_module'] ?? '';
                $temp_data['new_limit'] = $data['new_limit'] ?? '';

                $this->saas_model->_table_name = 'tbl_saas_temp_payment';
                $this->saas_model->_primary_key = 'temp_payment_id';
                $this->saas_model->save_old($temp_data);
                $pData['hash'] = $i_data['hash'];
            } else {
                $invoice_id = $checkTemp->invoice_id;
                $pData['hash'] = $checkTemp->hash;
                if ($checkTemp->package_id != $data['package_id']) {
                    $ds_data['package_id'] = $data['package_id'];
                    $ds_data['expired_date'] = $data['expired_date'];
                    $ds_data['package_id'] = $data['package_id'];
                    $ds_data['billing_cycle'] = $data['billing_cycle'];
                    $ds_data['new_module'] = $data['new_module'] ?? '';
                    $ds_data['new_limit'] = $data['new_limit'] ?? '';
                    $ds_data['amount'] = $data['amount'];
                    $this->saas_model->_table_name = 'tbl_saas_temp_payment';
                    $this->saas_model->_primary_key = 'temp_payment_id';
                    $this->saas_model->save_old($ds_data, $checkTemp->temp_payment_id);
                }
            }

            $pData['paymentmode'] = $data['paymentmode'];
            $pData['amount'] = $data['amount'];
            $pData['make_payment'] = 'Pay Now';
            // set session for payment
            $this->session->set_userdata('saas_payment_data', $data);

            $this->load->model('payments_model');
            $this->payments_model->process_payment($pData, $invoice_id);
        } else {
            set_alert('warning', _l('select_payment_method'));
            redirect('admin/customizePackages');
        }
    }

    public function get_expired_date($package_type)
    {
        $type_title = str_replace('_price', '', $package_type);
        if ($type_title == 'lifetime') {
            $renew_date = date('Y-m-d', strtotime('+100 year'));
        } elseif ($type_title == 'yearly') {
            $renew_date = date('Y-m-d', strtotime('+1 year'));
        } else {
            $renew_date = date('Y-m-d', strtotime('+1 month'));
        }
        return $renew_date;
    }


}
