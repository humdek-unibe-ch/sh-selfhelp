<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card mb-3">
    <div class="card-body">
        <p>The database holds <code><?php echo $count; ?></code> validation codes. <code><?php echo $count_consumed; ?></code> of those are consumed which leaves <strong><code><?php echo $count_open; ?></code> open for assignment</strong>.</p>
        <?php $this->output_export_buttons(); ?>
    </div>
</div>
