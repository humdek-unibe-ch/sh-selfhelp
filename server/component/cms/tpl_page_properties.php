<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card mb-3 px-3 py-1 border-0 bg-body-tertiary flex-row flex-wrap justify-content-between">
    <div>
        <strong class="text-truncate"><?php echo $fields['keyword_title']; ?></strong>
        <?php echo $fields['keyword']; ?>
    </div>
    <div>
        <strong class="text-truncate"><?php echo $fields['url_title']; ?></strong>
        <?php echo $fields['url']; ?>
    </div>
    <div>
        <strong class="text-truncate"><?php echo $fields['protocol_title']; ?></strong>
        <?php echo $fields['protocol']; ?>
    </div>
    <div>
        <strong class="text-truncate"><?php echo $fields['page_access_title']; ?></strong>
        <?php echo $fields['page_access']; ?>
    </div>
</div>
