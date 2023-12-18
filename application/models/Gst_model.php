<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Gst_model extends App_Model
{
    private $encrypted_fields = ['smtp_password', 'microsoft_mail_client_secret', 'google_mail_client_secret'];

    public function __construct()
    {
        parent::__construct();
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        foreach ($payment_gateways as $gateway) {
            $settings = $gateway['instance']->getSettings();
            foreach ($settings as $option) {
                if (isset($option['encrypted']) && $option['encrypted'] == true) {
                    array_push($this->encrypted_fields, $option['name']);
                }
            }
        }
    }

    /**
     * Update all settings
     * @param  array $data all settings
     * @return integer
     */
    

    function add(  $table = null, $data = null )
    {
        if ( empty( $data ) || empty( $table ) )
        {
            return false;
        }
        $this->db->insert( db_prefix().$table , $data);
        if($this->db->insert_id() > 0) {
            return $this->db->insert_id();
        }
        else {
            return false;
        }
    }

    function select($table, $table2, $join_column, $where_column, $where)
    {
        // $this->db->select('t.*, t2.*, t2.id as item_id');
        $this->db->from(db_prefix().$table." t1");
        $this->db->join(db_prefix().$table2." t2", 't1.id = t2.'.$join_column, 'left');
        $this->db->where("DATE_FORMAT(t1.".$where_column.",'%m%Y') =",$where);
        $this->db->limit('1500');
        $data = $this->db->get();
        if(count($data->result()) > 0) 
        {
            return $data->result();
        }
        else
        {
            return false;
        } 
    }

    function select_single($table, $where_column, $where)
        {
            $this->db->from(db_prefix() . $table);
            $this->db->where("DATE_FORMAT(" . $where_column . ",'%m%Y') =", $where);
            $this->db->limit(1500);
            $data = $this->db->get();

            if ($data->num_rows() > 0) {
                return $data->result();
            } else {
                return false;
            }
        }
}