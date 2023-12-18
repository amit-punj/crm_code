<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once('vendor/autoload.php');

use app\services\messages\Message;
use app\services\messages\PopupMessage;

function app_admin_head()
{
    hooks()->do_action('app_admin_head');
}

/**
 * @since 2.3.2
 * @return null
 */
function app_admin_footer()
{
    /**
     * @deprecated 2.3.2 Use app_admin_footer instead
     */
    do_action_deprecated('after_js_scripts_render', [], '2.3.2', 'app_admin_footer');

    hooks()->do_action('app_admin_footer');
}

/**
 * @since  1.0.0
 * Init admin head
 * @param  boolean $aside should include aside
 */
function init_head($aside = true)
{
    $CI = &get_instance();
    $CI->load->view('admin/includes/head');
    $CI->load->view('admin/includes/header', ['startedTimers' => $CI->misc_model->get_staff_started_timers()]);
    $CI->load->view('admin/includes/setup_menu');
    if ($aside == true) {
        $CI->load->view('admin/includes/aside');
    }
}
/**
 * @since  1.0.0
 * Init admin footer/tails
 */
function init_tail()
{
    $CI = &get_instance();
    $CI->load->view('admin/includes/scripts');
}
/**
 * Get admin url
 * @param string url to append (Optional)
 * @return string admin url
 */
function admin_url($url = '')
{
    $adminURI = get_admin_uri();

    if ($url == '' || $url == '/') {
        if ($url == '/') {
            $url = '';
        }

        return site_url($adminURI) . '/';
    }

    return site_url($adminURI . '/' . $url);
}

function pr($string, $die = false){
    echo "<pre>";
    print_r($string);
    echo "</pre>";
    if( $die ) die;
}

function get_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    
    if($ipaddress == '::1'){
        $ipaddress = '122.173.26.199';
    }
    return $ipaddress;
}

function GetBearerToken(){
    $ci = &get_instance();
    $ci->load->model('settings_model');
    try {
        $base_url       = get_option('api_base_url');
        $clientId       = get_option('company_client_id');
        $clientSecret   = get_option('company_client_secret');

        //Create guzzle http client
        $client = new \GuzzleHttp\Client(); 
        $response = $client->request('POST', $base_url.'/api/authenticate', [
            'headers' => [
                'accept' => 'application/json',
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ],
        ]);

        $body = $response->getBody();
        // Output the response
        $bearer_token = json_decode($body)->data->accessToken;
        
        if ($bearer_token) {
            $post_data['settings']['company_bearer_token'] = $bearer_token;
        }
        // pr($bearer_token,1);
        $success = $ci->settings_model->update($post_data);
        return $bearer_token;
    } catch (\Exception $e) {
        // Catch any other exceptions
        return '';
        echo 'An unexpected error occurred: ' . $e->getMessage();
    }
}

function GenerateAuthToken() {
    $ci = &get_instance();
    $ci->load->model('settings_model');
    $base_url           = get_option('api_base_url');
    $bearer_token       = get_option('company_bearer_token');
    if(empty($bearer_token)){
        $bearer_token = GetBearerToken();
    }

    $gst = $ci->settings_model->get_default_gst();
    $body = [
        "action"   => "ACCESSTOKEN",
        "username" => $gst->einvoice->user_name,
        "password" => $gst->einvoice->password,
    ];
    
    try {
        //Create guzzle http client
        $client = new \GuzzleHttp\Client(); 
        $response = $client->request('POST', $base_url.'/api/eway/enhanced/authentication', [
            'body' => json_encode($body),
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Bearer '.$bearer_token,
                'content-type' => 'application/json',
                'gstin' => $gst->gstin,
            ],
        ]);
        // Get the response body
        $body = $response->getBody();
        // pr(json_decode($body));
        // Output the response
        $einvoice_data['Sek'] = json_decode($body)->Data->Sek;
        $einvoice_data['AuthToken'] = json_decode($body)->Data->AuthToken;
        $einvoice_data['TokenExpiry'] = json_decode($body)->Data->TokenExpiry;
        
        $gst_success = $ci->settings_model->einvoice_update($einvoice_data, $gst->einvoice->id);
        $gst = $ci->settings_model->get_default_gst();
        return $gst;
    } catch (\Exception $e) {
        // Catch any other exceptions
        $gst_data['status'] = false;
        $gst_data['msg']    =  $e->getMessage();
        return $gst_data;
        echo 'An unexpected error occurred: ' . $e->getMessage();
    }
}

