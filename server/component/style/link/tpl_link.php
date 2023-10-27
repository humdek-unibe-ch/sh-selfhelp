<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a class="<?php echo $this->css; ?>" href="<?php echo $this->url; ?>" <?php echo $target; ?>>
    <?php echo $this->label; ?>
    <?php $this->output_children(); ?>
</a>
