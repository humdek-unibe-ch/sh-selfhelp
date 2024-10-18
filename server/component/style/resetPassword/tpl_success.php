<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
    <h1><?php echo $this->success; ?></h1>
        <p><?php echo $this->alert_success; ?></p>
        <a href="<?php echo $url; ?>" class="btn btn-primary"><?php echo $this->login_label; ?></a>
    </div>
</div>

