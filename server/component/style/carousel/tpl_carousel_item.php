<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="carousel-item <?php echo $active; ?>">
    <img class="d-block w-100" src="<?php echo $url; ?>" alt="<?php echo $alt; ?>">
    <?php $this->output_caption($caption); ?>
</div>
