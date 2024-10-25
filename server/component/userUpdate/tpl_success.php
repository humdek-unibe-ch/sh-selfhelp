<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
    <h1>Success</h1>
        <?php $this->output_success($this->mode); ?>
        <a href="<?php echo $url_user; ?>" class="btn btn-primary">Back to the User</a>
        <a href="<?php echo $url_users; ?>" class="btn btn-primary">To the Users</a>
    </div>
</div>
