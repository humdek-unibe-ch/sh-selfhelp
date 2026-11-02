<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a href="<?php echo htmlspecialchars($url); ?>" class="list-group-item list-group-item-action cms-fv-version<?php echo $is_selected ? ' active' : ''; ?>">
    <div class="d-flex align-items-center">
        <span class="cms-fv-version-no">v<?php echo intval($version['version_no']); ?></span>
        <?php if ($version['active']) { ?><span class="badge badge-success ml-2">Current</span><?php } ?>
        <span class="cms-fv-version-date ml-auto"><?php echo $this->format_date($version['created_at']); ?></span>
    </div>
    <div class="cms-fv-version-meta">
        <span title="<?php echo $version['user_email'] ? htmlspecialchars($version['user_email']) : 'Written outside the CMS (e.g. an SQL import)'; ?>"><i class="fas <?php echo $version['user_email'] ? 'fa-user' : 'fa-cog'; ?> mr-1"></i><?php echo $version['user_email'] ? htmlspecialchars($version['user_email']) : 'System / import'; ?></span>
    </div>
    <div class="cms-fv-version-meta">
        <?php echo number_format(intval($version['size']), 0, '.', "'"); ?> characters<?php if ($version['restored_from_no']) { ?> &middot; <i class="fas fa-undo"></i> restored from v<?php echo intval($version['restored_from_no']); ?><?php } ?>
    </div>
</a>
