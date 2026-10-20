<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $record_id = isset($row[ENTRY_RECORD_ID]) ? intval($row[ENTRY_RECORD_ID]) : 0; ?>
<div class="formUserInputLogList-card" data-record-id="<?php echo $record_id; ?>">
    <?php if ($this->has_list_actions() && $this->is_actions_left()) { ?>
        <?php $this->output_list_actions($record_id); ?>
    <?php } ?>
    <div class="formUserInputLogList-card-body">
        <?php $this->output_list_card_cells($row, $headers); ?>
    </div>
    <?php if ($this->has_list_actions() && !$this->is_actions_left()) { ?>
        <?php $this->output_list_actions($record_id); ?>
    <?php } ?>
</div>
