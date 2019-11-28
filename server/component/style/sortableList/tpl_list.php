<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<ul class="children-list list-group <?php echo $sortable; ?> <?php echo $this->css; ?>">
    <?php $this->output_list_new_button(); ?>
    <?php $this->output_list_items(); ?>
</ul>
