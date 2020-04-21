<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<table class="table <?php echo $this->css; ?> <?php echo $this->is_log ? 'showUserInput' : ''; ?> ">
    <?php $this->output_fields($fields); ?>
</table>
<?php $this->output_modal(); ?>