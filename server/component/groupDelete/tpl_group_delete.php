<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Delete Group</h1>
        <p>This will delete the group <code><?php echo $this->selected_group['name']; ?></code> and all the data associated to this group.</p>
    </div>
    <?php $this->output_form(); ?>
</div>
