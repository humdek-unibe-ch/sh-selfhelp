<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="<?php echo $this->id_prefix; ?>-carousel" class="carousel slide <?php echo $crossfade; ?> <?php echo $this->css; ?>" data-bs-ride="carousel">
    <?php $this->output_indicator_wrapper(); ?>
    <div class="carousel-inner">
        <?php $this->output_carousel_items(); ?>
    </div>
    <?php $this->output_controls(); ?>
</div>
