<div class="tw-mb-2 sm:tw-mb-4">
    <a href="<?php echo saas_url('super_admin/create'); ?>" class="btn btn-primary">
        <i class="fa-regular fa-plus tw-mr-1"></i>
        <?php echo _l('new_super_admin'); ?>
    </a>
</div>
<div class="panel_s ">
    <div class="panel-body">
        <table class="table table-striped DataTables " id="DataTables" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= _l('staff_dt_name') ?></th>
                <th><?= _l('staff_dt_email') ?></th>
                <th><?= _l('staff_dt_last_Login') ?></th>
                <th><?= _l('staff_dt_active') ?></th>

            </tr>
            </thead>
            <tbody>
            <script type="text/javascript">
                'use strict'
                $(function () {
                    list = base_url + "saas/super_admin/userList";
                    initDataTable('#DataTables', list);
                });
            </script>
            </tbody>
        </table>
    </div>
</div>
