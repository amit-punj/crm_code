<?php
defined('BASEPATH') or exit('No direct script access allowed');
$config['default_database'] = APP_DB_NAME;
$config['database_hostname'] = APP_DB_HOSTNAME;
$config['database_username'] = APP_DB_USERNAME;
$config['database_password'] = APP_DB_PASSWORD;
$config['default_url'] = APP_BASE_URL;
$config['main_url'] = APP_BASE_URL;

$config['config_db'] = array(
    'hostname' => $config['database_hostname'], 'username' => $config['database_username'], 'password' => $config['database_password'],
    'database' => '', /* this will be changed "on the fly" in controler */
    'dbdriver' => 'mysqli',
    'dbprefix' => db_prefix(),
    'char_set' => defined('APP_DB_CHARSET') ? APP_DB_CHARSET : 'utf8',
    'dbcollat' => defined('APP_DB_COLLATION') ? APP_DB_COLLATION : 'utf8_general_ci',
    'pconnect' => FALSE, 'db_debug' => TRUE, 'cache_on' => FALSE
);

require_once(FCPATH . 'modules/saas/config/saas_init.php'); // added by saas