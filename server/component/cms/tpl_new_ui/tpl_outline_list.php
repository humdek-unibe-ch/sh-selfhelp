<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// Variables: $list, $editable, $depth (see CmsView::output_outline_list).
?>
<ul class="ui-outline-list<?php echo $depth == 0 ? ' ui-outline-root' : ''; ?>"
    data-depth="<?php echo intval($depth); ?>"
    data-relation="<?php echo $this->esc($list['relation']); ?>"
    data-parent-id="<?php echo intval($list['parent_id']); ?>"
    data-update-url="<?php echo $this->esc($list['update_url']); ?>"
    data-insert-url="<?php echo $this->esc($list['insert_url']); ?>">
    <?php foreach ($list['nodes'] as $index => $node) {
        $this->output_outline_node($node, $index, $editable, $depth);
    } ?>
    <?php if ($editable && $depth > 0 && count($list['nodes']) == 0) { ?>
        <li class="ui-outline-placeholder">
            <button type="button" class="btn btn-link btn-sm" data-action="add-inside-list">
                <i class="fas fa-plus-circle mr-1"></i>Add section
            </button>
        </li>
    <?php } ?>
</ul>
