<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Remove Group <code><?php echo $group; ?></code> from User <code><?php echo $this->selected_user['email']; ?></code></h1>
        <p>Removing a group from a user will revoke the permissions of the group from the user.</p>
    </div>
    <?php $this->output_form_rm_group(); ?>
</div>
