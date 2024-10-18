<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div>
    <div class="px-1 session-nav-link <?php echo $active; ?>">
        <?php $this->output_label($child); ?>
    </div>
    <div class="ms-3">
        <?php $this->output_nav_children($child['children']); ?>
    </div>
</div>
