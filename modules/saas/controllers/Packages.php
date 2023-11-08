<?php defined('BASEPATH') or exit('No direct script access allowed');

class Packages extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('saas_model');
        saas_access();
    }

    public function index()
    {
        $data['title'] = 'Packages - Make Package';
        $data['active'] = 1;
        $data['subview'] = $this->load->view('packages/manage', $data, true);
        $this->load->view('_layout_main', $data);
    }

    public function packagesList($status = null)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('datatables');
            $this->datatables->table = 'tbl_saas_packages';
            $this->datatables->select = 'tbl_saas_packages.*,(SELECT COUNT(id) FROM tbl_saas_companies WHERE package_id=tbl_saas_packages.id) as total_companies';
            $column = array('name', 'trail_period', 'recommended', 'status', 'monthly_price', 'yearly_price', 'lifetime_price');
            $this->datatables->column_order = $column;
            $this->datatables->column_search = $column;
            $this->datatables->order = array('tbl_saas_packages.id' => 'desc');
            $where = array();
            if (!empty($status)) {
                $where = array('tbl_saas_packages.status' => $status);
            }
            $fetch_data = make_datatables($where);
            $data = array();

            $access = super_admin_access();
            foreach ($fetch_data as $key => $row) {
                $sub_array = array();
                $name = null;
                $name .= '<a href="' . base_url() . 'package_details/' . $row->id . '" title="' . _l('details') . '" data-toggle="modal" data-target="#myModal">' . $row->name . '</a>  ';
                // count total companies in this package
                $total_companies = $row->total_companies;
                $name .= '<br> <small class="text-muted">' . _l('companies') . ': ' . $total_companies . '</small>';

                $name .= '<div class="row-options">';
                if (!empty($access)) {
                    $name .= '<a href="' . base_url() . 'saas/packages/create/' . $row->id . '" title="' . _l('edit') . '">' . _l('edit') . '</a>  ';
                }
                $name .= '| <a href="' . base_url() . 'package_details/' . $row->id . '" title="' . _l('details') . '" data-toggle="modal" data-target="#myModal">' . _l('details') . '</a>  ';
                if (!empty($access)) {
                    $name .= '| <a href="' . base_url() . 'saas/packages/delete_packages/' . $row->id . '" title="' . _l('delete') . '" class="text-danger _delete">' . _l('delete') . '</a>';
                }
                $name .= '</div>';

                $sub_array[] = $name;
                $sub_array[] = $row->trial_period . ' ' . _l('days');
                $sub_array[] = package_price($row, 'row');

                $sub_array[] = ($row->recommended == 'Yes') ? '<span class="label label-success">Yes</span>' : '<span class="label label-danger">No</span>';
                if (!empty($access)) {
                    $checked = ($row->status == 'published') ? 'checked' : '';
                    $sub_array[] = '<div class="onoffswitch"><input type="checkbox"
                    data-id="' . $row->id . '"
                    data-switch-url="' . admin_url() . 'saas/packages/change_package_status" 
    id="onoffswitch_' . $row->id . '" class="onoffswitch-checkbox status" ' . $checked . ' /><label for="onoffswitch_' . $row->id . '" class="onoffswitch-label"></label></div>';
                } else {
                    $sub_array[] = $row->status == 'published' ? '<span class="label label-success">' . _l('published') . '</span>' : '<span class="label label-danger">' . _l('unpublished') . '</span>';
                }
                $data[] = $sub_array;
            }
            render_table($data);
        } else {
            redirect('saas/dashboard');
        }
    }

    public function change_package_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->where('id', $id);
            $this->db->update('tbl_saas_packages', array('status' => $status == 1 ? 'published' : 'unpublished'));
            log_activity('Package Status Changed [ID:' . $id . ', Status' . $status . ']');
            set_alert('success', _l('updated_successfully', _l('package')));
            echo json_encode(array('success' => true));
            die;
        }
    }

    public function create($id = null)
    {

        $data['title'] = _l('create_package');
        $data['active'] = 2;
        if (!empty($id)) {
            $data['package_info'] = get_row('tbl_saas_packages', array('id' => $id));
            $data['title'] = 'Packages - Edit Package';
        }
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', ['expenses_only !=' => 1]);
        $data['modules'] = $this->app_modules->get();
        $data['subview'] = $this->load->view('packages/create', $data, true);
        $this->load->view('_layout_main', $data);
    }

    public function save_packages($id = null)
    {

        $data = $this->saas_model->array_from_post(array(
            'name', 'monthly_price', 'yearly_price', 'lifetime_price',
            'trial_period', 'description', 'status', 'allowed_payment_modes', 'modules', 'allowed_themes'
        ));
        $data['allowed_payment_modes'] = isset($data['allowed_payment_modes']) ? serialize($data['allowed_payment_modes']) : serialize([]);
        $data['modules'] = isset($data['modules']) ? serialize($data['modules']) : serialize([]);
        $data['allowed_themes'] = isset($data['allowed_themes']) ? serialize($data['allowed_themes']) : serialize([]);
        $all_field = get_order_by('tbl_saas_package_field', array('status' => 'active'), 'order', 'asc');
        if (!empty($all_field)) {
            foreach ($all_field as $key => $field) {
                $field_name = $field->field_name;
                if ($field->field_type == 'text') {
                    $additional_field = 'additional_' . $field_name;
                    $data[$additional_field] = $this->input->post($additional_field, true) ? $this->input->post($additional_field, true) : NULL;
                }
                $data[$field_name] = $this->input->post($field_name, true);
            }
        }

        $recommended = $this->input->post('recommended', true);
        $update_all_company_packages = $this->input->post('update_all_company_packages', true);

        if (!empty($update_all_company_packages)) {
            $all_company = $this->saas_model->select_data('tbl_saas_companies', 'tbl_saas_companies.*,tbl_saas_companies_history.package_name,tbl_saas_companies_history.id as company_history_id', NULL, array('tbl_saas_companies.package_id' => $id, 'tbl_saas_companies_history.active' => 1), ['tbl_saas_companies_history' => 'tbl_saas_companies.id = tbl_saas_companies_history.companies_id'], 'result');

            if (!empty($all_company)) {
                foreach ($all_company as $key => $company) {
                    $pdata = $data;
                    $pdata['package_id'] = $id;
                    $this->saas_model->update_company_history($pdata, $company['company_history_id']);
                }
                $this->db = config_db(null, true);
            }
        }
        if (!empty($recommended)) {
            $data['recommended'] = $recommended;
            // remove recommended from other packages
            $this->db->where('recommended', 'Yes');
            $this->db->update('tbl_saas_packages', ['recommended' => 'No']);
        } else {
            $data['recommended'] = 'No';
        }
        $this->saas_model->_table_name = "tbl_saas_packages"; // table name
        $this->saas_model->_primary_key = "id"; // $id
        $this->saas_model->save($data, $id);


        // messages for user
        set_alert('success', _l('added_successfully', _l('package')));
        redirect('saas/packages');
    }


    public function package_details($id)
    {

        $data['title'] = 'Packages - Package Details';
        $data['package'] = get_row('tbl_saas_packages', array('id' => $id));
        $data['subview'] = $this->load->view('packages/package_details', $data, true);
        $this->load->view('_layout_main', $data);
    }

    public function delete_packages($id)
    {
        $package = get_row('tbl_saas_packages', array('id' => $id));
        log_activity('Package Deleted [ID:' . $id . ', Name' . $package->name . ']');

        $this->saas_model->_table_name = 'tbl_saas_packages';
        $this->saas_model->_primary_key = 'id';
        $this->saas_model->delete($id);

        // messages for user
        $type = "success";
        $message = _l('package_deleted');
        set_alert($type, $message);
        redirect('saas/packages');
    }

    public function customize($company_id = null)
    {
        $data['title'] = 'Customize Package';
        $companies_id = $this->input->post('companies_id', true);
        if (!empty($companies_id)) {
            $company_id = $companies_id;
        }
        if (!empty($company_id)) {
            $data['company_id'] = $company_id;
            $data['companyInfo'] = $this->saas_model->company_info($company_id, true);
            $data['packageInfo'] = get_usages($data['companyInfo']);
            $data['moduleInfo'] = get_old_result('tbl_saas_package_module');
        }
        $data['subview'] = $this->load->view('packages/customize', $data, true);
        $this->load->view('_layout_main', $data);
    }

    public function customize_package()
    {
        $new_limit = $this->input->post('new_limit', true);
        $new_module = $this->input->post('new_module', true);
        $company_id = $this->input->post('companies_id', true);
        $company_history_id = $this->input->post('company_history_id', true);
        $discount_percent = $this->input->post('discount_percent', true);
        $subtotal = $this->input->post('subtotal', true);
        $discount_type = $this->input->post('discount_total_type_selected', true);

        $total = $this->input->post('total', true);
        $companyInfo = $this->saas_model->company_info($company_id, true);

        $post = array();
        foreach ($new_limit as $key => $limit) {
            if (!empty($limit)) {
                if ($key === 'disk_space') {
                    $old_disk_space = $companyInfo->$key; // 1GB
                    // convert GB to byte and add new limit
                    $old_disk_space = convertGBToBytes($old_disk_space) + $limit * 1024 * 1024;
                    $post[$key] = convertSize($old_disk_space, 2);
                } else {
                    $post[$key] = $companyInfo->$key + $limit;
                }

            }
        }
        if (!empty($new_module)) {
            $old_module = $companyInfo->modules ? unserialize($companyInfo->modules) : [];
            // switch $new_module key and value and reset array key
            $new_module = array_flip($new_module);
            $new_module = array_values($new_module);
            // add the new module with old module if not exist
            $post['modules'] = serialize(array_unique(array_merge($old_module, $new_module)));
        }
        $post['package_id'] = $companyInfo->package_id;

        $companies_history_id = $this->saas_model->update_company_history($post, $company_history_id);
        if (!empty($companies_history_id)) {
            $pdata = array(
                'package_id' => $companyInfo->package_id,
                'billing_cycle' => $companyInfo->frequency,
                'is_coupon' => null,
                'coupon_code' => null,
                'reference_no' => 'SAAS-CPP- ' . date('Ymd') . '-' . rand(100000, 999999),
                'companies_history_id' => $company_history_id,
                'companies_id' => $company_id,
                'transaction_id' => 'TRN' . date('Ymd') . date('His') . '_' . substr(number_format(time() * rand(), 0, '', ''), 0, 6),
                'payment_method' => 'manual',
                'subtotal' => $subtotal,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_type == '%' ? ($subtotal * $discount_percent) / 100 : $discount_percent,
                'amount' => $total,
            );

            $this->saas_model->packagePayment($pdata, $company_history_id);
        }
        set_alert('success', _l('package_customized'));
        redirect('saas/companies/details/' . $company_id);


    }

    public
    function settings($active = null)
    {
        if (empty($active)) {
            $data['active'] = 'fields';
        } else {
            $data['active'] = $active;
        }
        $data['title'] = _l('saas_settings') . ' - ' . _l($data['active']);
        $data['all_tabs'] = $this->package_tabs();
        if ($data['active'] == 'fields') {
            $data['menu_items'] = $this->package_fields();
        } else {
            $data['modules'] = $this->app_modules->get();
            $data['update_url'] = 'saas/packages/update_modules';
            $data['moduleInfo'] = get_old_result('tbl_saas_package_module');
            // set array key as module system name
            $newModuleInfo = [];
            foreach ($data['moduleInfo'] as $val) {
                $newModuleInfo[$val->module_name] = $val->price;
            }
            $data['moduleInfo'] = $newModuleInfo;
        }
        $data['subview'] = $this->load->view('saas/settings/tab_view', $data, TRUE);
        $this->load->view('_layout_main', $data);
    }

    public
    function package_fields(): array
    {
        $menu_items = get_order_by('tbl_saas_package_field', null, 'order', 'asc');
        $menu = [];
        foreach ($menu_items as $item) {
            $menu[$item->field_label] = [
                'slug' => $item->field_id,
                'name' => $item->field_label,
                'position' => $item->order,
                'disabled' => $item->status == 'inactive' ? 'true' : 'false',
                'children' => [],
            ];
        }
        return $menu;
    }

    public
    function package_tabs(): array
    {
        $url = 'saas/packages/settings/';
        $tab = array(
            'fields' => [
                'position' => 1,
                'name' => 'settings_group_fields',
                'url' => $url . 'fields',
                'count' => '',
                'icon' => 'fa fa-list',
                'view' => $url . 'fields',
            ],
            'modules' => [
                'position' => 2,
                'name' => 'settings_group_modules',
                'url' => $url . 'modules',
                'count' => '',
                'icon' => 'fa fa-puzzle-piece',
                'view' => $url . 'modules',
            ],
        );
        return $tab;
    }

    public
    function update_package_field()
    {
        $options = $this->input->post('options');
        foreach ($options as $val) {
            if (isset($val['children'])) {
                $newChild = [];
                foreach ($val['children'] as $keyChild => $child) {
                    $newChild[$child['id']] = $child;
                }
                $val['children'] = $newChild;
            }
            $data['status'] = $val['disabled'] == 'true' ? 'inactive' : 'active';
            $data['order'] = $val['position'];
            $this->db->where('field_id', $val['id']);
            $this->db->update('tbl_saas_package_field', $data);
        }

    }

    public
    function update_modules()
    {
        $modules = $this->input->post('modules');
        $this->db->truncate('tbl_saas_package_module');
        if (!empty($modules)) {
            foreach ($modules as $key => $module) {
                if (empty($module)) {
                    continue;
                }
                $data['module_name'] = $key;
                $data['price'] = $module;
                $this->db->insert('tbl_saas_package_module', $data);
            }
        }
        set_alert('success', _l('module_price_updated'));
        redirect('saas/packages/settings/modules');
    }


}
