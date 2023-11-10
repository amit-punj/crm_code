<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
        <?php if(get_option('company_default_country') != 102){ ?>
            <div id="wrapper">
                <div class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tw-mb-2 sm:tw-mb-4">
                                <?php if(get_option('company_default_country') != 102){ ?>
                                    <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#tax_modal">
                                        <i class="fa-regular fa-plus tw-mr-1"></i>
                                        <?php echo _l('new_tax'); ?>
                                    </a>
                                <?php } ?>
                            </div>

                            <div class="panel_s">
                                <div class="panel-body panel-table-full">
                                    <?php render_datatable([
                                        _l('id'),
                                        _l('tax_dt_name'),
                                        _l('tax_dt_rate'),
                                        _l('options'),
                                        ], 'taxes'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="tax_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">
                                <span class="edit-title"><?php echo _l('tax_edit_title'); ?></span>
                                <span class="add-title"><?php echo _l('tax_add_title'); ?></span>
                            </h4>
                        </div>
                        <?php echo form_open('admin/taxes/manage', ['id' => 'tax_form']); ?>
                        <?php echo form_hidden('taxid'); ?>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-warning hide tax_is_used_in_expenses_warning">
                                        <?php echo _l('tax_is_used_in_expenses_warning'); ?>
                                    </div>
                                    <div class="alert alert-warning hide tax_is_used_in_subscriptions_warning">
                                        <?php echo _l('tax_is_used_in_subscriptions_warning'); ?>
                                    </div>
                                    <?php echo render_input('name', 'tax_add_edit_name'); ?>
                                    <?php echo render_input('taxrate', 'tax_add_edit_rate', '', 'number'); ?>
                                    <?php hooks()->do_action('before_taxes_modal_form_close'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php init_tail(); ?>
            <script>
            $(function() {
                initDataTable('.table-taxes', window.location.href, [3], [3], undefined, [2, 'asc']);
                appValidateForm($('form'), {
                    name: {
                        required: true,
                        remote: {
                            url: admin_url + "taxes/tax_name_exists",
                            type: 'post',
                            data: {
                                taxid: function() {
                                    return $('input[name="taxid"]').val();
                                }
                            }
                        }
                    },
                    rate: {
                        number: true,
                        required: true
                    }
                }, manage_tax);

                // don't allow | charachter in tax name
                // is used for tax name and tax rate separations!
                $('#tax_modal input[name="name"]').on('change', function() {
                    var val = $(this).val();
                    if (val.indexOf('|') > -1) {
                        val = val.replace('|', '');
                        // Clean extra spaces in case this char is in the middle with space
                        val = val.replace(/ +/g, ' ');
                        $(this).val(val);
                    }
                });

                $('#tax_modal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget)
                    var id = button.data('id');
                    $(this).find('button[type="submit"]').prop('disabled', false);
                    $('#tax_modal input[name="name"]').val('').prop('disabled', false);
                    $('#tax_modal input[name="taxrate"]').val('').prop('disabled', false);
                    $('#tax_modal input[name="taxid"]').val('')
                    $('#tax_modal .add-title').removeClass('hide');
                    $('#tax_modal .edit-title').addClass('hide');
                    $('.tax_is_used_in_expenses_warning').addClass('hide');
                    $('.tax_is_used_in_subscriptions_warning').addClass('hide');
                    if (typeof(id) !== 'undefined') {
                        $('input[name="taxid"]').val(id);
                        var name = $(button).parents('tr').find('td').eq(1).text();
                        var rate = $(button).parents('tr').find('td').eq(2).text();
                        var is_referenced_expenses = $(button).data('is-referenced-expenses');
                        if (is_referenced_expenses == 1) {
                            $('.tax_is_used_in_expenses_warning').removeClass('hide');
                        }

                        var is_referenced_subscriptions = $(button).data('is-referenced-subscriptions');
                        if (is_referenced_subscriptions == 1) {
                            $('.tax_is_used_in_subscriptions_warning').removeClass('hide');
                        }
                        $('#tax_modal .add-title').addClass('hide');
                        $('#tax_modal .edit-title').removeClass('hide');
                        $('#tax_modal input[name="name"]').val(name).prop('disabled', (is_referenced_expenses ==
                            1 || is_referenced_subscriptions == 1 ? true : false));
                        $('#tax_modal input[name="taxrate"]').val(rate).prop('disabled', (is_referenced_expenses ==
                            1 || is_referenced_subscriptions == 1 ? true : false));
                        $(this).find('button[type="submit"]').prop('disabled', is_referenced_expenses == 1 ||
                            is_referenced_subscriptions == 1)
                    }
                });
            });
            function manage_tax(form) {
                var data = $(form).serialize();
                var url = form.action;
                $.post(url, data).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success == true) {
                        $('.table-taxes').DataTable().ajax.reload();
                        alert_float('success', response.message);
                    } else {
                        if (response.message != '') {
                            alert_float('warning', response.message);
                        }
                    }
                    $('#tax_modal').modal('hide');
                });
                return false;
            }
            </script>
        <?php } else {?>
            <style type="text/css">
                /* The switch - the box around the slider */
                .switch {
                  position: relative;
                  display: inline-block;
                  width: 60px;
                  height: 21px;
                }

                /* Hide default HTML checkbox */
                .switch input {
                  opacity: 0;
                  width: 0;
                  height: 0;
                }

                /* The slider */
                .slider {
                  position: absolute;
                  cursor: pointer;
                  top: 0;
                  left: 0;
                  right: 4px;
                  bottom: 0;
                  background-color: #ccc;
                  -webkit-transition: .4s;
                  transition: .4s;
                }

                .slider:before {
                  position: absolute;
                  content: "";
                  /*height: 26px;
                  width: 26px;
                  left: 4px;
                  bottom: 4px;*/
                  height: 20px;
                    width: 20px;
                    left: 5px;
                    bottom: 1px;
                  background-color: white;
                  -webkit-transition: .4s;
                  transition: .4s;
                }

                input:checked + .slider {
                  background-color: #2196F3;
                }

                input:focus + .slider {
                  box-shadow: 0 0 1px #2196F3;
                }

                input:checked + .slider:before {
                  -webkit-transform: translateX(26px);
                  -ms-transform: translateX(26px);
                  transform: translateX(26px);
                }

                /* Rounded sliders */
                .slider.round {
                  border-radius: 34px;
                }

                .slider.round:before {
                  border-radius: 50%;
                }
            </style>
            <div id="wrapper">
                <div class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel_s">
                                <div class="panel-body panel-table-full">
                                    <table class="table dt-table" data-order-col="0" data-order-type="asc">
                                        <thead>
                                            <th><?php echo _l('id'); ?></th>
                                            <th><?php echo _l('tax_dt_name'); ?></th>
                                            <th><?php echo _l('tax_dt_rate'); ?></th> 
                                            <th><?php echo _l('options'); ?></th> 
                                        </thead>
                                        <tbody>
                                            <?php foreach ($indian_taxes ?? [] as $tax) { ?>
                                            <tr>
                                                <td><?= $tax['id']; ?></td>
                                                <td><?= $tax['name'] ?></td>
                                                <td><?= $tax['taxrate'] ?></td>
                                                <td>
                                                    <div class="tw-flex tw-items-center tw-space-x-3">
                                                        <label class="switch">
                                                          <input data-id="<?=$tax['grp_id']?>" class="group group<?=$tax['grp_id']?>" type="checkbox" <?= ($tax['enable']) ? 'checked':''?>>
                                                          <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php init_tail(); ?>
            <script type="text/javascript">
                $('.group').click(function(){
                    var group_id = $(this).data('id');
                    console.log('here', $(this).val())
                    console.log('here', $(this).data('id'))
                    let enable = (this.checked) ? 1 : 0; 
                    $.ajax({
                        url: '<?php echo admin_url('taxes/enableDisable'); ?>',
                        data: { "grp_id": group_id, 'enable':enable },
                        type: "post",
                        dataType: "json",
                        success: function (data)
                        {
                            console.log("data",data);
                            // SetCheckbox($('#changingCheckboxes').children("input:[type='checkbox']"), true);
                            // $.each(data.disabled, function ()
                            // {
                            //    SetCheckbox($('#changingCheckboxes #' + this), false);
                            // });
                        }
                    });
                    if (this.checked) {
                        $('.group').prop('checked', false); // Unchecks it
                        $('.group'+group_id).prop('checked', true); // Checks it
                    } else {
                        $('.group'+group_id).prop('checked', false); // Unchecks it
                    }
                })
            </script>
        <?php } ?>
</body>

</html>