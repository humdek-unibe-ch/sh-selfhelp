<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="filter-<?php echo $this->filter_type; ?> <?php echo $this->css; ?>">
    <?php $this->output_filter(); ?>
    <div class="filter-data d-none"><?php $this->output_filter_data(); ?></div>
</div>
