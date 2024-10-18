<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<li class="list-group-item section-child d-flex justify-content-between <?php echo $css; ?>" id="sections-field-<?php echo $index; ?>-<?php echo $id; ?>">
    <?php $this->output_list_item_index($index); ?>
    <?php $this->output_label($name, $url); ?>
    <?php $this->output_list_item_rm_button($id); ?>
</li>
