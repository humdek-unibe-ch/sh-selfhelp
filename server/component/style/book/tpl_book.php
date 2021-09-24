<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="book-turnjs <?php echo $this->css; ?>" data-config='<?php echo json_encode($this->config, JSON_HEX_QUOT | JSON_HEX_TAG); ?>'>
    <?php $this->output_children(); ?>
</div>
<div id="slider-bar" class="turnjs-slider">
    <div id="slider"></div>
</div>
<div id="book-buttons" class="turnjs-book-buttons d-none justify-content-between">
    <button id="book-previous-button" class="btn btn-primary"><?php echo $this->label_back; ?></button>
    <button id="book-next-button" class="btn btn-primary"><?php echo $this->label_next; ?></button>
</div>