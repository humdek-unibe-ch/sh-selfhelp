<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
$write_result = '';
if ($this->controller !== null) {
    if ($this->controller->has_succeeded()) {
        $write_result = 'success';
    } else if ($this->controller->has_failed()) {
        $write_result = 'fail';
    }
}
?>
<div class="<?php echo htmlspecialchars($root_css); ?>"
     id="section-<?php echo intval($this->id_section); ?>"
     data-write-result="<?php echo htmlspecialchars($write_result); ?>"
     data-config="<?php echo htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8'); ?>">
    <?php $this->output_controller_alerts_fail(); ?>
    <?php $this->output_controller_alerts_success(); ?>
    <?php $this->output_list_section(); ?>
    <?php $this->output_form_section(false); ?>
    <?php $this->output_form_modal(); ?>
    <?php $this->output_delete_modal(); ?>
    <?php $this->output_quick_action_form(); ?>
</div>
