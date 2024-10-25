<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Create New User</h1>
        <p>
            A new user requires a valid email address to be registered.
            Upon creation an email is sent to the recepient with an activation link.
        </p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">User Details</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="mb-3">
                    <label>Unique User Code</label>
                    <input type="text" class="form-control" name="code" maxlength="16" placeholder="Enter User Code" required>
                </div>
                <div class="mb-3">
                    <label>Email address</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                </div>
                <div class="mb-3">
                    <label>Assign User to Groups (You can select multiple groups)</label>
                    <?php $this->output_group_selection(); ?>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-end">Cancel</a>
            </form>
        </div>
    </div>
</div>
