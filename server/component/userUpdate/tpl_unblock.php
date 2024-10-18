<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Unblock User</h1>
        <p>This will unblock the user <code><?php echo $this->selected_user['email']; ?></code>.</p>
    </div>
    <?php $this->output_form_unblock(); ?>
</div>
