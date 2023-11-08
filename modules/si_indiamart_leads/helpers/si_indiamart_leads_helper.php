<?php
defined('BASEPATH') or exit('No direct script access allowed');

function convert_indiamart_leads_cron_run()
{
	$CI = &get_instance();
	##$CI->load->model('leads_model');
	##get time from and to, to get leads 
	$last_run = strtotime(get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_last_run'));
	$from_date = date('d-M-YH:i:s',$last_run-(60*2));##getting 2 minutes before previous run, not to miss any lead
	$now = time();
	$to_date = date('d-M-YH:i:s',$now);
	
	$result = $CI->si_indiamart_leads_model->fetch_leads_from_indiamart($from_date,$to_date);
	$count=0;
	if($result['success'] && !empty($result['data'])){
		foreach($result['data'] as $lead){
			##$CI->leads_model->add($lead);
			$CI->db->insert(db_prefix() . 'leads', $lead);
			$insert_id = $CI->db->insert_id();
			hooks()->do_action('lead_created', $insert_id);
			$count++;
		}
		$CI->si_indiamart_leads_model->add_activity_log(_l('si_iml_success_activity_log_text',$count));	
	}elseif(!$result['success']){
		$CI->si_indiamart_leads_model->add_activity_log($result['message']);
	}
	update_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_last_run',date('Y-m-d H:i:s',$now));
}

