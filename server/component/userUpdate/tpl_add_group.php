<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Add Group to User</h1>
        <p>Adding a group to a user will provide this user with the permissions of the group.</p>
    </div>
    <?php $this->output_form_add_groups(); ?>
</div>
