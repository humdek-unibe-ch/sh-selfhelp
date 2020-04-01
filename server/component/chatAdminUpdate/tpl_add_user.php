<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Add User to Chat Room</h1>
        <p>Adding a user to a chat room will allow this user to communicate within this room.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            Adding User
        </div>
        <div class="card-body">
            <form action="<?php echo $url; ?>" method="POST" autocomplete="off">
                <?php $this->output_autocomplete(); ?>
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="<?php echo $url_cancel; ?>" class="float-right btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
