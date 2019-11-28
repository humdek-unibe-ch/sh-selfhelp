<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="list-group-item list-group-item-action p-0 <?php echo $collapsible; ?>">
    <?php $this->output_chevron($is_collapsible, $is_expanded); ?>
    <?php $this->output_list_item_name($item, $active, $id_html); ?>
</div>
<?php $this->output_children_container($children, $is_expanded); ?>
