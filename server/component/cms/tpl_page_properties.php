<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card card-body mb-3 px-3 py-1 border-0 bg-light flex-row">
    <div class="mr-auto pr-2">
        <strong class="text-truncate"><?php echo $fields['keyword_title']; ?></strong>
        <?php echo $fields['keyword']; ?>
    </div>
    <div class="mx-auto px-2">
        <strong class="text-truncate"><?php echo $fields['url_title']; ?></strong>
        <?php echo $fields['url']; ?>
    </div>
    <div class="ml-auto pl-2">
        <strong class="text-truncate"><?php echo $fields['protocol_title']; ?></strong>
        <?php echo $fields['protocol']; ?>
    </div>
</div>
