<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Indiamart Leads Integration 
Description: Integrate Indiamart Leads into Perfex Leads
Version: 1.0.1
Requires at least: 2.3.*
Author: Sejal Infotech
Author URI: http://www.sejalinfotech.com
*/

define('SI_INDIAMART_MODULE_NAME', 'si_indiamart_leads');
define('SI_INDIAMART_VALIDATION_URL','http://www.sejalinfotech.com/perfex_validation/index.php');
define('SI_INDIAMART_KEY','c2lfaW5kaWFtYXJ0X2xlYWRz');

$CI = &get_instance();
hooks()->add_action('admin_init', 'si_indiamart_leads_hook_admin_init');
hooks()->add_filter('module_'.SI_INDIAMART_MODULE_NAME.'_action_links', 'module_si_indiamart_leads_action_links');
hooks()->add_action('settings_tab_footer','si_indiamart_leads_hook_settings_tab_footer');#for perfex low version V2.4 
hooks()->add_action('settings_group_end','si_indiamart_leads_hook_settings_tab_footer');#for perfex high version V2.8.4
register_cron_task('si_iml_hook_after_cron_run');

/**
* Load the module helper
*/
$CI->load->helper(SI_INDIAMART_MODULE_NAME . '/si_indiamart_leads');

/**
* Load the module model
*/
$CI->load->model(SI_INDIAMART_MODULE_NAME . '/si_indiamart_leads_model');

/**
* Register activation module hook
*/
register_activation_hook(SI_INDIAMART_MODULE_NAME, 'si_indiamart_leads_activation_hook');
function si_indiamart_leads_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}
/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SI_INDIAMART_MODULE_NAME, [SI_INDIAMART_MODULE_NAME]);

function module_si_indiamart_leads_action_links($actions)
{
	if(get_option(SI_INDIAMART_MODULE_NAME.'_activated') && get_option(SI_INDIAMART_MODULE_NAME.'_activation_code')!='')
		$actions[] = '<a href="' . admin_url('settings?group=si_indiamart_leads_settings') . '">' . _l('settings') . '</a>';
	else
		$actions[] = '<a href="' . admin_url('settings?group=si_indiamart_leads_settings') . '">' . _l('si_iml_settings_validate') . '</a>';	
	
	return $actions;
}

function si_indiamart_leads_hook_settings_tab_footer($tab)
{
	if($tab['slug']=='si_indiamart_leads_settings' && !get_option(SI_INDIAMART_MODULE_NAME.'_activated')){
		echo '<script src="'.module_dir_url('si_indiamart_leads','assets/js/si_indiamart_leads_settings_footer.js').'"></script>';
	}
}

/**
* Init module menu items in setup
* @return null
*/
function si_indiamart_leads_hook_admin_init()
{
	#Add customer permissions
	/*$capabilities = [];
	$capabilities['capabilities'] = [
		'view'   => _l('permission_view'),
	];
	register_staff_capabilities('si_indiamart_leads', $capabilities, _l('si_indiamart_leads_menu'));*/
	
	$CI = &get_instance();
	/**  Add Tab In Settings Tab of Setup **/
	if (is_admin() || has_permission('settings', '', 'view')) {
		$CI->app_tabs->add_settings_tab('si_indiamart_leads_settings', [
			'name'     => _l('si_indiamart_leads_settings'),
			'view'     => 'si_indiamart_leads/si_indiamart_leads_settings',
			'position' => 60,
		]);
	}
}

/*hook to run a cron*/
function si_iml_hook_after_cron_run($manually)
{
	$cron_enable = get_option(SI_INDIAMART_MODULE_NAME.'_trigger_auto_retry_enable');
	if($cron_enable == 1){
		$minutes_auto_operations = get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_minutes');
		$last_run = strtotime(get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_last_run'));
		if ($minutes_auto_operations == '') {
			$minutes_auto_operations = 6;
		}
		$seconds_auto_operations = intval($minutes_auto_operations)*60;##convert to seconds
		$time_now                = time();
		if (($time_now < ($last_run+$seconds_auto_operations)) || $manually === true) {
			return;
		}
		convert_indiamart_leads_cron_run();
	}
	return;
}

