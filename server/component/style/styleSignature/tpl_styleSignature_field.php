<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<li class="list-group-item bg-light">
    <div class="card card-body mb-3">
        <div class="row">
            <div class="col-12 col-sm">
                <strong class="style-sig-desc">Name</strong><code><?php echo $name; ?></code>
            </div>
            <div class="col-12 col-sm">
                <strong class="style-sig-desc">Type</strong><code><?php echo $type; ?></code>
            </div>
        </div>
    </div>
    <?php echo $description; ?>
</li>
