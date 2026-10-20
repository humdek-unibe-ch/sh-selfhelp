<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="formUserInputLogList-actions">
    <?php if ($this->label_edit !== '') { ?>
        <a href="<?php echo htmlspecialchars($this->model->get_record_url($record_id)); ?>"
           class="formUserInputLogList-btn-edit">
            <?php echo htmlspecialchars($this->label_edit); ?>
        </a>
    <?php } ?>
    <?php if ($this->can_delete) { ?>
        <a href="#"
           class="text-danger formUserInputLogList-btn-delete"
           data-toggle="modal"
           data-target="#<?php echo htmlspecialchars($this->delete_modal_id); ?>"
           data-record-id="<?php echo intval($record_id); ?>">
            <i class="fas fa-trash"></i>
            <span class="sr-only"><?php echo htmlspecialchars($this->label_delete); ?></span>
        </a>
    <?php } ?>
</div>
