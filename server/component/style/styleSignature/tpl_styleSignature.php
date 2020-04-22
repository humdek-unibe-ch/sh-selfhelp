<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card card-body mb-3">
    <div class="row">
        <div class="col-12 col-md">
            <strong class="style-sig-desc">Name</strong><code><?php echo $fields['name']; ?></code>
        </div>
        <div class="col-12 col-md">
            <strong class="style-sig-desc">Group</strong><code><?php echo $fields['style_group']; ?></code>
        </div>
        <div class="col-12 col-md">
            <strong class="style-sig-desc">Type</strong><code><?php echo $fields['type']; ?></code>
        </div>
    </div>
</div>
<?php echo $fields['description']; ?>
