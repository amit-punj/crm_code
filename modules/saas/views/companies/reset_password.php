<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= _l('reset_password') ?></h4>
    </div>
    <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
          action="<?php echo base_url(); ?>saas/companies/reset_password/<?php
          if (!empty($company_info->id)) {
              echo $company_info->id;
          }
          ?>" method="post" class="form-horizontal  ">
        <div class="modal-body form-horizontal">
            <div class="form-group">
                <div class="col-lg-12">
                    <input type="password" class="form-control" id="change_email_password"
                           placeholder="<?= _l('enter') . ' ' . _l('your') . ' ' . _l('current') . ' ' . _l('password') ?>"
                           name="my_password">
                    <span class="required" id="email_password"></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12">
                    <input type="password" class="form-control" id="new_password"
                           placeholder="<?= _l('enter') . ' ' . _l('new') . ' ' . _l('password') . ' ' . _l('for') . ' ' . $company_info->name ?>"
                           name="password">
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12">
                    <input type="password" class="form-control" data-parsley-equalto="#new_password"
                           placeholder="<?= _l('enter') . ' ' . _l('confirm_password') . ' ' . _l('for') . ' ' . $company_info->name ?>"
                           name="password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close') ?></button>
                <button type="submit" id="new_uses_btn" class="btn btn-primary"><?= _l('update') ?></button>
            </div>
        </div>
    </form>
</div>

