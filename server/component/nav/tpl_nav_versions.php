<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expand="false">
        <?php echo $current_version; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <?php $this->output_version_items($versions, $current_version, $target); ?>
    </div>
</li>
