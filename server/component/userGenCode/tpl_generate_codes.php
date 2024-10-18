<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Generate Validation Codes</h1>
        <p>
            Validation codes are used to let users register themselves.
            Specify the number of unique random codes you wish to generate and
            distribute these numbers to potential users.
        </p>
        <p>
            A user is able to register if he or she is in the posession of a
            valid code (each code can only be used once) and an email address.
            Once registered a validation email is sent to the user where the
            registration process can be completed.
        </p>
        <p>
            The number of validation codes should be larger than the estimated
            user count.
        </p>
    </div>
    <?php $this->output_codes(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Code Generation</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="mb-3">
                    <label>Number of codes to generate (max: <code><?php echo MAX_USER_COUNT; ?></code>)</label>
                    <input type="number" class="form-control" name="count" max="<?php echo MAX_USER_COUNT; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Generate</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-end">Cancel</a>
            </form>
        </div>
    </div>
</div>
