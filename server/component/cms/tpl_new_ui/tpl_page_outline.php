<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
// Variables: $outline (see CmsModel::get_page_outline) and $editable.
$add = $outline['add'];
$keyword = $this->page_info ? $this->page_info['keyword'] : '';
$count = count($outline['list']['nodes']);
?>
<div id="ui-outline" class="ui-outline"
    data-context="<?php echo $this->esc($outline['context']); ?>"
    data-selected="<?php echo intval($outline['selected']); ?>"
    data-count="<?php echo intval($outline['count']); ?>"
    data-editable="<?php echo $editable ? 1 : 0; ?>"
    data-page-url="<?php echo $this->esc($outline['page_url']); ?>"
    data-page-id="<?php echo intval($this->model->get_active_page_id()); ?>"
    <?php if ($add) { ?>
    data-add-relation="<?php echo $this->esc($add['relation']); ?>"
    data-add-parent-id="<?php echo intval($add['parent_id']); ?>"
    data-add-insert-url="<?php echo $this->esc($add['insert_url']); ?>"
    data-add-update-url="<?php echo $this->esc($add['update_url']); ?>"
    <?php } ?>>
    <div class="ui-outline-row ui-outline-page-row<?php echo $outline['selected'] == 0 ? ' is-selected' : ''; ?>">
        <a class="ui-outline-link" href="<?php echo $this->esc($outline['page_url']); ?>" data-page="1" title="Page properties">
            <i class="fas fa-file-alt fa-fw text-secondary mr-1"></i>
            <span class="ui-outline-name"><?php echo $this->esc($keyword); ?></span>
            <span class="badge badge-light border ui-outline-style">page</span>
        </a>
    </div>
    <?php if ($this->model->is_navigation_main()) { ?>
        <div class="alert alert-info ui-outline-note mb-2">
            <i class="fas fa-info-circle mr-1"></i>This is a navigation page. The sections of its navigation items are edited when an item is selected
            (see <strong>Navigation Hierarchy</strong> on the left or the navigation items in the page properties).
        </div>
    <?php } ?>
    <?php if ($count == 0) { ?>
        <div class="ui-outline-empty-state">
            <i class="far fa-folder-open fa-2x text-muted mb-2"></i>
            <h5>No sections yet</h5>
            <?php if ($editable && $add) { ?>
                <p class="text-muted mb-3">Add the first section to start building this page.</p>
                <button type="button" class="btn btn-success btn-sm" data-action="add-root"><i class="fas fa-plus mr-1"></i>Add section</button>
                <?php if ($this->model->can_import_section()) { ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-action="import-root"><i class="fas fa-file-upload mr-1"></i>Import section</button>
                <?php } ?>
            <?php } else { ?>
                <p class="text-muted mb-0">This page has no sections.</p>
            <?php } ?>
        </div>
    <?php } else { ?>
        <?php $this->output_outline_list($outline['list'], $editable, 0); ?>
        <div id="ui-outline-no-match" class="ui-outline-no-match d-none">No section matches the filter.</div>
    <?php } ?>
</div>
