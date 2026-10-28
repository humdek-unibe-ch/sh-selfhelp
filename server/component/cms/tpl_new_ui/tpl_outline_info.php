<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="ui-outline" class="ui-outline" data-context="<?php echo intval($this->model->get_active_page_id()); ?>-info" data-selected="0" data-count="0" data-editable="0">
    <div class="ui-outline-empty-state">
        <i class="fas fa-puzzle-piece fa-2x text-muted mb-2"></i>
        <h5>This page has no sections</h5>
        <p class="text-muted mb-0">The content of the page <code><?php echo $this->esc($this->page_info['keyword']); ?></code> is provided by a built-in component.
            Use the page properties on the right to configure it.</p>
    </div>
</div>