function create_eway_bill($invoice_data){
    $ci = &get_instance();
    $ci->load->model('settings_model');
    $base_url = get_option('api_base_url');
    
    $gst = $ci->settings_model->get_default_gst();
    // pr($gst);
    if($gst && $gst->einvoice && time() < strtotime($gst->einvoice->TokenExpiry)){
    } else {
        $gst = GenerateAuthToken();
    }

    $itemList = [];

    foreach ($invoice_data->items ?? [] as $item) {
        $invoice_item_taxes = get_invoice_item_taxes($item['id']);
        // pr($invoice_item_taxes);
        $amount = $item['rate'] * $item['qty'];

        $_tax = [];
        // $taxable_amount = $amount;
        foreach ($invoice_item_taxes as $key => $value) {
            $name = explode("-", $value['taxname']);
            // $tax_amount = ($amount / 100 ) * $value['taxrate'];
            $_tax[$name[0]]= $value['taxrate'];
            // $_tax[$name[0]]= $tax_amount;
            // $taxable_amount = $taxable_amount + $tax_amount;
        }
        
        $itemList[] = [
            "productName" => $item['description'],
            "productDesc" => $item['long_description'],
            "hsnCode" =>    $item['hsnCode'] ?? 851770,
            "quantity" => (int) $item['qty'],
            "qtyUnit" =>  $item['unit'],
            "cgstRate" => $_tax['IGST'] ?? 0, // Assuming you have CGST in the first index
            "sgstRate" => $_tax['SGST'] ?? 0, // Assuming you have SGST in the second index
            "igstRate" => $_tax['CGST'] ?? 0, // Assuming you have IGST in the third index
            "cessRate" => 0,
            "cessNonadvol" => 0,
            "taxableAmount" => (int) $amount
        ];
    }
    // pr($gst);
    // Now $itemList contains the formatted data for the API
    $api_data = [
        "supplyType" => "O",
        "subSupplyType" =>  "1",
        "subSupplyDesc" =>  null,
        "docType" =>  "INV",
        "docNo" =>  $invoice_data->id,
        "docDate" =>  date('d/m/Y',strtotime($invoice_data->date)), //"05/04/2022",
        "fromGstin" =>  $gst->gstin,
        "fromTrdName" =>  $gst->authorised_person_name,
        "fromAddr1" =>  $gst->address,
        "fromAddr2" =>   $gst->address,
        "fromPlace" =>  get_option('invoice_company_city'),
        "fromPincode" =>  get_option('invoice_company_postal_code') ?? 560090,
        // "fromPincode" => 560090,
        "actFromStateCode" => (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
        "fromStateCode" => (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
        // "actFromStateCode" =>  29,
        // "fromStateCode" =>  29,
        "toGstin" =>  $invoice_data->client->gst_number,
        // "toGstin" =>  '33AAACH1925Q1ZH',
        "toTrdName" =>  $invoice_data->client->company,
        "toAddr1" =>  $invoice_data->client->address,
        "toAddr2" =>  $invoice_data->client->address,
        "toPlace" =>  $invoice_data->client->city ?? '',
        "toPincode" => (int) $invoice_data->client->zip ?? 134109,
        "actToStateCode" =>  (int) ($invoice_data->client->gst_number) ? substr($invoice_data->client->gst_number,0,2) : 29,
        "toStateCode" =>  (int) ($invoice_data->client->gst_number) ? substr($invoice_data->client->gst_number,0,2) : 29,
        // "actToStateCode" =>   29,
        // "toStateCode" =>   29,
        "transactionType" =>  2,
        "otherValue" =>  "0",
        "totalValue" => (int) $invoice_data->subtotal,
        // "totalValue" => 3434,
        "cgstValue" =>  0,
        "sgstValue" =>  0,
        "igstValue" =>    (int) $invoice_data->total_tax,
        "cessValue" =>  0,
        "cessNonAdvolValue" =>  0,
        "totInvValue" =>  (int) $invoice_data->total,
        // "totInvValue" =>   22323,
        "transporterId" => $invoice_data->transporter_id,
        "transporterName" => $invoice_data->transporter_name,
        "transDocNo" => $invoice_data->trans_doc_no,
        "transMode" => $invoice_data->trans_mode,
        "transDistance" => $invoice_data->trans_distance,
        "transDocDate" => date('d/m/Y',strtotime($invoice_data->trans_doc_date)),
        "vehicleNo" => $invoice_data->vehicle_no,
        "vehicleType" => $invoice_data->vehicle_type,
        "itemList" => $itemList,
    ];
    // pr($api_data);
    // pr(json_encode($api_data));

    $bearer_token       = get_option('company_bearer_token');

    //Create guzzle http client
    $client = new \GuzzleHttp\Client(); 
    $response = $client->request('POST', $base_url.'/api/eway/enhanced/generate', [
        'body' => json_encode($api_data),
        'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$bearer_token,
            'authtoken' => $gst->einvoice->AuthToken,
            'content-type' => 'application/json',
            'gstin' => $gst->gstin,
            'sek' => $gst->einvoice->Sek,
            'username' => $gst->einvoice->user_name,
        ],
    ]);
    // Get the response body
    $body = $response->getBody();
    // Output the response
    $body = json_decode($body);
    // pr($body);
    if(isset($body->ewayBillNo) && !isset($body->status)){
        $data['status'] = 1; 
        $data['data']['eway_bill_no'] = $body->ewayBillNo;
    } else {
        $errors = explode(",",$body->error->errorCodes);
        array_pop($errors);
        foreach ($errors ?? [] as $key => $value) {
            $get = $ci->settings_model->get_error($value);
            if($get){
                $err[$key] = $get->error;
            } else {
                $err[$key] = $value.' - Please check description in API Error codes List.';
            }
        }
        $data['status'] = 0; 
        $data['errors'] =$err;
    }
    pr($data,1);
    return $data;
    // pr($body,1);
}

function genarate_einvoice($invoice_data){
    $ci = &get_instance();
    $ci->load->model('settings_model');
    $base_url = get_option('api_base_url');
    
    $gst = $ci->settings_model->get_default_gst();
    if($gst && $gst->einvoice && time() < strtotime($gst->einvoice->TokenExpiry)){
    } else {
        $gst = GenerateAuthToken();
    }

    $itemList = [];
    $totalAssVal = 0;
    foreach ($invoice_data->items ?? [] as $item) {
        $invoice_item_taxes = get_invoice_item_taxes($item['id']);
        // pr($invoice_item_taxes);
        $amount = $item['rate'] * $item['qty'];
        $totalAssVal = $totalAssVal + $amount;
        $_tax = [];
        // $taxable_amount = $amount;
        foreach ($invoice_item_taxes as $key => $value) {
            $name = explode("-", $value['taxname']);
            // $tax_amount = ($amount / 100 ) * $value['taxrate'];
            $_tax[$name[0]]= $value['taxrate'];
            // $_tax[$name[0]]= $tax_amount;
            // $taxable_amount = $taxable_amount + $tax_amount;
        }
        
        $itemList[] = [
            "SlNo"=> "1",
            "PrdDesc"=> $item['description'],
            "IsServc"=> "N",
            "HsnCd"=> $item['hsnCode'] ?? 851770,
            // "Barcde"=> "123456",
            "Qty"=> (int) $item['qty'],
            // "FreeQty"=> 10,
            "Unit"=> $item['unit'],
            "UnitPrice"=> (int) $item['rate'],
            "TotAmt"=> (int) $amount,
            "Discount"=> 0,
            // "PreTaxVal"=> 1,
            "AssAmt"=> (int) $amount,
            "GstRt"=> $_tax['IGST'] ?? 0,
            "IgstAmt"=> (int)  ($amount / 100 ) * $_tax['IGST'],
            "CgstAmt"=> 0,
            "SgstAmt"=> 0,
            // "CesRt"=> 5,
            // "CesAmt"=> 498.94,
            // "CesNonAdvlAmt"=> 10,
            // "StateCesRt"=> 12,
            // "StateCesAmt"=> 1197.46,
            // "StateCesNonAdvlAmt"=> 5,
            "OthChrg"=> 0,
            "TotItemVal"=> (int) $amount + ($amount / 100 ) * $_tax['IGST'],
            // "OrdLineRef"=> "3256",
            // "OrgCntry"=> "AG",
            // "PrdSlNo"=> "12345",
            // "BchDtls"=> {
            //     "Nm"=> "123456",
            //     "ExpDt"=> "01/08/2020",
            //     "WrDt"=> "01/09/2020"
            // },
            // "AttribDtls"=> [
            //     {
            //         "Nm"=> "Rice",
            //         "Val"=> "10000"
            //     }
            // ]
        ];
    }

    $api_data = [
        "Version"=> "1.1",
        "Irn"=> null,
        "TranDtls"=> [
            "TaxSch"=> "GST",
            "SupTyp"=> "B2B",
            "RegRev"=> "Y",
            "EcmGstin"=> null,
            "IgstOnIntra"=> "N"
        ],
        "DocDtls"=> [
            "Typ"=> "INV",
            // "No"=> 'test1cc24696',
            "No"=> $invoice_data->id,
            "Dt"=> "01/12/2023"
            // "Dt"=>  date('d/m/Y',strtotime($invoice_data->date))
        ],
        "SellerDtls"=> [
            "Gstin"=> $gst->gstin,
            "LglNm"=> get_option('invoice_company_name'), //$gst->authorised_person_name,
            "TrdNm"=> get_option('invoice_company_name'), //$gst->authorised_person_name,
            "Addr1"=> $gst->address,
            "Addr2"=> $gst->address,
            "Loc"=> get_option('company_state'),
            "Pin"=> (int) get_option('invoice_company_postal_code') ?? 560090,
            "Stcd"=> (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
            // "Ph"=> "9000000000",
            // "Em"=> "abc@gmail.com"
        ],
        "BuyerDtls"=> [
            "Gstin"=>  $invoice_data->client->gst_number,
            "LglNm"=> $invoice_data->client->company,
            "TrdNm"=>$invoice_data->client->company,
            "Pos"=> ($invoice_data->client->gst_number) ? substr($invoice_data->client->gst_number,0,2) : 29,
            "Addr1"=>  $invoice_data->client->address,
            "Addr2"=>  $invoice_data->client->address,
            "Loc"=>$invoice_data->client->city ?? '',
            "Pin"=> (int) $invoice_data->client->zip ?? 134109,
            "Stcd"=> (int) ($invoice_data->client->gst_number) ? substr($invoice_data->client->gst_number,0,2) : 29,
            // "Ph"=> $invoice_data->client->phonenumber,
            // "Em"=> "xyz@yahoo.com"
        ],
        "DispDtls"=> [
            "Nm"=>$gst->authorised_person_name,
            "Addr1"=> $gst->address,
            "Addr2"=> $gst->address,
            "Loc"=> get_option('invoice_company_city'),
            "Pin"=> get_option('invoice_company_postal_code') ?? 560090,
            "Stcd"=> (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
        ],
        "ShipDtls"=> [
            "Gstin"=> $invoice_data->client->gst_number,
            "LglNm"=> $invoice_data->client->company,
            "TrdNm"=>$invoice_data->client->company,
            "Addr1"=> $invoice_data->client->address,
            "Addr2"=> $invoice_data->client->address,
            "Loc"=> $invoice_data->client->city ?? '',
            "Pin"=> (int) $invoice_data->client->zip ?? 134109,
            "Stcd"=>(int) ($invoice_data->client->gst_number) ? substr($invoice_data->client->gst_number,0,2) : 29,
        ],
        "ItemList"=> $itemList,
        "ValDtls"=> [
            "AssVal"=> (int) $totalAssVal,
            "CgstVal"=> 0,
            "SgstVal"=> 0,
            "IgstVal"=>  (int) $invoice_data->total_tax,
            // "CesVal"=> 0,
            // "StCesVal"=> 1202.46,
            "Discount"=> 0,
            "OthChrg"=> 0,
            "RndOffAmt"=> 0,
            "TotInvVal"=> (int) $invoice_data->total,
            "TotInvValFc"=> (int) $invoice_data->total
        ],
        // "PayDtls"=> [
        //     "Nm"=> "ABCDE",
        //     "AccDet"=> "5697389713210",
        //     "Mode"=> "Cash",
        //     "FinInsBr"=> "SBIN11000",
        //     "PayTerm"=> "100",
        //     "PayInstr"=> "Gift",
        //     "CrTrn"=> "test",
        //     "DirDr"=> "test",
        //     "CrDay"=> 100,
        //     "PaidAmt"=> 10000,
        //     "PaymtDue"=> 5000
        // ],
        // "RefDtls"=> [
        //     "InvRm"=> "TEST",
        //     "DocPerdDtls"=> [
        //         "InvStDt"=> "01/08/2020",
        //         "InvEndDt"=> "01/09/2020"
        //     ]
        // ],
        // "PrecDocDtls"=> [
        //     [
        //         "InvNo"=> "DOC/002",
        //         "InvDt"=> "01/08/2020",
        //         "OthRefNo"=> "123456"
        //     ]
        // ],
        // "ContrDtls"=> [
        //     [
        //         "RecAdvRefr"=> "AB12340",
        //         "RecAdvDt"=> null,
        //         "TendRefr"=> "D/10",
        //         "ContrRefr"=> "CRs",
        //         "ExtRefr"=> "Yo456",
        //         "ProjRefr"=> "Doc-456",
        //         "PORefr"=> "Doc-789",
        //         "PORefDt"=> "01/08/2020"
        //     ]
        // ],
        // "AddlDocDtls"=> [
        //     [
        //         "Url"=> "https://einv-apisandbox.nic.in",
        //         "Docs"=> "Test Doc",
        //         "Info"=> "Document Test"
        //     ]
        // ],
        // "ExpDtls"=> [
        //     "ShipBNo"=> null,
        //     "ShipBDt"=> null,
        //     "Port"=> null,
        //     "RefClm"=> null,
        //     "ForCur"=> null,
        //     "CntCode"=> null,
        //     "ExpDuty"=> null
        // ],
        // "EwbDtls"=> [
        //     "TransId"=> "12AWGPV7107B1Z1",
        //     "TransName"=> "XYZ EXPORTS",
        //     "TransMode"=> "1",
        //     "Distance"=> 100,
        //     "TransDocNo"=> "DOC01",
        //     "TransDocDt"=> "18/08/2020",
        //     "VehNo"=> "ka123456",
        //     "VehType"=> "R"
        // ]        
    ];

    $bearer_token       = get_option('company_bearer_token');
    //Create guzzle http client
    $client = new \GuzzleHttp\Client(); 
    $response = $client->request('POST', $base_url.'/api/einvoice/enhanced/generate-irn', [
        'body' => json_encode($api_data),
        'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$bearer_token,
            'authtoken' => $gst->einvoice->AuthToken,
            'content-type' => 'application/json',
            'gstin' => $gst->gstin,
            'sek' => $gst->einvoice->Sek,
            'user_name' => $gst->einvoice->user_name,
        ],
    ]);
    // Get the response body
    $body = $response->getBody();
    // Output the response
    $body = json_decode($body);
    if(isset($body->AckNo) && $body->Status == 'ACT'){
        $data['status'] = 1; 
        $data['data']['AckNo'] = $body->AckNo;
        $data['data']['AckDt'] = $body->AckDt;
        $data['data']['Irn'] = $body->Irn;
        $data['data']['SignedInvoice'] = $body->SignedInvoice;
        $data['data']['SignedQRCode'] = $body->SignedQRCode;
    } else if(isset($body->ErrorDetails) && $body->Status == 0){
        foreach($body->ErrorDetails ?? [] as $key => $value) {
            $err[$key]=$value->ErrorCode . ' - ' . $value->ErrorMessage;
        }
        $data['status'] = 0; 
        $data['errors'] =$err;
    }
    // pr($data);
    return $data;
    pr(json_encode($itemList),1);
}

function GetOTP(){
    $ci = &get_instance();
    $ci->load->model('settings_model');

    $gst = $ci->settings_model->get_default_gst();
    $api_data=[
        "action" => "OTPREQUEST",
        "username" => $gst->gst_user_id,
    ];
    $base_url       = get_option('api_base_url');
    $bearer_token   = get_option('company_bearer_token');
    if(empty($bearer_token)){
        $bearer_token = GetBearerToken();
    }

    //Create guzzle http client
    $client = new \GuzzleHttp\Client(); 
    $response = $client->request('POST', $base_url.'/api/gst/requestOTP', [
        'body' => json_encode($api_data),
        'headers' => [
            'accept' => 'application/json',
            'ip-usr' => get_ip(),
            // 'state-cd'=> (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29, 
            'state-cd'=> 33, 
            'authorization' => 'Bearer '.$bearer_token,
            'content-type' => 'application/json',

        ],
    ]);
    $body = $response->getBody();
    // Output the response
    $body = json_decode($body);
    $gst_data = [];
    if($body->status_cd == 1){
        $gst_data['gst_app_key'] = $body->app_key;
        $gst_success = $ci->settings_model->gst_update($gst_data, $gst->id);
    }
    $gst = $ci->settings_model->get_default_gst();
    return $gst;
}
function GetGstAuthToken($id = ''){
    $ci = &get_instance();
    $ci->load->model('settings_model');

    $base_url       = get_option('api_base_url');
    $bearer_token       = get_option('company_bearer_token');
    if(empty($bearer_token)){
        $bearer_token = GetBearerToken();
    }
    // $state_cd       = get_option('state-cd');
    if($id != ''){
        $gst = $ci->settings_model->get_all($id);
    } else {
        $gst = $ci->settings_model->get_default_gst();
    }

    $api_data=[
        "action" => "AUTHTOKEN",
        "username" => $gst->gst_user_id,
        "otp" => $gst->otp
    ];
    //Create guzzle http client
    $client = new \GuzzleHttp\Client(); 
    $response = $client->request('POST', $base_url.'/api/gst/authtoken', [
        'body' => json_encode($api_data),
        'headers' => [
            'accept' => 'application/json',
            'ip-usr' => get_ip(),
            'state-cd'=> (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
            'authorization' => 'Bearer '.$bearer_token,
            'content-type' => 'application/json',

        ],
    ]);
    $body = $response->getBody();
      // Output the response
    $body = json_decode($body);
    // pr($body,1);
    $gst_data = [];
    if(isset($body->auth_token) && isset($body->status_cd)){
        $gst_data['gst_auth_token'] = $body->auth_token;
        $gst_data['gst_token_expiry_date'] = Date('y:m:d', strtotime('+'.$body->expiry.' days'));
        $gst_data['gst_sek'] = $body->sek;
        $gst_success = $ci->settings_model->gst_update($gst_data, $gst->id);
    } else if($body->status_cd == 0){
        $err = $body->error->error_cd.' - '.$body->error->message;
        $data['status'] = 0; 
        $data['errors'] =$err;
    }
    $gst = $ci->settings_model->get_default_gst();
    return $gst;
}
function GetGstRefreshToken(){
    $ci = &get_instance();
    $ci->load->model('settings_model');

    $base_url           = get_option('api_base_url');
    $bearer_token       = get_option('company_bearer_token');
    if(empty($bearer_token)){
        $bearer_token = GetBearerToken();
    }
    // $state_cd       = get_option('state-cd');
    $gst = $ci->settings_model->get_default_gst();

    $api_data=[
        "action" => "REFRESHTOKEN",
        "username" => $gst->gst_user_id,
        "auth_token" => $gst->gst_auth_token,
        "app_key" => $gst->gst_app_key
    ];
    //Create guzzle http client
    $client = new \GuzzleHttp\Client(); 
    $response = $client->request('POST', $base_url.'/api/gst/refreshtoken', [
        'body' => json_encode($api_data),
        'headers' => [
            'accept' => 'application/json',
            'ip-usr' => get_ip(),
            // 'state-cd'=> (int) ($gst->gst_number) ? substr($gst->gst_number,0,2) : 29,
            'state-cd'=> 27,
            'authorization' => 'Bearer '.$bearer_token,
            'sek' => $gst->gst_app_key,
            'content-type' => 'application/json',

        ],
    ]);
    $body = $response->getBody();
    // echo $body;
      // Output the response
    $body = json_decode($body);
    $gst_data = [];
    if(isset($body->auth_token) && isset($body->status_cd)){
        $gst_data['gst_auth_token'] = $body->auth_token;
        $gst_data['gst_token_expiry_date'] = Date('y:m:d', strtotime('+'.$body->expiry.' days'));
        $gst_data['gst_sek'] = $body->sek;
        $gst_success = $ci->settings_model->gst_update($gst_data, $gst->id);
    } else if($body->status_cd == 0){
        $err = $body->error->error_cd.' - '.$body->error->message;
        $data['status'] = 0; 
        $data['errors'] =$err;
    }
    $gst = $ci->settings_model->get_default_gst();
    return $gst;
}

/**
 * @since  2.3.3
 * Helper function for checking staff capabilities, this function should be used instead of has_permission
 * Can be used e.q. staff_can('view', 'invoices');
 *
 * @param  string $capability         e.q. view | create | edit | delete | view_own | can_delete
 * @param  string $feature            the feature name e.q. invoices | estimates | contracts | my_module_name
 *
 *    NOTE: The $feature parameter is available as optional, but it's highly recommended always to be passed
 *    because of the uniqueness of the capability names.
 *    For example, if there is a capability "view" for feature "estimates" and also for "invoices" a capability "view" exists too
 *    In this case, if you don't pass the feature name, there may be inaccurate results
 *    If you are certain that your capability name is unique e.q. my_prefixed_capability_can_create , you don't need to pass the $feature
 *    and you can use this function as e.q. staff_can('my_prefixed_capability_can_create')
 *
 * @param  mixed $staff_id            staff id | if not passed, the logged in staff will be checked
 *
 * @return boolean
 */
function staff_can($capability, $feature = null, $staff_id = '')
{
    $staff_id = empty($staff_id) ? get_staff_user_id() : $staff_id;

    /**
     * Maybe permission is function?
     * Example is_admin or is_staff_member
     */
    if (function_exists($capability) && is_callable($capability)) {
        return call_user_func($capability, $staff_id);
    }

    /**
     * If user is admin return true
     * Admins have all permissions
     */
    if (is_admin($staff_id)) {
        return true;
    }

    $CI = &get_instance();

    $permissions = null;
    /**
     * Stop making query if we are doing checking for current user
     * Current user is stored in $GLOBALS including the permissions
     */
    if ((string) $staff_id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
        $permissions = $GLOBALS['current_user']->permissions;
    }

    /**
     * Not current user?
     * Get permissions for this staff
     * Permissions will be cached in object cache upon first request
     */
    if (!$permissions) {
        if (!class_exists('staff_model', false)) {
            $CI->load->model('staff_model');
        }

        $permissions = $CI->staff_model->get_staff_permissions($staff_id);
    }

    if (!$feature) {
        $retVal = in_array_multidimensional($permissions, 'capability', $capability);

        return hooks()->apply_filters('staff_can', $retVal, $capability, $feature, $staff_id);
    }

    foreach ($permissions as $permission) {
        if ($feature == $permission['feature']
            && $capability == $permission['capability']) {
            return hooks()->apply_filters('staff_can', true, $capability, $feature, $staff_id);
        }
    }

    return hooks()->apply_filters('staff_can', false, $capability, $feature, $staff_id);
}

/**
 * @since  2.3.3
 * Check whether a role has specific permission applied
 * @param  mixed  $role_id    role id
 * @param  string  $capability e.q. view|create|read
 * @param  string  $feature    the feature, e.q. invoices|estimates etc...
 * @return boolean
 */
function has_role_permission($role_id, $capability, $feature)
{
    $CI          = &get_instance();
    $permissions = $CI->roles_model->get($role_id)->permissions;

    foreach ($permissions as $appliedFeature => $capabilities) {
        if ($feature == $appliedFeature && in_array($capability, $capabilities)) {
            return true;
        }
    }

    return false;
}

/**
 * @since 1.0.0
 * NOTE: This function will be deprecated in future updates, use staff_can($do, $feature = null, $staff_id = '') instead
 *
 * Check if staff user has permission
 * @param  string  $permission permission shortname
 * @param  mixed  $staffid if you want to check for particular staff
 * @return boolean
 */
function has_permission($permission, $staffid = '', $can = '')
{
    return staff_can($can, $permission, $staffid);
}
/**
 * @since  1.0.0
 * Load language in admin area
 * @param  string $staff_id
 * @return string return loaded language
 */
function load_admin_language($staff_id = '')
{
    $CI = & get_instance();

    $CI->lang->is_loaded = [];
    $CI->lang->language  = [];

    $language = get_option('active_language');
    if ((is_staff_logged_in() || $staff_id != '') && !is_language_disabled()) {
        $staff_language = get_staff_default_language($staff_id);
        if (!empty($staff_language)
            && file_exists(APPPATH . 'language/' . $staff_language)) {
            $language = $staff_language;
        }
    }

    $CI->lang->load($language . '_lang', $language);
    load_custom_lang_file($language);
    $GLOBALS['language'] = $language;
    $GLOBALS['locale']   = get_locale_key($language);

    hooks()->do_action('after_load_admin_language', $language);

    return $language;
}


/**
 * Return admin URI
 * CUSTOM_ADMIN_URL is not yet tested well, don't define it
 * @return string
 */
function get_admin_uri()
{
    return ADMIN_URI;
}

/**
 * @since  1.0.0
 * Check if current user is admin
 * @param  mixed $staffid
 * @return boolean if user is not admin
 */
function is_admin($staffid = '')
{

    /**
     * Checking for current user?
     */
    if (!is_numeric($staffid)) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->admin === '1';
        }

        $staffid = get_staff_user_id();
    }

    $CI = & get_instance();

    if ($cache = $CI->app_object_cache->get('is-admin-' . $staffid)) {
        return $cache === 'yes';
    }

    $CI->db->select('1')
    ->where('admin', 1)
    ->where('staffid', $staffid);

    $result = $CI->db->count_all_results(db_prefix() . 'staff') > 0 ? true : false;
    $CI->app_object_cache->add('is-admin-' . $staffid, $result ? 'yes' : 'no');

    return is_admin($staffid);
}

function admin_body_class($class = '')
{
    echo 'class="' . join(' ', get_admin_body_class($class)) . '"';
}

function get_admin_body_class($class = '')
{
    $classes   = [];
    $classes[] = 'app';
    $classes[] = 'admin';
    $classes[] = $class;

    $ci = &get_instance();

    $first_segment  = $ci->uri->segment(1);
    $second_segment = $ci->uri->segment(2);
    $third_segment  = $ci->uri->segment(3);

    $classes[] = $first_segment;

    // Not valid eq users/1 - ID
    if ($second_segment != '' && !is_numeric($second_segment)) {
        $classes[] = $second_segment;
    }

    // Not valid eq users/edit/1 - ID
    if ($third_segment != '' && !is_numeric($third_segment)) {
        $classes[] = $third_segment;
    }

    if (is_staff_logged_in()) {
        $classes[] = 'user-id-' . get_staff_user_id();
    }

    $classes[] = strtolower($ci->agent->browser());

    if (is_mobile()) {
        $classes[] = 'mobile';
        $classes[] = 'hide-sidebar';
    }

    if (is_rtl()) {
        $classes[] = 'rtl';
    }

    $classes = hooks()->apply_filters('admin_body_class', $classes);

    return array_unique($classes);
}


/**
 * Feature that will render all JS necessary data in admin head
 * @return void
 */
function render_admin_js_variables()
{
    $date_format   = get_option('dateformat');
    $date_format   = explode('|', $date_format);
    $maxUploadSize = file_upload_max_size();
    $date_format   = $date_format[0];
    $CI            = &get_instance();

    $options = [
        'date_format'                                 => $date_format,
        'decimal_places'                              => get_decimal_places(),
        'company_is_required'                         => get_option('company_is_required'),
        'default_view_calendar'                       => get_option('default_view_calendar'),
        'calendar_events_limit'                       => get_option('calendar_events_limit'),
        'tables_pagination_limit'                     => get_option('tables_pagination_limit'),
        'time_format'                                 => get_option('time_format'),
        'decimal_separator'                           => get_option('decimal_separator'),
        'thousand_separator'                          => get_option('thousand_separator'),
        'timezone'                                    => get_option('default_timezone'),
        'calendar_first_day'                          => get_option('calendar_first_day'),
        'allowed_files'                               => get_option('allowed_files'),
        'desktop_notifications'                       => get_option('desktop_notifications'),
        'show_table_export_button'                    => get_option('show_table_export_button'),
        'has_permission_tasks_checklist_items_delete' => has_permission('checklist_templates', '', 'delete'),
        'show_setup_menu_item_only_on_hover'          => get_option('show_setup_menu_item_only_on_hover'),
        'newsfeed_maximum_files_upload'               => get_option('newsfeed_maximum_files_upload'),
        'dismiss_desktop_not_after'                   => get_option('auto_dismiss_desktop_notifications_after'),
        'enable_google_picker'                        => get_option('enable_google_picker'),
        'google_client_id'                            => get_option('google_client_id'),
        'google_api'                                  => get_option('google_api_key'),
        'has_permission_create_task'                  => staff_can('create', 'tasks'),
    ];

    // by remove it means do not prefix it

    $lang = [
        'invoice_task_billable_timers_found'                      => _l('invoice_task_billable_timers_found'),
        'validation_extension_not_allowed'                        => _l('validation_extension_not_allowed'),
        'tag'                                                     => _l('tag'),
        'options'                                                 => _l('options'),
        'no_items_warning'                                        => _l('no_items_warning'),
        'item_forgotten_in_preview'                               => _l('item_forgotten_in_preview'),
        'new_notification'                                        => _l('new_notification'),
        'estimate_number_exists'                                  => _l('estimate_number_exists'),
        'invoice_number_exists'                                   => _l('invoice_number_exists'),
        'confirm_action_prompt'                                   => _l('confirm_action_prompt'),
        'calendar_expand'                                         => _l('calendar_expand'),
        'media_files'                                             => _l('media_files'),
        'credit_note_number_exists'                               => _l('credit_note_number_exists'),
        'item_field_not_formatted'                                => _l('numbers_not_formatted_while_editing'),
        'email_exists'                                            => _l('email_exists'),
        'phonenumber_exists'                                      => _l('phonenumber_exists'),
        'website_exists'                                          => _l('website_exists'),
        'company_exists'                                          => _l('company_exists'),
        'filter_by'                                               => _l('filter_by'),
        'you_can_not_upload_any_more_files'                       => _l('you_can_not_upload_any_more_files'),
        'cancel_upload'                                           => _l('cancel_upload'),
        'browser_not_support_drag_and_drop'                       => _l('browser_not_support_drag_and_drop'),
        'drop_files_here_to_upload'                               => _l('drop_files_here_to_upload'),
        'file_exceeds_max_filesize'                               => _l('file_exceeds_max_filesize') . ' (' . bytesToSize('', $maxUploadSize) . ')',
        'file_exceeds_maxfile_size_in_form'                       => _l('file_exceeds_maxfile_size_in_form') . ' (' . bytesToSize('', $maxUploadSize) . ')',
        'unit'                                                    => _l('unit'),
        'dt_length_menu_all'                                      => _l('dt_length_menu_all'),
        'dt_button_reload'                                        => _l('dt_button_reload'),
        'dt_button_excel'                                         => _l('dt_button_excel'),
        'dt_button_csv'                                           => _l('dt_button_csv'),
        'dt_button_pdf'                                           => _l('dt_button_pdf'),
        'dt_button_print'                                         => _l('dt_button_print'),
        'dt_button_export'                                        => _l('dt_button_export'),
        'search_ajax_empty'                                       => _l('search_ajax_empty'),
        'search_ajax_initialized'                                 => _l('search_ajax_initialized'),
        'search_ajax_searching'                                   => _l('search_ajax_searching'),
        'not_results_found'                                       => _l('not_results_found'),
        'search_ajax_placeholder'                                 => _l('search_ajax_placeholder'),
        'currently_selected'                                      => _l('currently_selected'),
        'task_stop_timer'                                         => _l('task_stop_timer'),
        'dt_button_column_visibility'                             => _l('dt_button_column_visibility'),
        'note'                                                    => _l('note'),
        'search_tasks'                                            => _l('search_tasks'),
        'confirm'                                                 => _l('confirm'),
        'showing_billable_tasks_from_project'                     => _l('showing_billable_tasks_from_project'),
        'invoice_task_item_project_tasks_not_included'            => _l('invoice_task_item_project_tasks_not_included'),
        'credit_amount_bigger_then_invoice_balance'               => _l('credit_amount_bigger_then_invoice_balance'),
        'credit_amount_bigger_then_credit_note_remaining_credits' => _l('credit_amount_bigger_then_credit_note_remaining_credits'),
        'save'                                                    => _l('save'),
        'expense'                                                 => _l('expense'),
        'ticket'                                                  => _l('ticket'),
        'lead'                                                    => _l('lead'),
        'create_reminder'                                         => _l('create_reminder'),
    ];

    echo '<script>';

    echo 'var site_url = "' . site_url() . '";';
    echo 'var admin_url = "' . admin_url() . '";';

    echo 'var app = {};';
    echo 'var app = {};';

    echo 'app.available_tags = ' . json_encode(get_tags_clean()) . ';';
    echo 'app.available_tags_ids = ' . json_encode(get_tags_ids()) . ';';
    echo 'app.user_recent_searches = ' . json_encode(get_staff_recent_search_history()) . ';';
    echo 'app.months_json = ' . $monthNames = json_encode([_l('January'), _l('February'), _l('March'), _l('April'), _l('May'), _l('June'), _l('July'), _l('August'), _l('September'), _l('October'), _l('November'), _l('December')]) . ';';
    echo 'app.tinymce_lang = "' . get_tinymce_language($GLOBALS['locale']) . '";';
    echo 'app.locale = "' . $GLOBALS['locale'] . '";';
    echo 'app.browser = "' . strtolower($CI->agent->browser()) . '";';
    echo 'app.user_language = "' . get_staff_default_language() . '";';
    echo 'app.is_mobile = "' . is_mobile() . '";';
    echo 'app.user_is_staff_member = "' . is_staff_member() . '";';
    echo 'app.user_is_admin = "' . is_admin() . '";';
    echo 'app.max_php_ini_upload_size_bytes = "' . $maxUploadSize . '";';
    echo 'app.calendarIDs = "";';

    echo 'app.options = {};';
    echo 'app.lang = {};';

    foreach ($options as $var => $val) {
        echo 'app.options.' . $var . ' = "' . $val . '";';
    }

    foreach ($lang as $key => $val) {
        echo 'app.lang. ' . $key . ' = "' . $val . '";';
    }

    echo 'app.lang.datatables = ' . json_encode(get_datatables_language_array()) . ';';

    /**
     * @deprecated 2.3.2
     */

    $deprecated = [
        'app_language'                                => get_staff_default_language(), // done, prefix it
        'app_is_mobile'                               => is_mobile(), // done, prefix it
        'app_user_browser'                            => strtolower($CI->agent->browser()), // done, prefix it
        'app_date_format'                             => $date_format, // done, prefix it
        'app_decimal_places'                          => get_decimal_places(), // done, prefix it
        'app_company_is_required'                     => get_option('company_is_required'), // done, prefix it
        'app_default_view_calendar'                   => get_option('default_view_calendar'), // done, prefix it
        'app_calendar_events_limit'                   => get_option('calendar_events_limit'), // done, prefix it
        'app_tables_pagination_limit'                 => get_option('tables_pagination_limit'), // done, prefix it
        'app_time_format'                             => get_option('time_format'), // done, prefix it
        'app_decimal_separator'                       => get_option('decimal_separator'), // done, prefix it
        'app_thousand_separator'                      => get_option('thousand_separator'), // done, prefix it
        'app_timezone'                                => get_option('default_timezone'), // done, prefix it
        'app_calendar_first_day'                      => get_option('calendar_first_day'), // done, prefix it
        'app_allowed_files'                           => get_option('allowed_files'), // done, prefix it
        'app_desktop_notifications'                   => get_option('desktop_notifications'), // done, prefix it
        'max_php_ini_upload_size_bytes'               => $maxUploadSize, // done, dont do nothing
        'app_show_table_export_button'                => get_option('show_table_export_button'), // done, dont to nothing
        'calendarIDs'                                 => '', // done, dont do nothing
        'is_admin'                                    => is_admin(), // done, dont do nothing
        'is_staff_member'                             => is_staff_member(), // done, dont do nothing
        'has_permission_tasks_checklist_items_delete' => has_permission('checklist_templates', '', 'delete'), // done, dont do nothing
        'app_show_setup_menu_item_only_on_hover'      => get_option('show_setup_menu_item_only_on_hover'), // done, dont to nothing
        'app_newsfeed_maximum_files_upload'           => get_option('newsfeed_maximum_files_upload'), // done, dont to nothing
        'app_dismiss_desktop_not_after'               => get_option('auto_dismiss_desktop_notifications_after'), // done, dont to nothing
        'app_enable_google_picker'                    => get_option('enable_google_picker'), // done, dont to nothing
        'app_google_client_id'                        => get_option('google_client_id'), // done, dont to nothing
        'google_api'                                  => get_option('google_api_key'), // done, dont do nothing
    ];

    $firstKey = key($deprecated);

    $vars = 'var ' . $firstKey . '="' . $deprecated[$firstKey] . '",';

    unset($deprecated[$firstKey]);

    foreach ($deprecated as $var => $val) {
        $vars .= $var . '="' . $val . '",';
    }

    echo rtrim($vars, ',') . ';';

    echo 'var appLang = {};';
    foreach ($lang as $key => $val) {
        echo 'appLang["' . $key . '"] = "' . $val . '";';
    }

    echo '</script>';
}

function _maybe_system_setup_warnings()
{
    if (!defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') || (defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') && DISABLE_APP_SYSTEM_HELP_MESSAGES)) {
        hooks()->add_action('ticket_created', [new PopupMessage('app\services\messages\FirstTicketCreated'), 'check']);
        hooks()->add_action('lead_created', [new PopupMessage('app\services\messages\FirstLeadCreated'), 'check']);
        hooks()->add_action('new_tag_created', [new PopupMessage('app\services\messages\FirstTagCreated'), 'check']);
        hooks()->add_action('task_timer_started', [new PopupMessage('app\services\messages\StartTimersWithNoTasks'), 'check']);
        hooks()->add_action('task_checklist_item_created', [new PopupMessage('app\services\messages\ReOrderTaskChecklistItems'), 'check']);
        hooks()->add_action('smtp_test_email_success', [new PopupMessage('app\services\messages\MailConfigured'), 'check']);
    }

    // Check for just updates message
    hooks()->add_action('before_start_render_dashboard_content', '_maybe_show_just_updated_message');
    // Check whether mod_security is enabled
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\ModSecurityEnabled'), 'check']);
    // Check if there is index.html file in the root crm directory, on some servers if this file exists eq default server index.html file the authentication/login page may not work properly
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\StaticIndexHtml'), 'check']);
    // Show development message
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\DevelopmentEnvironment'), 'check']);
    // Check if cron is required to be configured for some features
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\IsCronSetupRequired'), 'check']);
    // Base url check for https
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\IsBaseUrlChangeRequired'), 'check']);
    // Check if timezone is set
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\Timezone'), 'check']);
    // Notice for cloudflare rocket loader
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\CloudFlare'), 'check']);
    // Notice for iconv extension
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\Iconv'), 'check']);
    // Check if there is dot in database name, causing problem on upgrade
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\DatabaseNameHasDot'), 'check']);
    // Some hosting providers cast this file as a malicious and may be deleted
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\TcpdfFileMissing'), 'check']);
    // Check for cron job running
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\CronJobFailure'), 'check']);
    // Php version notice
    hooks()->add_action('before_start_render_dashboard_content', [new Message('app\services\messages\PhpVersionNotice'), 'check']);
}
