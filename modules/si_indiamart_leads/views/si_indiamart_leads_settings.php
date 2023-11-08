<?php defined('BASEPATH') or exit('No direct script access allowed'); 
$leads_seconds = get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_seconds');
$lead_minutes = 0;
if(is_numeric($leads_seconds))
	$lead_minutes = round($leads_seconds/60);
?>
<ul class="nav nav-tabs" role="tablist">
	<li role="presentation"  class="active">
		<a href="#si_iml_settings_tab1" aria-controls="si_iml_settings_tab1" role="tab" data-toggle="tab"><?php echo _l('si_iml_settings_tab1'); ?></a>
	</li>
</ul>
<div class="tab-content mtop30">
	<div role="tabpanel" class="tab-pane  active" id="si_iml_settings_tab1">
		<?php if(!get_option(SI_INDIAMART_MODULE_NAME.'_activated') || get_option(SI_INDIAMART_MODULE_NAME.'_activation_code')==''){?>
		<div class="row" id="si_iml_validate_wrapper" data-wait-text="<?php echo '<i class=\'fa fa-spinner fa-pulse\'></i> '._l('wait_text'); ?>" data-original-text="<?php echo _l('si_iml_settings_validate'); ?>">
			<div class="col-md-9">
				<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('si_iml_settings_purchase_code_help'); ?>"></i>
				<?php echo render_input('settings['.SI_INDIAMART_MODULE_NAME.'_activation_code]','si_iml_settings_activation_code',get_option(SI_INDIAMART_MODULE_NAME.'_activation_code'),'text',array('data-toggle'=>'tooltip','data-title'=>_l('si_iml_settings_purchase_code_help'),'maxlength'=>60)); 
					echo form_hidden('settings['.SI_INDIAMART_MODULE_NAME.'_activated]',get_option(SI_INDIAMART_MODULE_NAME.'_activated'));
				?>
				<span><?php echo _l('si_iml_settings_valid_purchase_help'); ?></span>
				<span><a target="_blank" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"><?php echo _l('setup_help'); ?></a></span>
			</div>
			<div class="col-md-3 mtop25">
				<button id="si_iml_validate" class="btn btn-success"><?php echo _l('si_iml_settings_validate');?></button>
			</div>
			<div class="col-md-12" id="si_iml_validate_messages" class="mtop25 text-left"></div>
		</div>
		<?php } else {?>
		<div class="row">
			<div class="col-md-12">
				<i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('si_iml_settings_api_key_help'); ?>"></i>
				<?php echo render_input('settings['.SI_INDIAMART_MODULE_NAME.'_api_key]','si_iml_settings_api_key',get_option(SI_INDIAMART_MODULE_NAME.'_api_key'),'text',array('data-toggle'=>'tooltip','data-title'=>_l('si_iml_settings_api_key_help'),'required'=>true,'maxlength'=>60)); ?>
			</div>
		</div>
		<hr />
		<div class="row">	
			<div class="col-md-12">
				<?php echo render_yes_no_option(SI_INDIAMART_MODULE_NAME.'_trigger_auto_retry_enable','si_iml_settings_trigger_auto_retry_enable'); ?>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="col-md-12">
				<label><?php echo _l('si_iml_settings_trigger_retry_leads_minutes'); ?></label>
				<p class="text-warning"> (<?php echo _l('si_iml_settings_leads_minute_instruction')?>)</p>
				<div class="input-group">
					<input type="number" class="form-control" name="settings[<?php echo SI_INDIAMART_MODULE_NAME?>_trigger_retry_leads_minutes]" value="<?php echo get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_minutes'); ?>" min="0" maxlength="1440">
					<div class="input-group-addon">
						<span><?php echo _l('minutes'); ?></span>
					</div>
				</div>
				<h5 class="mbot15 pull-right"><?php echo _l('si_iml_settings_trigger_retry_leads_last_run',_dt(get_option(SI_INDIAMART_MODULE_NAME.'_trigger_retry_leads_last_run')));?></h5>
			</div>
		</div>
		<hr/>
		<div class="row">
			<div class="col-md-12">
				<h5 class="mbot15"><?php echo _l('si_iml_settings_leads_default_values')?></h5>
			</div>
			<div class="col-md-4 mtop10">
				<?php 
				echo render_select('settings['.SI_INDIAMART_MODULE_NAME.'_lead_status]',get_instance()->leads_model->get_status(),array('id','name'),'lead_import_status',get_option(SI_INDIAMART_MODULE_NAME.'_lead_status'),array('data-width'=>'100%','required'=>true),array(),'no-mbot','',true);?>
			</div>
			<div class="col-md-4 mtop10">
				<?php 
				echo render_select('settings['.SI_INDIAMART_MODULE_NAME.'_lead_source]',get_instance()->leads_model->get_source(),array('id','name'),'lead_import_source',get_option(SI_INDIAMART_MODULE_NAME.'_lead_source'),array('data-width'=>'100%','required'=>true),array(),'no-mbot','',true);?>
			</div>
			<div class="col-md-4 mtop10">
				<?php 
				echo render_select('settings['.SI_INDIAMART_MODULE_NAME.'_lead_assigned]',get_instance()->staff_model->get('', ['active' => 1]),array('staffid',array('firstname','lastname')),'leads_import_assignee',get_option(SI_INDIAMART_MODULE_NAME.'_lead_assigned'),array('data-width'=>'100%'),array(),'no-mbot','',true);?>
			</div>
			
		</div>
		<?php } ?>
		<hr/>
	</div>
</div>