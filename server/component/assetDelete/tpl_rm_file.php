<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Remove File <code><?php echo $this->file_name; ?></code> from the Server</h1>
        <p>Removing a file from the server will delete this file permanently. This cannot be undone.</p>
    </div>
    <?php $this->output_form_rm_file(); ?>
</div>
