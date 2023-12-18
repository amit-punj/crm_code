<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style type="text/css">
    table.table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .hide,.vat_hide {display: none;}
    .show,.vat_show {display: block;}
    .merged-input-box {
        display: flex; 
    }
    .gst_code1{
        border-right-style: none;
        border-top-right-radius: revert !important;
        border-bottom-right-radius: revert !important;
        width: 50px;
    }
    .gst_number1{
        border-left-style: none;
        border-top-left-radius: revert !important;
        border-bottom-left-radius: revert !important;
    }
</style>
<div role="tabpanel" class="tab-pane" id="company_info">
    <div class="horizontal-scrollable-tabs panel-full-width-tabs">
        <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
        <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
        <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
                <li role="presentation" class="active">
                    <a href="#general" aria-controls="general" role="tab" data-toggle="tab"><?php echo _l('settings_sales_heading_general'); ?></a>
                </li>
                <?php 
                    if(get_option('company_default_country') == 102){
                        $vat_hide_show  = 'hide';
                        $hide_show      = 'show';
                    } else {
                        $vat_hide_show  = 'show';
                        $hide_show      = 'hide';
                    }
                ?>
                <li role="presentation" class="gst_tab <?= $hide_show ?>">
                    <a href="#gst" aria-controls="gst" role="tab" data-toggle="tab"><?php echo _l('GST'); ?></a>
                </li>
                <li role="presentation" class="gst_tab <?= $hide_show ?>">
                    <a href="#einvoice" aria-controls="einvoice" role="tab" data-toggle="tab"><?php echo _l('E-INVOICE'); ?></a>
                </li>
                <?php hooks()->do_action('after_finance_settings_last_tab'); ?>
            </ul>
        </div>
    </div>
    <div class="tab-content mtop15">
        <div role="tabpanel" class="tab-pane active" id="general">
            <div class="alert alert-info">
                <?php echo _l('settings_sales_company_info_note'); ?>
            </div>
            <?php echo render_input('settings[invoice_company_name]', 'settings_sales_company_name', get_option('invoice_company_name')); ?>
            <?php echo render_select('settings[company_default_country]', get_all_countries(), [ 'country_id', [ 'short_name']], 'Default Country', get_option('company_default_country')); ?>
            <?php echo render_input('settings[invoice_company_address]', 'settings_sales_address', get_option('invoice_company_address')); ?>
            <?php echo render_input('settings[invoice_company_city]', 'settings_sales_city', get_option('invoice_company_city')); ?>
            <?php //if(get_option('company_default_country') != 102) {?>
                <div class="form-group vat_div <?=$vat_hide_show?>" app-field-wrapper="settings[company_state]">
                    <label for="settings[company_state]" class="control-label">State</label>
                    <input type="text" id="settings[company_state]" name="settings[company_state]" class="form-control" value="Chandigarh">
                </div>
             <?php// } else {?>
                <div class="form-group gst_tab <?= $hide_show ?>">
                    <label class="control-label" for="state"><?php echo _l('State'); ?></label>
                    <select class="selectpicker display-block" data-width="100%" name='settings[company_state]' data-none-selected-text="<?php echo _l('No State Selelcted'); ?>">
                        <?php foreach ($states as $state) { ?>
                        <option <?= ($state['state'] == get_option('company_state')) ? 'selected':''?> value="<?= $state['state']; ?>"><?php echo $state['state']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php // } ?>
            <?php echo render_input('settings[invoice_company_country_code]', 'settings_sales_country_code', get_option('invoice_company_country_code')); ?>
            <?php echo render_input('settings[invoice_company_postal_code]', 'settings_sales_postal_code', get_option('invoice_company_postal_code')); ?>
            <?php echo render_input('settings[invoice_company_phonenumber]', 'settings_sales_phonenumber', get_option('invoice_company_phonenumber')); ?>            
            <?php echo render_custom_fields('company', 0); ?>
            <?php echo render_input('settings[company_vat]','company_vat_number',get_option('company_vat'),'text',[],[],'vat_div '.$vat_hide_show,''); ?>
            <hr />
            <?php echo render_textarea('settings[company_info_format]', 'company_info_format', clear_textarea_breaks(get_option('company_info_format')), ['rows' => 8, 'style' => 'line-height:20px;']); ?>
            <p>
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{company_name}</a>
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{address}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{city}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{state}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{zip_code}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{country_code}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{phone}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{vat_number}</a>,
                <a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{vat_number_with_label}</a>
            </p>
            <?php $custom_company_fields = get_company_custom_fields();
            if (count($custom_company_fields) > 0) {
                echo '<hr />';
                echo '<p class="font-medium"><b>' . _l('custom_fields') . '</b></p>';
                echo '<ul class="list-group">';
                foreach ($custom_company_fields as $field) {
                    echo '<li class="list-group-item"><b>' . $field['name'] . '</b>: ' . '<a href="#" class="settings-textarea-merge-field" data-to="company_info_format">{cf_' . $field['id'] . '}</a></li>';
                }
                echo '</ul>';
                echo '<hr />';
            }
            ?>
        </div>
        <div role="tabpanel" class="tab-pane gst_tab " id="gst">
            <div class="div_if_india">
                <input type="hidden" name="gst_id" value="<?= $gst_details->id ?? '' ?>">
                <?php 
                    $validation = ['maxlength' => 15, 'minlength'=>15];
                ?>
                <div class="form-group" app-field-wrapper="settings[gst_number]">
                    <label for="settings[gst_number]" class="control-label">GST Number <?= $this->input->get('tab')?></label>
                    <div class="merged-input-box">
                    <input type="text" id="settings[gst_code]"   <?= ($this->input->get('tab') =='gst') ? 'required':'' ?>  name="settings[gst_code]" class="form-control gst_code1" value="<?=$gst_details->gst_number?substr($gst_details->gst_number,0,2):''?>" readonly>
                    <input type="text" id="settings[gst_number]" <?= ($this->input->get('tab') =='gst') ? 'required':'' ?>  name="settings[gst_number]" class="form-control gst_number1" maxlength="13" minlength="13" value="<?=$gst_details->gst_number?substr($gst_details->gst_number,2):''?>">
                    </div>
                </div>    
                <?php //echo render_input('settings[gst_number]', 'GST Number', $gst_details->gst_number??'','text',$validation);?>
                <?php echo render_input('settings[pan_number]', 'PAN Number', $gst_details->pan_number ?? ''); ?>
                <?php echo render_input('settings[gstin]', 'GSTIN', $gst_details->gstin ?? ''); ?>
                <?php echo render_input('settings[gst_user_id]', 'GST user ID', $gst_details->gst_user_id ?? ''); ?>
                <?php echo render_input('settings[gst_password]', 'GST Password', $gst_details->gst_password ?? ''); ?>
                <?php echo render_input('settings[otp]', 'OTP', $gst_details->otp ?? ''); ?>
                <?php echo render_input('settings[authorised_person_name]','Authorised Person Name',$gst_details->authorised_person_name??'');?>
                <?php echo render_input('settings[father_name]', 'Father Name', $gst_details->father_name ?? ''); ?>
                <?php echo render_input('settings[address]', 'Address', $gst_details->address ?? ''); ?>
                <?php echo render_input('settings[din_number]', 'DIN Number', $gst_details->din_number ?? ''); ?>
                <?php echo render_input('settings[designation]', 'Designation', $gst_details->designation ?? ''); ?>
                <?php echo render_input('settings[bank_name]', 'Bank Name', $gst_details->bank_name ?? ''); ?>
                <?php echo render_input('settings[bank_account_number]', 'Bank Account Number', $gst_details->bank_account_number ?? ''); ?>
                <?php echo render_input('settings[bank_ifsc_code]', 'Bank IFSC Code', $gst_details->bank_ifsc_code ?? ''); ?>
                <?php echo render_input('settings[bank_address]', 'Bank Address', $gst_details->bank_address ?? ''); ?>
                <div class="form-group">
                    <label for="enable_default_gst" class="control-label clearfix">Enable Default GST</label>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="y_opt_1_enable_default_gst" <?= ($gst_details->enable_default_gst == 1) ?'checked' : ''?> name="settings[enable_default_gst]" value="1">
                        <label for="y_opt_1_enable_default_gst">Yes</label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="y_opt_2_enable_default_gst" <?= ($gst_details->enable_default_gst == 0) ?'checked' : ''?> name="settings[enable_default_gst]" value="0">
                        <label for="y_opt_2_enable_default_gst">No</label>
                    </div>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>GST Number</th>
                        <th>PAN Number</th>
                        <th>GST user ID</th>
                        <th>GST Password</th>
                        <th>Authorised Person Name</th>
                        <th>Father Name</th>
                        <th>Address</th>
                        <th>DIN Number</th>
                        <th>Designation</th>
                        <th>Bank Name</th>
                        <th>Bank Account Number</th>
                        <th>Bank IFSC Code</th>
                        <th>Bank Address</th>
                        <th>Default GST</th>
                        <th>Action</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php foreach ($gsts ?? [] as $key => $value) { ?>
                            <tr>
                                <td><?php echo $value['gst_code'].$value['gst_number'];?></td>
                                <td><?php echo $value['pan_number'];?></td>
                                <td><?php echo $value['gst_user_id'];?></td>
                                <td><?php echo $value['gst_password'];?></td>
                                <td><?php echo $value['authorised_person_name'];?></td>
                                <td><?php echo $value['father_name'];?></td>
                                <td><?php echo $value['address'];?></td>
                                <td><?php echo $value['din_number'];?></td>
                                <td><?php echo $value['designation'];?></td>
                                <td><?php echo $value['bank_name'];?></td>
                                <td><?php echo $value['bank_account_number'];?></td>
                                <td><?php echo $value['bank_ifsc_code'];?></td>
                                <td><?php echo $value['bank_address'];?></td>
                                <td><?= ($value['enable_default_gst'] == 1) ? 'Default': '';?></td>
                                <td>
                                    <?php 
                                        $edit_url   = admin_url('settings').'?group=company&tab=gst&id='.$value['id'];
                                        $delete_url = admin_url('settings/delete_gst/').$value['id'];
                                    ?>
                                    <a href="<?php echo $edit_url?>"><?=_l('edit')?></a>
                                    <a style="color: red;" href="<?php echo $delete_url?>" onclick="return confirm('Are you sure?')"><?=_l('delete')?></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
            </table> 
        </div>
        <div role="tabpanel" class="tab-pane gst_tab " id="einvoice">
            <div class="div_if_india">
                <div class="form-group">
                    <label for="enable_default_gst" class="control-label clearfix">E-Invoice Applicable</label>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="enable_einvoice_1" <?= (get_option('einvoice_applicable') == 1) ?'checked' : ''?> name="settings[einvoice_applicable]" value="1">
                        <label for="enable_einvoice_1">Yes</label>
                    </div>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" id="enable_einvoice_2" <?= (get_option('einvoice_applicable') == 0) ?'checked' : ''?> name="settings[einvoice_applicable]" value="0">
                        <label for="enable_einvoice_2">No</label>
                    </div>
                </div>
                <div class="form-group">
                    <input type="hidden" name="einvoice_id" value="<?= $einvoice_details->id ?? '' ?>">
                    <label class="control-label" for=""><?php echo _l('Select GST Number'); ?></label>
                    <select class="selectpicker display-block" data-width="100%" name='gst_number' data-none-selected-text="<?php echo _l('No GST Selected'); ?>">
                        <option value=""></option>
                        <?php foreach ($gsts as $gst) { 
                            if(!in_array($gst['gst_number'], $exist_gsts)){
                        ?>
                            <option <?= ($gst['gst_number'] == $einvoice_details->gst_number) ? 'selected':'jj'?> value="<?= $gst['gst_number']; ?>"><?php echo $gst['gst_number']; ?></option>
                        <?php } else if(isset($einvoice_details) && !empty($einvoice_details->gst_number) && $einvoice_details->gst_number ==  $gst['gst_number']){ ?>
                                <option <?= ($gst['gst_number'] == $einvoice_details->gst_number) ? 'selected':'kk'?> value="<?= $gst['gst_number']; ?>"><?php echo $gst['gst_number']; ?></option>
                        <?php } }?>
                    </select>
                </div>
                <?php echo render_input('user_name', 'User Name', $einvoice_details->user_name ?? ''); ?>
                <?php echo render_input('password', 'Password', $einvoice_details->password ?? ''); ?>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>GST Number</th>
                        <th>User Name</th>
                        <th>Password</th>
                        <!-- <th>Enable Default GST</th> -->
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($einvoices ?? [] as $key => $value) { ?>
                        <tr>
                            <td><?= $value['gst_number'];?></td>
                            <td><?= $value['user_name'];?></td>
                            <td><?= $value['password'];?></td>
                            <!-- <td><?php // ($value['einvoice_applicable'] == 1) ? 'Yes': 'No';?></td> -->
                            <td>
                                <?php 
                                    $edit_url   = admin_url('settings').'?group=company&tab=einvoice&einvoice_id='.$value['id'];
                                    $delete_url = admin_url('settings/delete_einvoice/').$value['id'];
                                ?>
                                <a href="<?php echo $edit_url?>"><?=_l('edit')?></a>
                                <a style="color: red;" href="<?php echo $delete_url?>" onclick="return confirm('Are you sure?')"><?=_l('delete')?></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table> 
        </div>
    </div>
</div>