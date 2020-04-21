<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="list-group nested-list <?php echo $css; ?>">
    <?php $this->output_search_from(); ?>
    <div>
        <?php $this->output_list_items($this->items); ?>
    </div>
</div>
