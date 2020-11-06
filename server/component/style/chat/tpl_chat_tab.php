<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<li class="nav-item">
    <a class="nav-link <?php echo $tab_css ?>" href="<?php echo $tab_url ?>"><?php echo $tab_name ?><?php $this->output_new_badge_group($group_id); ?></a>
</li>