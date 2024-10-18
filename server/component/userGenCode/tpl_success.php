<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
    <h1>Success</h1>
        <p>A total of <code><?php echo $count; ?></code> validation codes were successfully created and added to the database.</p>
        <?php $this->output_collision(); ?>
        <?php $this->output_export_buttons(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-secondary">Create More Validation Codes</a>
    </div>
</div>

