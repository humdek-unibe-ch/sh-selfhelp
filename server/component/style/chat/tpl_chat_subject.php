<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div class="card mt-3 mb-3">
    <div class="card-body">
        <?php $this->output_group_tabs_holder(); ?>        
        <div class="my-3">
            <?php $this->output_chat($title); ?>
        </div>
    </div>
</div>