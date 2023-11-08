<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Perfex Multi Theme
Description: Multi Themes for Perfex CRM
Version: 1.0.1
Author: Zonvoir
Author URI: https://zonvoir.com
Requires at least: 2.3.2
*/
define('PERFEX_MULTI_THEME_MODULE_NAME', 'perfex_multi_theme');
hooks()->add_action('app_admin_footer', 'multi_theme_selection_sidebar');
hooks()->add_action('app_admin_head', 'perfex_multi_theme_head_component');
hooks()->add_action('app_admin_authentication_head', 'perfex_multi_theme_staff_login');
$CI = &get_instance();
register_activation_hook(PERFEX_MULTI_THEME_MODULE_NAME, 'perfex_multi_theme_activation_hook');
function perfex_multi_theme_activation_hook()
{
	require(__DIR__ . '/install.php');
}
register_uninstall_hook(PERFEX_MULTI_THEME_MODULE_NAME, 'perfex_multi_theme_uninstall_hook');
function perfex_multi_theme_uninstall_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/uninstall.php');
}
function multi_theme_selection_sidebar()
{
	$CI = &get_instance();
    $CI->load->view('perfex_multi_theme/sidebar');

}
hooks()->add_action('admin_init', 'perfex_multi_theme_module_init_menu_items');
function perfex_multi_theme_module_init_menu_items()
{
    $CI = &get_instance();
    $CI->app->add_quick_actions_link([
        'name'       => _l('vl_video_library'),
        'url'        => 'video_library',
        'permission' => 'video_library',
        'position'   => 52,
    ]);
    if (is_admin()) {
        // The first paremeter is the parent menu ID/Slug
        $CI->app_menu->add_setup_menu_item('perfex_multi_theme_setup', [
            'slug' => 'Video_lib_setup-groups',
            'name' => _l('perfex_multi_theme_settings_first'),
			'href' => admin_url('perfex_multi_theme/main'),
            'position' => 48,
        ]);
       
    }
}
register_language_files(PERFEX_MULTI_THEME_MODULE_NAME, ['perfex_multi_theme']);
$CI->load->helper(PERFEX_MULTI_THEME_MODULE_NAME . '/perfex_theme_multi');