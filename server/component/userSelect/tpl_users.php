<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card card-header mb-4 rounded-2 py-5 px-3">
    <h1>User Management</h1>
    <p>
        The table below lists all existing users.
        Selecting one will allow to specify the groups the user belongs to in order to set specific access rights, block, unblock, or delete the user.
    </p>
    <p>
        To create a new user use the button in the top left corner (if available).
    </p>
</div>
<?php $this->output_user_activity(); ?>
