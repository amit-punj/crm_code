<div class="row">
    <!-- Start Form -->
    <div class="col-lg-12">
        <?php
        $themes = array();
        $form_url = 'saas/themebuilder/updateBuilder';
        $name = 'saas_default_theme';
        $dir = 'themes/';
        if (!empty(subdomain())) {
            $form_url = 'admin/themebuilder/updateBuilder';
            $subs = get_company_subscription(null, 'running');
            $allowed_themes = (!empty($subs->allowed_themes) ? unserialize($subs->allowed_themes) : array());
            if (count($allowed_themes) > 0) {
                $themes = $allowed_themes;
            }
            $dir = $subs->domain . '/';
            $name = 'default_theme';
        } else {
            $themes = get_theme_list();
            // add default theme to the list
            array_unshift($themes, 'default');
        }
        echo form_open_multipart(base_url($form_url), array('role' => 'form', 'data-parsley-validate' => '', 'novalidate' => '', 'class' => 'form-horizontal'));

        $default_theme = get_option($name);
        $url = base_url('preview/' . $dir . $default_theme . '/index.html');
        if ($default_theme == 'default') {
            $url = base_url('');
        }
        ?>
        <section class="panel panel-custom">
            <header class="panel-heading"><?= _l('general_settings') ?>
            </header>
            <div class="panel-body pb-sm">

                <div class="form-group">
                    <label class="col-lg-3 control-label"><?= _l('default_theme') ?></label>
                    <div class="col-lg-6">
                        <div class="input-group">
                            <select name="<?= $name ?>" class="form-control selectpicker"
                                    id="theme_view"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php
                                foreach ($themes as $key => $theme) {
                                    $themeName = basename($theme);
                                    ?>
                                    <option value="<?php echo $themeName; ?>" <?php if ($themeName == $default_theme) {
                                        echo 'selected';
                                    } ?>><?php echo ucfirst($themeName); ?></option>
                                <?php } ?>
                            </select>
                            <span class="input-group-addon">
                                <a
                                        id="theme_view_preview"
                                        href="<?php echo $url; ?>"
                                        target="_blank">
                                    <i class="fa fa-eye"></i> <?= _l('preview') ?>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <?php
                if (empty(subdomain())) {
                    ?>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?= _l('upload_theme') ?></label>
                        <div class="col-lg-6">
                            <div class="input-group">
                                <input type="file" name="theme_zip" class="form-control">
                                <span class="input-group-addon">
                                <a href="https://docs.coderitems.com/perfectsaas/#how_upload_theme" target="_blank">
                                    <i class="fa fa-question-circle" data-toggle="tooltip"
                                       title="<?= _l('upload_theme_help') ?>"></i>
                                </a>
                            </span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
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

<script>
    $(document).ready(function () {
        $('body').on('change', '#theme_view', function () {
            const layout = $(this).val();
            let url = '<?= base_url('preview/' . $dir) ?>' + layout + '/index.html';
            if (layout == 'default') {
                url = '<?= base_url('') ?>'
            }
            // add value to invoice_layout_preview preview
            $('#theme_view_preview').attr('href', url);
        });
    });
</script>