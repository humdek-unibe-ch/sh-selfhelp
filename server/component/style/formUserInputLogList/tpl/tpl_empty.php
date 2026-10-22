<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="formUserInputLogList-empty">
    <?php if ($this->empty_title !== '') { ?>
        <h3 class="formUserInputLogList-empty-title">
            <?php echo htmlspecialchars($this->empty_title); ?>
        </h3>
    <?php } ?>
    <?php if ($this->empty_text !== '') { ?>
        <p class="formUserInputLogList-empty-text">
            <?php echo htmlspecialchars($this->empty_text); ?>
        </p>
    <?php } ?>
    <div class="formUserInputLogList-empty-cta">
        <?php $this->output_new_button(); ?>
    </div>
</div>
