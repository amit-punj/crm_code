<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <?php $company_logo = get_option('saas_company_logo'); ?>
        <?php $company_logo_dark = get_option('saas_company_logo_dark'); ?>
        <?php if ($company_logo != '') { ?>
            <div class="row">
                <div class="col-md-9">
                    <img src="<?php echo base_url('uploads/company/' . $company_logo); ?>" class="img img-responsive">
                </div>
                <?php if (has_permission('settings', '', 'delete')) { ?>
                    <div class="col-md-3 text-right">
                        <a href="<?php echo saas_url('settings/remove_company_logo'); ?>" data-toggle="tooltip"
                           title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>"
                           class="_delete text-danger"><i class="fa fa-remove"></i></a>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        <?php } else { ?>
            <div class="form-group">
                <label for="saas_company_logo"
                       class="control-label"><?php echo _l('settings_general_company_logo'); ?></label>
                <input type="file" name="saas_company_logo" class="form-control" value="" data-toggle="tooltip"
                       title="<?php echo _l('settings_general_company_logo_tooltip'); ?>">
            </div>
        <?php } ?>
        <hr/>
        <?php if ($company_logo_dark != '') { ?>
            <div class="row">
                <div class="col-md-9">
                    <img src="<?php echo base_url('uploads/company/' . $company_logo_dark); ?>"
                         class="img img-responsive">
                </div>
                <?php if (has_permission('settings', '', 'delete')) { ?>
                    <div class="col-md-3 text-right">
                        <a href="<?php echo saas_url('settings/remove_company_logo/dark'); ?>" data-toggle="tooltip"
                           title="<?php echo _l('settings_general_company_remove_logo_tooltip'); ?>"
                           class="_delete text-danger"><i class="fa fa-remove"></i></a>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
        <?php } else { ?>
            <div class="form-group">
                <label for="saas_company_logo_dark" class="control-label"><?php echo _l('company_logo_dark'); ?></label>
                <input type="file" name="saas_company_logo_dark" class="form-control" value="" data-toggle="tooltip"
                       title="<?php echo _l('settings_general_company_logo_tooltip'); ?>">
            </div>
        <?php } ?>
        <hr/>
        <?php $favicon = get_option('favicon'); ?>
        <?php if ($favicon != '') { ?>
            <div class="form-group favicon">
                <div class="row">
                    <div class="col-md-9">
                        <img src="<?php echo base_url('uploads/company/' . $favicon); ?>" class="img img-responsive">
                    </div>
                    <?php if (has_permission('settings', '', 'delete')) { ?>
                        <div class="col-md-3 text-right">
                            <a href="<?php echo saas_url('settings/remove_fv'); ?>" class="_delete text-danger"><i
                                        class="fa fa-remove"></i></a>
                        </div>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div>
        <?php } else { ?>
            <div class="form-group favicon_upload">
                <label for="favicon" class="control-label"><?php echo _l('settings_general_favicon'); ?></label>
                <input type="file" name="favicon" class="form-control">
            </div>
        <?php } ?>
        <hr/>
        <?php $attrs = (get_option('saas_companyname') != '' ? array() : array('autofocus' => true)); ?>
        <?php echo render_input('settings[saas_companyname]', 'settings_general_company_name', get_option('saas_companyname'), 'text', $attrs); ?>
        <hr/>
        <?php echo render_input('settings[saas_allowed_files]', 'settings_allowed_upload_file_types', get_option('saas_allowed_files')); ?>
        <hr/>
        <div class="proxy row">
            <div class="col-sm-8">
                <label for="settings[saas_landing_page_url]"
                       class="control-label"><?php echo _l('landing_page_url') ?>
                    <span class="tw-ml-2" data-toggle="tooltip"
                          title="<?= _l('landing_page_url_tooltip') ?>"
                    >
                    <i class="fa fa-question-circle"></i>
                </span>
                </label>
                <?php $value = (!empty(get_option('saas_landing_page_url')) ? get_option('saas_landing_page_url') : companyBaseUrl());
                // remove last slash if exists
                $value = rtrim($value, '/');
                ?>
                <input type="text"
                       id="settings[saas_landing_page_url]"
                       name="settings[saas_landing_page_url]"
                       class="form-control"
                       value="<?= $value ?>"/>
            </div>
            <div class="col-sm-4">
                <label for="settings[saas_landing_page_url_mode]"
                       class="control-label"><?php echo _l('landing_page_url_mode') ?>
                    <span class="tw-ml-2" data-toggle="tooltip"
                          title="<?= _l('saas_landing_page_url_mode_tooltip') ?>"
                    >
                    <i class="fa fa-question-circle"></i>
                </span>
                </label>
                <?php
                $svalue = get_option('saas_landing_page_url_mode');
                $mode = [
                    [
                        'key' => 'default',
                        'value' => _l('default')
                    ],
                    [
                        'key' => ('proxy'),
                        'value' => _l('proxy'),
                    ],
                    [
                        'key' => 'redirection',
                        'value' => _l('redirection')
                    ]
                ]
                ?>
                <?= render_select('settings[saas_landing_page_url_mode]', $mode, ['key', ['value']], '', (!empty($svalue) ? $svalue : 'default')); ?>
            </div>
        </div>
        <hr/>
        <?php echo render_yes_no_option('disable_frontend', 'disable_frontend', _l('disable_frontend_help')); ?>
        <hr/>
        <?php echo render_yes_no_option('saas_force_redirect_to_dashboard', 'saas_force_redirect_to_dashboard', _l('saas_force_redirect_to_dashboard_help')); ?>
        <hr/>

        <div class="form-group">
            <label for="calculate_disk_space"><?php echo _l('calculate_disk_space'); ?></label><br/>

            <div class="radio radio-inline radio-primary">
                <input type="radio" name="settings[saas_calculate_disk_space]"
                       value="both" <?php if (get_option('saas_calculate_disk_space') == 'both' || get_option('saas_calculate_disk_space') == '') {
                    echo 'checked';
                } ?>>
                <label for="both"><?php echo _l('both'); ?></label>
            </div>
            <div
                    class="radio radio-inline radio-primary">
                <input type="radio" name="settings[saas_calculate_disk_space]" id="media"
                       value="media" <?php if (get_option('saas_calculate_disk_space') == 'media') {
                    echo 'checked';
                } ?>>
                <label for="media"><?= _l('only_media') ?></label>
            </div>
            <div
                    class="radio radio-inline radio-primary ">
                <input type="radio" name="settings[saas_calculate_disk_space]" id="upload"
                       value="upload" <?php if (get_option('saas_calculate_disk_space') == 'upload') {
                    echo 'checked';
                } ?>>
                <label for="upload"><?= _l('only_upload') ?></label>
            </div>
        </div>
        <hr/>
        <div class="form-group" app-field-wrapper="settings[saas_reserved_tenant]">
            <label for="settings[saas_reserved_tenant]" class="control-label"><?php echo _l('saas_reserved_tenant') ?>
                <span class="tw-ml-2" data-toggle="tooltip"
                      title="<?= _l('saas_reserved_tenant_tooltip') ?>"
                >
                    <i class="fa fa-question-circle"></i>
                </span>
            </label>
            <input type="text"
                   id="settings[saas_reserved_tenant]"
                   name="settings[saas_reserved_tenant]"
                   class="form-control"
                   value="<?= get_option('saas_reserved_tenant') ?>"/>
            <div class="form-help-block small text-danger">
                <?= _l('saas_reserved_tenant_help') ?>
            </div>
        </div>
    </div>
</div>
