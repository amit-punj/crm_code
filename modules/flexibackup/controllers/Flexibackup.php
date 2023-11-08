<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexibackup extends AdminController {
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('FlexiBackup');
        }
    }


    public function index()
    {
        $data['title'] = _l('flexibackup_existing_backups');
        $this->load->model('flexibackup/flexibackup_model');
        $data['backups'] = $this->flexibackup_model->get_backups();
        $this->app_css->add('flexibackup-tree-css', module_dir_url('flexibackup', 'assets/css/jstree.min.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexibackup-tree-js', module_dir_url('flexibackup', 'assets/js/jstree.min.js'), 'admin', ['app-js']);
        $this->app_scripts->add('flexibackup-js', module_dir_url('flexibackup', 'assets/js/flexibackup.js'), 'admin', ['app-js']);
        $this->load->view('flexibackup', $data);
    }

    public function ajax(){
        $action = $this->input->get('action');
        $result['success'] = false;
        switch ($action){
            case 'file_action_view':
                $id = $this->input->get('id');
                $type = $this->input->get('type');
                $this->load->library('flexibackup/flexibackup_module');
                $this->load->model('flexibackup/flexibackup_model');
                $backup = $this->flexibackup_model->get_backup($id);
                $files = $this->flexibackup_module->get_backup_file($type,$backup,true);
                $result['html'] = $this->load->view('partials/file-action-view',['id'=>$id,'type'=>$type,'backup'=>$backup],true);
                $result['success'] = true;
                $result['files'] = ($type == 'database') ? [] : $files;
                break;

            case 'view_log_file':
                $id = $this->input->get('id');
                $this->load->model('flexibackup/flexibackup_model');
                $backup = $this->flexibackup_model->get_backup($id);
                $file = FCPATH.'flexibackup/'.$backup->backup_name.'/log.txt';
                if(file_exists($file)) {
                    $result['html'] = $this->load->view('partials/view-log',['id'=>$id,'backup'=>$backup,'file'=>$file],true);
                }else{
                    $result['html'] = _l('flexibackup_log_file_not_found');
                }
                $result['success'] = true;
                break;

           case 'restore_backup':
                $id = $this->input->get('id');
                $this->load->model('flexibackup/flexibackup_model');
                $backup = $this->flexibackup_model->get_backup($id);
                $file_types = ($backup->backup_type == "database") ? array('database') :  explode(',',$backup->backup_data);
                $result['html'] = $this->load->view('partials/restore-backup-options',['id'=>$id,'backup'=>$backup,'file_types'=>$file_types],true);
                $result['success'] = true;
                break;

        }
        header('Content-Type: application/json');
        echo json_encode( $result );
    }
    public function settings()
    {
        if ($_post = $this->input->post()) {
            $remote_storage = $_post['settings']['flexibackup_remote_storage'];
            $updated_1 = update_option('flexibackup_files_backup_schedule', $_post['settings']['flexibackup_files_backup_schedule']);
            $updated_2 = update_option('flexibackup_database_backup_schedule', $_post['settings']['flexibackup_database_backup_schedule']);
            $updated_3 = update_option('flexibackup_remote_storage', $remote_storage);
            $updated_4 = update_option('flexibackup_include_modules', $_post['settings']['flexibackup_include_modules'] ?? 0);
            $update_5 =  update_option('flexibackup_include_application', $_post['settings']['flexibackup_include_application'] ?? 0);
            $update_6 =  update_option('flexibackup_include_uploads', $_post['settings']['flexibackup_include_uploads'] ?? 0);
            $update_7 =  update_option('flexibackup_include_assets', $_post['settings']['flexibackup_include_assets'] ?? 0);
            $update_8 =  update_option('flexibackup_include_system', $_post['settings']['flexibackup_include_system'] ?? 0);
            $update_9 =  update_option('flexibackup_include_resources', $_post['settings']['flexibackup_include_resources'] ?? 0);
            $update_10 = update_option('flexibackup_include_media', $_post['settings']['flexibackup_include_media'] ?? 0);
            $update_11 = update_option('flexibackup_include_others', $_post['settings']['flexibackup_include_others'] ?? 0);
            $update_12 = update_option('flexibackup_auto_backup_to_remote_enabled', $_post['settings']['flexibackup_auto_backup_to_remote_enabled'] ?? 0);
            $update_13 = update_option('flexibackup_auto_backup_time', $_post['settings']['flexibackup_auto_backup_time']);
            update_option('flexibackup_backup_name_prefix', $_post['settings']['flexibackup_backup_name_prefix'] ?? 'backup');
            //lets saved settings of each remote storage
            switch ($remote_storage){
                case 'ftp':
                    update_option('flexibackup_ftp_server', $_post['flexibackup_ftp_server']);
                    update_option('flexibackup_ftp_user', $_post['flexibackup_ftp_user']);
                    update_option('flexibackup_ftp_password', $_post['flexibackup_ftp_password']);
                    update_option('flexibackup_ftp_path', $_post['flexibackup_ftp_path']);
                    break;

                case 'sftp':
                    update_option('flexibackup_sftp_server', $_post['flexibackup_sftp_server']);
                    update_option('flexibackup_sftp_user', $_post['flexibackup_sftp_user']);
                    update_option('flexibackup_sftp_password', $_post['flexibackup_sftp_password']);
                    update_option('flexibackup_sftp_path', $_post['flexibackup_sftp_path']);
                    break;

                case 's3':
                    update_option('flexibackup_s3_access_key', $_post['flexibackup_s3_access_key']);
                    update_option('flexibackup_s3_secret_key', $_post['flexibackup_s3_secret_key']);
                    update_option('flexibackup_s3_location', $_post['flexibackup_s3_location']);
                    update_option('flexibackup_s3_region', $_post['flexibackup_s3_region']);
                    break;
                case 'webdav':
                    update_option('flexibackup_webdav_username', $_post['flexibackup_webdav_username']);
                    update_option('flexibackup_webdav_password', $_post['flexibackup_webdav_password']);
                    update_option('flexibackup_webdav_base_uri', $_post['flexibackup_webdav_base_uri']);
                    break;
                case 'email':
                    update_option('flexibackup_email_address', $_post['flexibackup_email_address']);
                    break;
            }


            if ($updated_2 || $updated_1 || $updated_3 || $updated_4 || $update_5 || $update_6 || $update_7 || $update_8 || $update_9 || $update_10) {
                set_alert('success', _l('flexibackup_auto_backup_options_updated'));
            }
            redirect(admin_url('flexibackup/settings'));
        }
        $data['title'] = _l('flexibackup_settings');
        $this->app_scripts->add('flexibackup-js', module_dir_url('flexibackup', 'assets/js/flexibackup.js'), 'admin', ['app-js']);
        $this->app_css->add('flexibackup-css', module_dir_url('flexibackup', 'assets/css/flexibackup.css'), 'admin', ['app-css']);
        $this->load->view('settings', $data);
    }
    public function schedule_backup()
    {
        $this->load->library('flexibackup/flexibackup_module');
        $data['title'] = _l('flexibackup_next_scheduled_backup');
        $data['database_backup_schedule'] = $this->flexibackup_module->get_next_scheduled_backup('database') ?? "";;
        $data['files_backup_schedule'] = $this->flexibackup_module->get_next_scheduled_backup('file') ?? "";
        $data['time_now'] = $this->flexibackup_module->get_time_now();
        $this->app_scripts->add('flexibackup-js', module_dir_url('flexibackup', 'assets/js/flexibackup.js'), 'admin', ['app-js']);
        $this->load->view('scheduled-backup', $data);
    }

    public function backup_now(){
        //if form is submitted
        if($_post = $this->input->post()){
            $backup_db = isset($_post['flexibackup_include_database_in_the_backup']) ? $_post['flexibackup_include_database_in_the_backup'] : 0;
            $backup_files = isset($_post['flexibackup_include_file_in_the_backup']) ? $_post['flexibackup_include_file_in_the_backup'] : 0;
            $options = [
                'file' => (bool)$backup_files,
                'database' => (bool)$backup_db
            ];
            $file_settings['modules'] = $_post['settings']['flexibackup_include_modules'] ?? 0;
            $file_settings['application'] = $_post['settings']['flexibackup_include_application']?? 0;
            $file_settings['uploads'] = $_post['settings']['flexibackup_include_uploads'] ?? 0;
            $file_settings['assets'] = $_post['settings']['flexibackup_include_assets'] ?? 0;
            $file_settings['system'] = $_post['settings']['flexibackup_include_system'] ?? 0;
            $file_settings['resources'] = $_post['settings']['flexibackup_include_resources'] ?? 0;
            $file_settings['media'] = $_post['settings']['flexibackup_include_media'] ?? 0;
            $file_settings['others'] = $_post['settings']['flexibackup_include_others'] ?? 0;

            $this->load->library('flexibackup/flexibackup_module');
            $success = $this->flexibackup_module->perform_backup($options,$file_settings);
            if ($success) {
                set_alert('success', _l('flexibackup_successful'));
            }
        }
        redirect(admin_url('flexibackup'));
    }

    public function download_backup($id,$type){
        $this->load->model('flexibackup/flexibackup_model');
        $backup = $this->flexibackup_model->get_backup($id);
        //if backup is database, it is sql, if it is log, it is txt, if it is file, it is zip
        if($type == "database"){
            $ext = "sql";
        }elseif($type == "log"){
            $ext = "txt";
        }else{
            $ext = "zip";
        }
        $path = FCPATH.'flexibackup/'.$backup->backup_name.'/'.$type.'.'.$ext;
        $this->load->helper('download');
        if (file_exists($path)) {
            force_download($path, null);
        } else {
            set_alert('warning', _l('flexibackup_could_not_donwload_file'));
            redirect(admin_url('flexibackup'));
        }
    }

    public function delete_backup($id,$type){
        //if backup is database, it is sql, if it is log, it is txt, if it is file, it is zip
        $this->load->library('flexibackup/flexibackup_module');
        $success = $this->flexibackup_module->delete_backup($id,$type);
        if($success){
            set_alert('success', _l('flexibackup_file_removed_successfully'));
        }else{
            set_alert('warning', _l('flexibackup_could_not_remove_backup'));
        }
        redirect(admin_url('flexibackup'));
    }

    public function upload_to_remote($id){
        $remote = get_option("flexibackup_remote_storage");
        if($remote){
            $this->load->library('flexibackup/flexibackup_module');
            $backup_response = $this->flexibackup_module->upload_backup_to_remote($id);
            $success = $backup_response['status'];
            if($success && $backup_response['message'] == ''){
                set_alert('success', _l("flexibackup_files_uploaded_to").$remote .' '. _l("flexibackup_successfully"));
            }else{
                set_alert('warning', _l("flexibackup_could_not_complete_remote_backup").$backup_response['message']);
            }
        }else{
            set_alert('warning', _l("flexibackup_no_remote_storage_selected"));
        }
        redirect(admin_url('flexibackup'));
    }

    public function restore_backup(){
        if($_post = $this->input->post()){
            $backup_id = $_post['backup_id'];
            $file_types = $_post['restore_files'];
            if($file_types){
                $this->load->library('flexibackup/flexibackup_module');
                $result = $this->flexibackup_module->restore_backup($backup_id,$file_types);
                $success = $result['status'];
                if($success){
                    set_alert('success', _l("flexibackup_backup_restored_successfully"));
                }else{
                    set_alert('warning', _l("flexibackup_could_not_restore_backup").$result['message']);
                }
            }else{
                set_alert('warning', _l("flexibackup_at_least_one_file_type_to_restore"));
            }
        }
        redirect(admin_url('flexibackup'));
    }

}