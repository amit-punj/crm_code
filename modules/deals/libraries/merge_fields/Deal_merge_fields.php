<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deal_merge_fields extends App_merge_fields
{
    /**
     * This function builds an array of custom email templates keys.
     * The provided keys will be available in perfex email template editor for the supported templates.
     * @return array
     */
    public function build()
    {
        // List of email templates used by the plugin
        $templates = [
            'deal-send-email',
        ];
        $available = ['deal'];
    }

    /**
     * Format merge fields for company instance
     * @param object $company
     * @return array
     */
    public function format($company)
    {
        return $this->instance($company);
    }

    /**
     * Company Instance merge fields
     * @param object $company
     * @return array
     */
    public function instance($company)
    {

        $activation_code = $company->activation_code;
        $wildcard = ConfigItems('saas_server_wildcard');
        $companyUrl = base_url();
        $domain = '&d=' . url_encode($company->domain);
        if (!empty($wildcard)) {
            $domain = '';
            $companyUrl = companyUrl($company->domain);
        }
        $sub_domain = $companyUrl . 'setup?c=' . url_encode($activation_code) . $domain;

        $fields = [];
        $fields['{name}'] = $company->name;
        $fields['{company_url}'] = companyUrl($company->domain);
        $fields['{package_name}'] = $company->package_name;
        $fields['{expiration_date}'] = $company->expired_date;
        $fields['{activation_url}'] = $sub_domain;
        $fields['{activation_token}'] = $company->activation_code;
        return $fields;
    }
}
