<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="ui-outline-toolbar" class="ui-outline-toolbar">
    <?php if ($editable) { ?>
        <button type="button" id="ui-outline-add" class="btn btn-success btn-sm" <?php echo $outline['add'] ? '' : 'disabled'; ?>><i class="fas fa-plus mr-1"></i>Add section</button>
        <?php if ($can_import) { ?>
            <button type="button" id="ui-outline-import" class="btn btn-outline-secondary btn-sm" <?php echo $outline['add'] ? '' : 'disabled'; ?>><i class="fas fa-file-upload mr-1"></i>Import section</button>
        <?php } ?>
    <?php } else { ?>
        <span class="ui-outline-view-hint small text-muted"><i class="fas fa-lock mr-1"></i>View mode. Switch on <strong>Edit</strong> to change the sections.</span>
    <?php } ?>
    <div class="ui-outline-filter ml-auto">
        <i class="fas fa-search"></i>
        <input type="search" id="ui-outline-filter" class="form-control form-control-sm" placeholder="Filter sections by name, style or text" aria-label="Filter sections" autocomplete="off">
    </div>
    <span id="ui-outline-count" class="ui-outline-count small text-muted"></span>
    <div class="btn-group btn-group-sm">
        <button type="button" id="ui-outline-expand-all" class="btn btn-outline-secondary" title="Expand all"><i class="fas fa-angle-double-down"></i></button>
        <button type="button" id="ui-outline-collapse-all" class="btn btn-outline-secondary" title="Collapse all"><i class="fas fa-angle-double-up"></i></button>
    </div>
</div>
