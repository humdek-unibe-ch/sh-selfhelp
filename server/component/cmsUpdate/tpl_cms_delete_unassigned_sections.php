<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Delete All Unassigned Sections</h1>
        <p>You must be absolutely certain that this is what you want. This operation cannot be undone! This operation will delete all unassigned sections and all children attached to them. To verify, enter <code>DELETE_ALL</code> as verification.</p>
    </div>
    <?php $this->output_content_delete_unassigned_sections() ?>
</div>
