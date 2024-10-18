<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
    <h1>Success</h1>
        <p>The user <code><?php echo $user; ?></code> was successfully created.</p>
        <a href="<?php echo $url_user; ?>" class="btn btn-primary">To the new User</a>
        <a href="<?php echo $url_users; ?>" class="btn btn-primary">To the Users</a>
        <a href="<?php echo $url_new; ?>" class="btn btn-secondary">Create New User</a>
    </div>
</div>

