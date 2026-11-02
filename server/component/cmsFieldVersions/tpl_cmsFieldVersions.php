<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid my-3 cms-field-versions-page">
    <div class="d-flex align-items-center mb-3">
        <div>
            <h1 class="h4 mb-0"><i class="fas fa-history text-muted mr-1"></i> History of <code><?php echo htmlspecialchars($labels['field']); ?></code></h1>
            <small class="text-muted"><?php echo htmlspecialchars($labels['owner']); ?> &middot; <?php echo htmlspecialchars($labels['language']); ?></small>
        </div>
        <a href="<?php echo htmlspecialchars($back_url); ?>" class="btn btn-outline-secondary btn-sm ml-auto"><i class="fas fa-arrow-left mr-1"></i> Back to the CMS</a>
    </div>
    <div class="cms-field-versions card" data-title="<?php echo htmlspecialchars($labels['field']); ?>" data-subtitle="<?php echo htmlspecialchars($labels['owner'] . ' · ' . $labels['language']); ?>">
        <div class="cms-fv-alerts"><?php $this->output_alert(); ?></div>
        <div class="cms-fv-layout">
            <div class="cms-fv-list">
                <div class="cms-fv-list-header"><?php echo count($versions); ?> <?php echo count($versions) === 1 ? 'version' : 'versions'; ?></div>
                <div class="list-group list-group-flush">
                    <?php $this->output_versions($versions, $selected); ?>
                </div>
            </div>
            <div class="cms-fv-detail">
                <?php $this->output_selected($selected); ?>
            </div>
        </div>
    </div>
</div>
