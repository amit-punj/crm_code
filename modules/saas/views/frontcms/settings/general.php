
<div class="row">
    <!-- Start Form -->
    <div class="col-lg-12">
        <?php
        echo form_open_multipart(base_url('saas/frontcms/settings/updateOption'), array('role' => 'form', 'data-parsley-validate' => '', 'novalidate' => '', 'class' => 'form-horizontal'));
        ?>
        <section class="panel panel-custom">
            <header class="panel-heading"><?= _l('general_settings') ?>
            </header>
            <div class="panel-body pb-sm">

                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= _l('home_page_slider') ?></label>

                    <div class="col-lg-6">
                        <div class="material-switch tw-mt-2">
                            <input name="saas_front_slider" id="ext_url" type="checkbox" value="1" <?php
                            if (get_option('saas_front_slider') != '') {
                                echo "checked";
                            } ?> />
                            <label for="ext_url" class="label-success"></label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= _l('home_slider_speed') ?></label>
                    <div class="col-lg-6">
                        <div class="input-group">
                            <input type="text" data-parsley-type="number"
                                   value="<?php if (get_option('home_slider_speed') != '') {
                                       echo html_escape(get_option('home_slider_speed'));
                                   } ?>" name="home_slider_speed" class="form-control">
                            <div class="input-group-addon"><?= _l('second') ?></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label"></label>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </section>
        <?php echo form_close(); ?>
        <!-- End Form -->
    </div>
</div>