<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="table-responsive">
    <table class="table items items-preview invoice-items-preview" data-type="invoice">
        <thead>
        <tr>
            <th><?= _l('module_name') ?></th>
            <th><?= _l('price') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!empty($modules)) {
            foreach ($modules as $key => $module) {
                if ($module['system_name'] == 'saas') {
                    continue;
                }
                $value = '';
                // set default value from moduleInfo array and $module['system_name']
                if (isset($moduleInfo[$module['system_name']])) {
                    $value = $moduleInfo[$module['system_name']];
                }
                ?>
                <tr class="apply_new_limit">
                    <td><?= $module['headers']['module_name'] ?></td>
                    <td>
                        <input type="number"
                               class="form-control new_limit" name="modules[<?= $module['system_name'] ?>]"
                               value="<?php
                               echo $value;
                               ?>">
                    </td>
                </tr>
                <?php
            }
        }
        ?>

        </tbody>
    </table>
</div>
<div class="btn-bottom-toolbar text-right">
    <button type="submit" class="btn btn-primary"><?= _l('update') . ' ' . _l('module_price') ?></button>
</div>
