<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Si_indiamart_leads_model extends App_Model
{
	private $indiamart_url = 'https://mapi.indiamart.com/wservce/crm/crmListing/v2/';##api V2
	public function __construct()
	{
		parent::__construct();
		$indiamart_api_key   = trim(get_option(SI_INDIAMART_MODULE_NAME.'_api_key'));
		$this->indiamart_url .= '?glusr_crm_key='.$indiamart_api_key;
	}
	
	function fetch_leads_from_indiamart($start_time='',$end_time='')
	{
		try{
			
			$url = $this->indiamart_url;
			if($start_time!='')
				$url .= '&start_time='.$start_time;
			if($end_time!='')
				$url .= '&end_time='.$end_time;		
			
			$curl = curl_init();
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_URL            => $url,
				
			]);
			$result = curl_exec($curl);
			$error  = '';
			if (!$curl || !$result) {
				$error = 'Curl Error - Contact your hosting provider with the following error as reference: Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
			}
			
			$result = json_decode($result, true);
			
			$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if($code==404)
				$error = 'Server request unavailable, try after sometime.';
			##if fetched from indiamart, but invalid key or some issue	
			if($code==200 && $result['CODE']!=200)
				$error = 'Error:'.$result['CODE'].'- '.$result['MESSAGE'];	
				
			curl_close($curl);
			
			if ($error != '') {
				log_activity('Indiamart Lead Call Error:' .$error);
				return array(
					'success' => false,
					'message'=>$error,
				);
			}
			
			return array(
					'success' => true,
					'message'=>$result['MESSAGE'],
					'data'=>$this->map_leads_with_indiamart_fields($result['RESPONSE']),
				);
			
		}
		catch (Exception $e) {
			log_activity('Indiamart Lead Call Error:' .$e->getMessage());
			return array('success'=>false,'message'=>$e->getMessage());
		}
	}
	
	function map_leads_with_indiamart_fields($data)
	{
		$leads = array();
		foreach($data as $indiamart_field)
		{
			##check if new lead
			if($this->is_duplicate_lead($indiamart_field['UNIQUE_QUERY_ID']))
			 	continue;
			
			##map lead with indiamart fields	
			$country_id = 0;
			$this->db->where('iso2', $indiamart_field['SENDER_COUNTRY_ISO']);
			$this->db->or_where('iso3', $indiamart_field['SENDER_COUNTRY_ISO']);
			$country = $this->db->get(db_prefix().'countries')->row();
			if($country)
				$country_id = $country->country_id;
			
			$fields = array('name'			=> $indiamart_field['SENDER_NAME'],
							'address'		=> $indiamart_field['SENDER_ADDRESS'],
							'city'			=> $indiamart_field['SENDER_CITY'],
							'email'			=> $indiamart_field['SENDER_EMAIL'],
							'state'			=> $indiamart_field['SENDER_STATE'],
							'country'		=> $country_id,
							'phonenumber'	=> $indiamart_field['SENDER_MOBILE'],
							'company'		=> $indiamart_field['SENDER_COMPANY'],
							'description'	=> nl2br($indiamart_field['QUERY_PRODUCT_NAME']."\n".$indiamart_field['QUERY_MESSAGE']),
							'hash' 			=> app_generate_hash() . '-' . app_generate_hash(),
							'assigned'		=> 0,
							'source'		=> get_option(SI_INDIAMART_MODULE_NAME.'_lead_source'),
							'status'		=> get_option(SI_INDIAMART_MODULE_NAME.'_lead_status'),
							'dateadded'   	=> date('Y-m-d H:i:s'),
							'addedfrom'   	=> 0,
							'si_indiamart_lead_id'=>$indiamart_field['UNIQUE_QUERY_ID'],
			);
			if(get_option(SI_INDIAMART_MODULE_NAME.'_lead_assigned')!='')
				$fields['assigned'] = get_option(SI_INDIAMART_MODULE_NAME.'_lead_assigned');
			
			array_push($leads, $fields);	
		}
		return $leads;
	}
	
	function is_duplicate_lead($si_indiamart_lead_id)
	{
		$this->db->where('si_indiamart_lead_id', $si_indiamart_lead_id);
		$lead = $this->db->get(db_prefix().'leads');
		if($lead->num_rows() > 0)
			return true;
		else
			return false;	
	}
	
	function add_activity_log($description)
	{
		$data = array(	'dateadded'	=>	date('Y-m-d H:i:s'),
						'description'	=>	$description,
				);
		
		$this->db->insert(db_prefix() . 'si_iml_activity_log', $data);
	}
}
