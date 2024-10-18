<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a class="carousel-control-<?php echo $direction; ?>" href="#<?php echo $this->id_prefix; ?>-carousel" role="button" data-bs-slide="<?php echo $direction; ?>">
    <span class="fas fa-lg <?php echo $icon; ?>" aria-hidden="true"></span>
    <span class="visually-hidden"><?php echo $direction; ?></span>
</a>
