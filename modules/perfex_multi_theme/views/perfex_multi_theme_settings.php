<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php echo form_open_multipart($this->uri->uri_string('main'), array('id' => 'perfex_multi_theme_setup',));?>
 <?php $enabled = get_option('perfex_multi_theme_clients'); ?>
<?php $dashboard_image = get_option('dashboard_bg_image'); ?>
<?php $login_image = get_option('login_bg_image'); ?>
<div class="form-group">
    <label for="perfex_multi_theme_clients" class="control-label clearfix">
        <?= _l('perfex_multi_theme_settings'); ?>
    </label>
    <hr>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_1_perfex_multi_theme_clients_enabled" name="is_mt_client" value="1" <?= ($enabled == '1') ? ' checked' : '' ?>>
        <label for="y_opt_1_perfex_multi_theme_clients_enabled">
            <?= _l('settings_yes'); ?>
        </label>
    </div>
    <div class="radio radio-primary radio-inline">
        <input type="radio" id="y_opt_2_admin-multi_theme_enabled" name="is_mt_client" value="0" <?= ($enabled == '0') ? ' checked' : '' ?>>
        <label for="y_opt_2_admin-multi_theme_enabled">
            <?= _l('settings_no'); ?>
        </label>
    </div>
</div>
<hr/>
<?php if($login_image != ''){ ?>
			<div class="row">
				<div class="col-md-4">
                <?php echo _l('Login Background Image'); ?> <br/> <br/>
					<img src="<?php echo base_url('uploads/company/'.$login_image); ?>" class="img img-responsive" height="300" width="300">
				</div>
				<?php if(has_permission('settings','','delete')){ ?>
					<div class="col-md-8 text-left">
						<a href="<?php echo base_url('perfex_multi_theme/main/remove_login_bg_image'); ?>" data-toggle="tooltip" title="<?php echo _l('remove_login_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_logo" class="control-label"><?php echo _l('Login Background Image'); ?></label>
				<input type="file" name="login_bg_image" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>">
			</div>
		<?php } ?>
        <hr/>
 <?php if($dashboard_image != ''){ ?>
			<div class="row">
				<div class="col-md-4">
                <?php echo _l('Dashboard Background Image'); ?> <br/> <br/>
					<img src="<?php echo base_url('uploads/company/'.$dashboard_image); ?>" class="img img-responsive"height="300" width="300">
				</div>
				<?php if(has_permission('settings','','delete')){ ?>
					<div class="col-md-8 text-left">
						<a href="<?php echo base_url('perfex_multi_theme/main/remove_dashboard_bg_image'); ?>" data-toggle="tooltip" title="<?php echo _l('remove_dashboard_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
					</div>
				<?php } ?>
			</div>
			<div class="clearfix"></div>
		<?php } else { ?>
			<div class="form-group">
				<label for="company_logo" class="control-label"><?php echo _l('Dashboard Background Image'); ?></label>
				<input type="file" name="dashboard_bg_image" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_company_logo_tooltip'); ?>">
			</div>
		<?php } ?>
        <div class="btn-bottom-toolbar text-right">
                     <button type="submit" class="btn btn-info">Save</button>
                  </div>
            
            <?php echo form_close(); ?>
            <!-- </form> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>