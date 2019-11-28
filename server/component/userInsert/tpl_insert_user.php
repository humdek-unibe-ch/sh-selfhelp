<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
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
                <div class="form-group">
                    <label>Unique User Code</label>
                    <input type="text" class="form-control" name="code" maxlength="16" placeholder="Enter User Code" required>
                </div>
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" class="form-control" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label>Assign User to Groups (Use <kbd>Crtl</kbd> or <kbd>Shift</kbd> to select or unselect multiple elements)</label>
                    <?php $this->output_group_selection(); ?>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
</div>
