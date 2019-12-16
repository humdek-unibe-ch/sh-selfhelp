<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a class="nav-link mr-0 <?php echo $active?> <?php echo $accessToChat?>" href="<?php echo $url; ?>">
    <i class="fas fa-envelope fa-lg"></i>
    <?php $this->output_new_messages(); ?>
</a>
