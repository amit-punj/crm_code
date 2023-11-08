<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deal_send_email extends App_mail_template
{
    protected $for = 'deal';

    protected $deals;

    public $slug = 'deal-send-email';
    public $cc = '';
    public $send_to = '';
    public $attachments = [];


    public function __construct($params)
    {
        parent::__construct();

        $this->cc = $params->cc;
        $this->deals = $params;
        $this->send_to = $params->recipient;
        $this->attachments = $params->attachments;
        $this->template = $params->template;
    }

    public function build()
    {

    }
}
