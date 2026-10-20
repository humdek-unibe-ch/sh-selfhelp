<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
if ($this->is_cards_layout()) {
    require __DIR__ . "/tpl_card_row.php";
    return;
}
$record_id = isset($row[ENTRY_RECORD_ID]) ? intval($row[ENTRY_RECORD_ID]) : 0;
?>
<tr class="formUserInputLogList-row" data-record-id="<?php echo $record_id; ?>">
    <?php if ($this->has_list_actions() && $this->is_actions_left()) { ?>
        <td class="formUserInputLogList-actions-cell">
            <?php $this->output_list_actions($record_id); ?>
        </td>
    <?php } ?>
    <?php $this->output_list_row_cells($row, $headers); ?>
    <?php if ($this->has_list_actions() && !$this->is_actions_left()) { ?>
        <td class="formUserInputLogList-actions-cell">
            <?php $this->output_list_actions($record_id); ?>
        </td>
    <?php } ?>
</tr>
