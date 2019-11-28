<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="progress <?php echo $this->css; ?>">
    <div class="h-100 progress-bar <?php echo $striped; ?> bg-<?php echo $this->type; ?>" role="progressbar" aria-valuenow="<?php echo $progress;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress; ?>%">
        <?php $this->output_progress_label(); ?>
    </div>
</div>
