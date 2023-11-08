<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 mtop20" data-container="top-12">
        <dl class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-3 sm:tw-gap-5">
            <?php
            $uses = get_usages($company_info);
            if (!empty($uses)) {
                foreach ($uses as $use) {
                    if ($use['active'] === 'active') {
                        $total = '';
                        $name = '';
                        $url = $use['url'];
                        $limit = '/' . $use['limit'] . ' ';
                        if (empty($use['limit'])) {
                            $total .= '<del class="tw-text-base tw-font-normal tw-text-neutral-500">';
                            $name .= '<del class="tw-text-base tw-font-normal tw-text-neutral-500">';
                            $limit = '';
                            $url = base_url('updatePackage');
                        }
                        $usesClass = '';
                        // check if the usage is over the limit
                        if ($use['limit'] > 0 && $use['total'] > $use['limit']) {
                            $usesClass = 'tw-border-danger-500 tw-text-danger-600';
                        }
                        $total .= $use['total'] . $limit;
                        if ($use['slug'] === 'media') {
                            $total = convertSize($use['total']) . ' / ' . convertSize($use['limit']);
                        }

                        $name .= '<i class="' . $use['icon'] . '"></i> ' . _l('total') . ' ' . $use['name'] . ' ';
                        if (empty($use['limit'])) {
                            $total .= '</del>';
                            $name .= '</del>';
                        }
                        ?>
                        <a href="<?php echo $url ?>">
                            <div class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-bg-white" <?= $usesClass ? 'style="border-color: #f87171;"' : '' ?>>
                                <div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2 ">
                                    <dt class="tw-font-medium  <?= $usesClass ? 'tw-text-danger-600' : 'text-success' ?>">
                                        <?php
                                        echo $name;
                                        ?>
                                    </dt>
                                    <dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
                                        <div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold  <?= $usesClass ? 'tw-text-danger-600' : 'tw-text-primary-600' ?>">
                                            <?php
                                            echo $total;
                                            ?>
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </a>
                        <?php
                    }
                }
            }
            ?>

        </dl>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="col-sm-4" data-spy="scroll" data-offset="0" xmlns="http://www.w3.org/1999/html">
            <div class="row">

                <div class="panel panel-custom fees_payment">
                    <!-- Default panel contents -->
                    <div class="panel-heading">
                        <div class="panel-title">
                            <strong><?= _l('subscription_details') ?></strong>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php
                        if (!empty($company_info->frequency)) {
                            if ($company_info->frequency == 'monthly') {
                                $frequency = _l('mo');
                            } else if ($company_info->frequency == 'lifetime') {
                                $frequency = _l('lt');
                            } else {
                                $frequency = _l('yr');
                            }
                            $plan_name = '<a data-toggle="modal" data-target="#myModal" href="' . base_url('subs_package_details/' . $company_info->company_history_id . '/1') . '">' . $company_info->package_name . ' ' . display_money($company_info->amount, default_currency()) . ' /' . $frequency . ' ' . '</a>';
                        } else {
                            $plan_name = '-';
                        }

                        $activation_code = null;
                        if (!empty($company_info->db_name) != 0) {
                            $db_name = $company_info->db_name;
                        }
                        $update = true;
                        if ($company_info->status == 'pending') {
                            $label = 'exclamation-triangle text-info';
                            $activation_code = $company_info->activation_code;
                            $update = null;
                        } else if ($company_info->status == 'running') {
                            $label = 'check-circle text-success';
                        } else if ($company_info->status == 'expired') {
                            $label = 'lock text-danger';
                        } else if ($company_info->status == 'suspended') {
                            $label = 'ban text-warning';
                        } else {
                            $label = 'times-circle text-danger';
                        }
                        $trial = null;
                        $till_date = null;
                        $validity_date = null;
                        if ($company_info->status != 'pending') {
                            if ($company_info->trial_period != 0) {
                                $till_date = trial_period($company_info);
                                $trial = '<small class="label label-danger text-sm mt0">' . _l('trial') . '</small>';
                            } else {
                                $till_date = running_period($company_info);
                            }
                            $validity_date = date("Y-m-d", strtotime($till_date . "day"));
                        }
                        ?>
                        <div class="tw-pb tw-pt tw-inline-block overflow-hidden">
                            <i class="fa fa-3x fa-<?= $label ?> pull-left"></i>
                            <h2 class="tw-mt-0 tw-ml-3 pull-left">
                                <?= _l($company_info->status) . ' ' . $trial ?>
                                <?php if (!empty($till_date)) { ?>
                                    <small
                                            class="tw-block text-sm fs-0-8"
                                    ><?= _l('till') . ' ' . $validity_date; ?></small>
                                <?php } ?>
                            </h2>
                        </div>
                        <div class="tw-mb-3 tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md tw-bg-white">
                            <div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2 ">

                                <div class="tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
                                    <div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">
                                        <?= _l('remaining') . ' ' . _l('days') ?></div>
                                    <div class=" tw-self-end tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">
                                        <?= $till_date ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tw-border-b tw-border-solid tw-border-neutral-200 tw-py-2 tw-px-2 tw-mb-1">
                            <label class="control-label"><?= _l('name') ?></label>
                            <span class="pull-right"><?= $company_info->name ?></span>
                        </div>
                        <div class="tw-border-b tw-border-solid tw-border-neutral-200 tw-py-2 tw-px-2 tw-mb-1">
                            <label class="control-label"><?= _l('email') ?></label>
                            <span class="pull-right"><?= $company_info->email ?></span>
                        </div>
                        <?php
                        $links = all_company_url($company_info->domain);
                        foreach ($links as $lname => $link) {
                            ?>
                            <div class="bb tw-py-2 tw-px-2 tw-mb-1 tw-flex tw-items-center tw-justify-between">
                                <label class="control-label"><?= _l($lname) . ' ' . _l('link') ?></label>
                                <div class="pull-right">
                                    <a target="_blank" href="<?= $link ?>"> <?= _l('login_customer_contacts') ?></a>
                                    <a href="javascript:void(0)" class="copy-link tw-ml-2" data-toggle="tooltip"
                                       title="<?= _l('copy') ?>"
                                       data-clipboard-text="<?= $link ?>"><i
                                                class="fa fa-clipboard"></i>
                                    </a>
                                    <br/>
                                    <a target="_blank" href="<?= $link ?>admin"> <?= _l('admin_login_staff') ?></a>
                                    <a href="javascript:void(0)" class="copy-link tw-ml-2" data-toggle="tooltip"
                                       title="<?= _l('copy') ?>"
                                       data-clipboard-text="<?= $link ?>admin"><i
                                                class="fa fa-clipboard"></i>
                                    </a>
                                </div>
                            </div>
                        <?php }
                        ?>

                        <div class="tw-border-b tw-border-solid tw-border-neutral-200 tw-py-2 tw-px-2 tw-mb-1">
                            <label class="control-label"><?= _l('package') ?></label>
                            <span class="pull-right"><?= $plan_name ?></span>
                        </div>
                        <div class="tw-border-b tw-border-solid tw-border-neutral-200 tw-py-2 tw-px-2 tw-mb-1">
                            <label class="control-label"><?= _l('created_date') ?></label>
                            <span class="pull-right"><?= _dt($company_info->created_date) ?></span>
                        </div>
                        <div class="tw-border-b tw-py-2 tw-px-2 tw-mb-1">
                            <a href="<?= base_url('admin/customizePackages') ?>"
                               class="btn btn-sm btn-primary"><?= _l('customize') . ' ' . _l('package') ?>
                            </a>
                            <a href="<?= base_url('updatePackage') ?>"
                               class="btn btn-sm btn-info  pull-right"><?= _l('upgrade') . ' ' . _l('package') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo csrf_jquery_token() ?>
        <!--  **************** show when print End ********************* -->
        <div class="col-sm-8 ">
            <div class="panel panel-custom">
                <!-- Default panel contents -->
                <div class="panel-heading">
                    <div class="panel-title">
                        <strong><?= _l('subscriptions') . ' ' . _l('histories') ?></strong>
                    </div>
                </div>

                <!-- Table -->
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped da" id="subsTable">
                            <thead>
                            <tr>
                                <th><?= _l('name') ?></th>
                                <th><?= _l('amount') ?></th>
                                <th><?= _l('created_date') ?></th>
                                <th><?= _l('validity') ?></th>
                                <th><?= _l('method') ?></th>

                            </tr>
                            </thead>
                            <tbody id="pricing">
                            <script>

                                initDataTable('#subsTable', base_url + "admin/companyHistoryList/" + '<?= $company_info->companies_id ?>', undefined, undefined, undefined);

                            </script>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!--************ Payment History End***********-->
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-custom">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <div class="panel-title">
                    <strong><?= _l('payment') . ' ' . _l('histories') ?></strong>
                </div>
            </div>

            <!-- Table -->
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped da" id="PaymentDataTables">
                        <thead>
                        <tr>
                            <th><?= _l('package_name') ?></th>
                            <th><?= _l('transaction_id') ?></th>
                            <th><?= _l('amount') ?></th>
                            <th><?= _l('payment_date') ?></th>
                            <th><?= _l('method') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <script>
                            initDataTable('#PaymentDataTables', base_url + "admin/companyPaymentList/" + '<?= $company_info->companies_id ?>', undefined, undefined, undefined);
                        </script>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!--************ Payment History End***********-->
    </div>
</div>
<script type="text/javascript">
    'use strict';
    // click on copy-link to copy to clipboard
    $('.copy-link').on('click', function () {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(this).data('clipboard-text')).select();
        document.execCommand("copy");
        $temp.remove();
        alert_float('success', '<?= _l('copied') ?>');
    });
</script>