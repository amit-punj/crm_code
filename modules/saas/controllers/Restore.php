<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Restore extends App_Controller
{

    function index()
    {


        $this->load->dbforge();
        $this->db->flush_cache();
        // set db dbprefix to empty
        $this->db->set_dbprefix('');
        $tables = $this->db->list_tables();
        foreach ($tables as $table) {
            if ($table == 'tblsessions') {
                continue;
            }
            // drop all table if exists
            $this->dbforge->drop_table($table);
        }
        $db_name = $this->db->database;
        $db_username = $this->db->username;
        $db_password = $this->db->password;
        $db_host = $this->db->hostname;
        $this->create_tables($db_name, $db_username, $db_password, $db_host);

        $this->restore_company_db();

        redirect('admin/dashboard');
    }

    function restore_company_db()
    {
        // drop all tables from different database (company database)
        $config_db = $this->config->config['config_db'];
        $config_db['database'] = 'acme_34';
        $this->new_db = $this->load->database($config_db, true);
        $tables = $this->new_db->list_tables();

        foreach ($tables as $table) {
            // drop all table if exists
            $this->new_db->query('DROP TABLE IF EXISTS ' . $table);
        }
        // Connect to the database
        $mysqli = new mysqli($config_db['hostname'], $config_db['username'], $config_db['password'], $config_db['database']);
        // Check for errors
        $query = file_get_contents(module_dir_path(SaaS_MODULE, 'assets/reset/acme.sql'));

        if (mysqli_connect_errno())
            return false;
        $mysqli->multi_query($query);

        do {

        } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
        // Close the connection
        $mysqli->close();

    }

    function create_tables($db_name, $db_username, $db_password, $db_host)
    {
        // Connect to the database
        $mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
        // Check for errors
        $query = file_get_contents(module_dir_path(SaaS_MODULE, 'assets/reset/install.sql'));
        if (mysqli_connect_errno())
            return false;
        $mysqli->multi_query($query);

        do {

        } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
        // Close the connection
        $mysqli->close();
    }

}

