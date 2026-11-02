<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="cms-fv-detail-header">
    <div class="cms-fv-min0">
        <div class="d-flex align-items-center">
            <h5 class="mb-0">Version <?php echo intval($selected['version_no']); ?></h5>
            <?php if ($selected['active']) { ?><span class="badge badge-success ml-2">Current content</span><?php } ?>
        </div>
        <div class="cms-fv-version-meta mt-1">
            <?php echo $this->format_date($selected['created_at']); ?>
            &middot; <?php echo $selected['user_email'] ? htmlspecialchars($selected['user_email']) : 'System / import'; ?>
            &middot; <?php echo number_format(intval($selected['size']), 0, '.', "'"); ?> characters
            <?php if ($selected['restored_from_no']) { ?>&middot; restored from v<?php echo intval($selected['restored_from_no']); ?><?php } ?>
        </div>
    </div>
    <?php if (!$selected['active']) { ?>
    <form class="cms-fv-restore ml-auto pl-3" action="<?php echo htmlspecialchars($this->model->get_version_url(intval($selected['version_no']))); ?>" method="post" data-confirmation="<?php echo htmlspecialchars(json_encode($confirmation)); ?>">
        <input type="hidden" name="restore_version" value="<?php echo intval($selected['id']); ?>">
        <button type="submit" class="btn btn-warning btn-sm text-nowrap"><i class="fas fa-undo mr-1"></i> Restore this version</button>
    </form>
    <?php } ?>
</div>
<ul class="nav nav-tabs cms-fv-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#cms-fv-changes" role="tab">
            Changes to current
            <?php if ($added) { ?><span class="cms-fv-stat cms-fv-stat-add">+<?php echo $added; ?></span><?php } ?>
            <?php if ($removed) { ?><span class="cms-fv-stat cms-fv-stat-del">&minus;<?php echo $removed; ?></span><?php } ?>
        </a>
    </li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#cms-fv-content" role="tab">Content</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="cms-fv-changes" role="tabpanel">
        <?php $this->output_diff($rows); ?>
    </div>
    <div class="tab-pane" id="cms-fv-content" role="tabpanel">
        <pre class="cms-fv-content"><?php echo htmlspecialchars($this->model->get_readable($selected['content'])); ?></pre>
    </div>
</div>
