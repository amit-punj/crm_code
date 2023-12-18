<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .center{
        text-align: center;
    }
    .background_color{
        background: #1c8bec;
        color: white;
        padding: 5px 30px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
                // echo form_open($this->uri->uri_string(), ['id' => 'invoice-form', 'class' => '_transaction_form invoice-form']);
                // if (isset($invoice)) {
                //     echo form_hidden('isedit');
                // }
            ?>
            <div class="col-md-12">
                <h4
                    class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span>
                        <?php echo _l('GST'); ?>
                    </span>
                </h4>
                <div class="panel_s invoice accounting-template">
                    <div class="additional"></div>
                    <div class="panel-body">
                        <div role="tabpanel" class="tab-pane" id="company_info">
                            <div class="horizontal-scrollable-tabs panel-full-width-tabs">
                                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                <div class="horizontal-tabs">
                                    <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                                        <li role="presentation" class="">
                                            <a href="#return_dashboard" aria-controls="return_dashboard" role="tab" data-toggle="tab"><?php echo _l('Return Dashboard'); ?></a>
                                        </li>
                                        <li role="presentation" class="active">
                                            <a href="#report_dashboard" aria-controls="report_dashboard" role="tab" data-toggle="tab"><?php echo _l('Report Dashboard'); ?></a>
                                        </li>
                                        <?php hooks()->do_action('after_finance_settings_last_tab'); ?>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="tab-content mtop15">
                                <div role="tabpanel" class="tab-pane" id="return_dashboard">
                                </div>
                                <div role="tabpanel" class="tab-pane report_dashboard_tab active" id="report_dashboard">
                                    <div class="row">
                                        <div class="col-md-4 center">
                                            <span class ="background_color">GSTR-1 Reports</span>
                                            <div class="row" style="margin-top: 10px;">
                                                <a target="_blank" href=" <?php echo admin_url('gst/gstr1b2binvoices') ?>" >GSTR-1 Get B2B Invoices </a>
                                            </div>
                                            <div class="row" style="margin-top: 10px;">
                                                <a target="_blank" href="<?php echo admin_url('gst/gstr1b2clinvoices') ?>">GSTR-1 Get B2CL Invoices </a>
                                            </div>
                                            <div class="row" style="margin-top: 10px;">
                                                <a target="_blank" href="<?php echo admin_url('gst/gstr1b2csinvoices') ?>">GSTR-1 Get B2CS Invoices </a>
                                            </div>
                                            <div class="row" style="margin-top: 10px;">
                                                <a target="_blank" href="<?php echo admin_url('gst/gstr1cdnrinvoices') ?>">GSTR-1 Get CDNR Invoices </a>
                                            </div>
                                            <div class="row" style="margin-top: 10px;">
                                                <a data-toggle="modal" data-target="#exampleModal" class="openModalBtn">GSTR-1 Get CDNUR Invoices </a>
                                            </div>
                                            <div class="row">
                                                <a data-toggle="modal" data-target="#exampleModal" class="openModalBtn">GSTR-1 Yearly Sales Summary </a>
                                            </div>
                                            <div class="row">
                                                <a data-toggle="modal" data-target="#exampleModal" class="openModalBtn">GSTR-1 Rate Wise Sales </a>
                                            </div>
                                            <div class="row">
                                                <a data-toggle="modal" data-target="#exampleModal" class="openModalBtn">Export Full Year GSTR-1 in Excel </a>
                                            </div>
                                        </div>
                                        <div class="col-md-4 center">
                                            <span class= "background_color"> GSTR-3B Reports</span>
                                        </div>
                                        <div class="col-md-4 center">
                                            <span class="background_color" >GSTR-2A/4A Reports</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php //echo form_close(); ?>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Select Month/Year</h5>
        </div>
        <div class="modal-body">
            <form action="<?= base_url('gst/process_selection') ?>" method="post">
                <div class="form-group">
                    <label class="control-label" for=""><?php echo _l('Month'); ?></label>
                    <select class="selectpicker display-block" data-width="100%" name='month' data-none-selected-text="<?php echo _l('No Month Selected'); ?>">
                        <option value=""></option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="control-label" for=""><?php echo _l('Year'); ?></label>
                    <select class="selectpicker display-block" data-width="100%" name='year' data-none-selected-text="<?php echo _l('No Year Selected'); ?>">
                        <option value=""></option>
                        <?php for ($i = date('Y'); $i >= date('Y') - 20; $i--) : ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <a href="<?php echo admin_url('gst') ?>" type="button" class="btn btn-primary">Save changes</a>
        </div>
    </div>
    </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
$(function() {
    validate_invoice_form();
    // Init accountacy currency symbol
    init_currency();
    // Project ajax search
    init_ajax_project_search_by_customer_id();
    // Maybe items ajax search
    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
});

// JavaScript to open and close the modal
document.getElementById('openModalBtn').addEventListener('click', openModal);

function openModal() {
    document.getElementById('monthYearModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('monthYearModal').style.display = 'none';
}
</script>
</body>

</html>