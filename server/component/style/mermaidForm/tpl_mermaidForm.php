<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<div id="section-<?php echo $this->id_section; ?>" class="mermaidForm  <?php echo $this->css; ?>">
    <div class="mermaidCode"><?php echo $code; ?></div>
    <div class="mermaidFormEditableFields mermaidFormHidden"><?php echo $fields; ?></div>
    <div class="mermaidFormName mermaidFormHidden"><?php echo $formName; ?></div>
    <div class="section-children-ui-cms">
        <?php if (isset($this->model->get_params()['title'])) {
            // if we have that parameter we load from cms, show the children
            $this->output_children();
        } ?>
    </div>
    <?php $this->output_modal(); ?>
</div>