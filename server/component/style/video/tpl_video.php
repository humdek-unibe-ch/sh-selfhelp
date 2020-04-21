<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<video class="<?php echo $fluid; ?> <?php echo $this->css; ?>" controls>
    <?php $this->output_video_sources(); ?>
    <?php echo $this->alt; ?>
</video>
