<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<form id="section-<?php echo $this->id_section; ?>" action="<?php echo $this->url ?>" method="post" class="<?php echo $this->css; ?>">
    <?php
        if ($this->entry_data) {
            $this->output_children_entry($this->entry_data);
        } else {
            $this->output_children();
        }
    ?>
    <?php $this->output_submit_button(); ?>
    <?php $this->output_submit_and_send_button(); ?>
    <?php $this->output_cancel(); ?>
</form>