<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<form id="section-<?php echo $this->id_section; ?>" action="<?php echo $this->url ?>" method="post" class="selfHelp-form <?php echo $this->css; ?>" data-confirmation='<?php echo json_encode($data_confirmation); ?>'>
    <?php $this->output_form_children(); ?>
    <?php $this->output_submit_button(); ?>    
    <?php $this->output_cancel(); ?>
</form>