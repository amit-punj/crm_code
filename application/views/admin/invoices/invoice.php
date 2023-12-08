<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php
                echo form_open($this->uri->uri_string(), ['id' => 'invoice-form', 'class' => '_transaction_form invoice-form']);
                if (isset($invoice)) {
                    echo form_hidden('isedit');
                }
            ?>
            <div class="col-md-12">
                <h4
                    class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span>
                        <?php echo isset($invoice) ? format_invoice_number($invoice) : _l('create_new_invoice'); ?>
                    </span>
                    <?php echo isset($invoice) ? format_invoice_status($invoice->status) : ''; ?>
                </h4>
                <?php $this->load->view('admin/invoices/invoice_template'); ?>
            </div>
            <?php echo form_close(); ?>
            <?php  $this->load->view('admin/invoice_items/item'); ?>
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
$('.supply_type').on('change', function(){
    var val = $(this).val();
    if(val == 'export' || val == 'sez'){
        $('.payment_tax_type_div').show();
    } else {
        $('.payment_tax_type_div').hide();
    }
});
$('.supply_type').on('change', function(){
    var val = $(this).val();
    var payment_tax_type = $('.payment_tax_type').selectpicker('val')
    if(val == 'no_gst' || val == 'exempt' || val == 'nil_rated') {
        console.log('coming in if')
        $('.tax').prop("disabled", true).selectpicker("refresh"); 
    } else if(val == 'regular') {
        console.log('coming in else if')
        $('.tax').removeAttr("disabled").selectpicker("refresh");
    } else {
        if(payment_tax_type == 'without_payment_taxes') {
            console.log('coming in else')
            $('.tax').prop("disabled", true).selectpicker("refresh");
        } else {
            $('.tax').removeAttr("disabled").selectpicker("refresh");
        }
    }
});
$('.payment_tax_type').on('change', function(){
    var val = $(this).val();
    var supply_type = $('.supply_type').selectpicker('val');
    console.log('2nd val',val)
    console.log('2nd supply_type',supply_type)
    if(val == 'without_payment_taxes' && (supply_type == 'export' || supply_type == 'sez')) {
        console.log('coming in if')
        $('.tax').prop("disabled", true).selectpicker("refresh");
    } else {
        console.log('coming in else')
        $('.tax').removeAttr("disabled").selectpicker("refresh");
    }
});
</script>
</body>

</html>