<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?php echo $active; ?>" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expand="false">
        <?php $this->output_icon($icon); ?>
        <?php echo $page_name; ?>
    </a>
    <div class="dropdown-menu <?php echo $align; ?>">
        <?php $this->output_nav_menu_items($children); ?>
    </div>
</li>
