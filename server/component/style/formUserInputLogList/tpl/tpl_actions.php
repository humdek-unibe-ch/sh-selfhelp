<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="formUserInputLogList-actions">
    <?php
    $raw_row = $this->model->get_list_raw_row($record_id);
    foreach ($this->get_actions_order_keys() as $action_key) {
        if ($action_key === 'edit') {
            if ($this->label_edit !== '') {
                ?>
                <a href="<?php echo htmlspecialchars($this->model->get_record_url($record_id)); ?>"
                   class="formUserInputLogList-btn-edit">
                    <?php echo htmlspecialchars($this->label_edit); ?>
                </a>
                <?php
            }
        } elseif ($action_key === 'delete') {
            if ($this->can_delete) {
                ?>
                <a href="#"
                   class="text-danger formUserInputLogList-btn-delete"
                   data-toggle="modal"
                   data-target="#<?php echo htmlspecialchars($this->delete_modal_id); ?>"
                   data-record-id="<?php echo intval($record_id); ?>">
                    <i class="fas fa-trash"></i>
                    <span class="sr-only"><?php echo htmlspecialchars($this->label_delete); ?></span>
                </a>
                <?php
            }
        } else {
            $action = $this->model->get_quick_action_by_id($action_key);
            if (!$action || !$this->model->quick_action_matches_row($action, $raw_row)) {
                continue;
            }
            $bundle = $this->model->get_quick_action_label_bundle($action['id']);
            $css = trim('formUserInputLogList-btn-quick ' . $action['css']);
            ?>
            <a href="#"
               class="<?php echo htmlspecialchars($css); ?>"
               data-action-id="<?php echo htmlspecialchars($action['id']); ?>"
               data-record-id="<?php echo intval($record_id); ?>"
               data-confirm="<?php echo $action['confirm'] ? '1' : '0'; ?>"
               data-confirm-title="<?php echo htmlspecialchars($bundle['confirm_title']); ?>"
               data-confirm-message="<?php echo htmlspecialchars($bundle['confirm_message']); ?>">
                <?php echo htmlspecialchars($bundle['label']); ?>
            </a>
            <?php
        }
    }
    ?>
</div>
