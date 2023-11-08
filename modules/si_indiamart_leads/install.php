<?php defined('BASEPATH') or exit('No direct script access allowed');

if(!$CI->db->table_exists(db_prefix() . 'si_iml_activity_log')) {
	$CI->db->query('CREATE TABLE `' . db_prefix() . "si_iml_activity_log` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`description` text NOT NULL,
	`dateadded` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

## Add column in leads table
if (!$CI->db->field_exists('si_indiamart_lead_id', db_prefix() . 'leads')) {
	$CI->db->query('ALTER TABLE `' . db_prefix() . 'leads` ADD `si_indiamart_lead_id` VARCHAR(50) NOT NULL DEFAULT "" COMMENT "indiamart lead id"');
}

add_option(SI_INDIAMART_MODULE_NAME.'_activated',0);
add_option(SI_INDIAMART_MODULE_NAME.'_activation_code','');
add_option(SI_INDIAMART_MODULE_NAME.'_api_key','');
add_option(SI_INDIAMART_MODULE_NAME.'_trigger_auto_retry_enable',1);##enable or not automatic fetch leads
add_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_minutes',6);##every 6 minute
add_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_last_run',date('Y-m-d H:i:s',strtotime('-1 hour')));
add_option(SI_INDIAMART_MODULE_NAME.'_lead_assigned','');
add_option(SI_INDIAMART_MODULE_NAME.'_lead_source','');
add_option(SI_INDIAMART_MODULE_NAME.'_lead_status','');
add_option(SI_INDIAMART_MODULE_NAME.'_delete_activity_log_older_then',1);
