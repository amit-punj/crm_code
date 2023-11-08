<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Main_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add new  type
     * @param mixed $data All $_POST data
     * @return boolean
     */
    public function update_color($data)
    {
        if(empty($data['theme_css'])){
            $data['theme_css'] = null;
        }
        unset($data['csrf_token_name']);
        $this->db->where('staff_id', $data['staff_id']);
        $staff = $this->db->get(db_prefix() . '_multi_theme')->row();
        if ($staff)
        {
            $this->db->where('staff_id', $data['staff_id']);
            $this->db->update(db_prefix() . '_multi_theme', $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }   
        }
        else{
            $this->db->insert(db_prefix().'_multi_theme', $data);
            $insert_id = $this->db->insert_id();
            if ($this->db->affected_rows() > 0) {
                return true;
            }    
        }
    }
}
