<?php
$uri = $this->uri->segment(4);
if (empty($uri)) {
    $uri = null;
}
?>
<?php if (!empty(super_admin_access())) { ?>
    <div class="_buttons tw-mb-2 sm:tw-mb-4">

        <a href="<?php echo saas_url('companies/create'); ?>"
           class="btn btn-primary pull-left display-block">
            <i class="fa-regular fa-plus tw-mr-1"></i>
            <?php echo _l('new_company'); ?>
        </a>
        <div class="clearfix"></div>
    </div>
<?php } ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped DataTables " id="DataTables" width="100%">
                <thead>
                <tr>
                    <th><?= _l('name') ?></th>
                    <th><?= _l('email') ?></th>
                    <th><?= _l('account') ?></th>
                    <th><?= _l('package') ?></th>
                    <th><?= _l('trial_period') ?></th>
                    <th><?= _l('amount') ?></th>
                    <th><?= _l('status') ?></th>
                    <th><?= _l('date') ?></th>
                    <th><?= _l('action') ?></th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <script type="text/javascript">
                list = base_url + "saas/companies/companiesList/" + '<?= $uri ?>';
            </script>
        </div>
    </div>
</div>
<?php echo form_close(); ?>

<script>
    $(function () {
        'use strict';
        initDataTable('#DataTables', list, undefined, undefined, 'undefined');
    });
</script>