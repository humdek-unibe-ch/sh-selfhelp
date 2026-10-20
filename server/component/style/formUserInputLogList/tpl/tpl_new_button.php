<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php if ($this->show_form_in_modal) { ?>
    <button type="button"
            class="btn btn-primary formUserInputLogList-btn-new"
            data-toggle="modal"
            data-target="#<?php echo htmlspecialchars($this->modal_id); ?>"
            data-mode="new">
        <?php echo htmlspecialchars($this->label_new); ?>
    </button>
<?php } else { ?>
    <a class="btn btn-primary formUserInputLogList-btn-new"
       href="<?php echo htmlspecialchars($this->model->get_record_url('new')); ?>">
        <?php echo htmlspecialchars($this->label_new); ?>
    </a>
<?php } ?>
