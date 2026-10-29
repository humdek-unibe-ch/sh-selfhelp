<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3" id="formAction-editor" data-form-tables="<?php echo htmlspecialchars(json_encode($this->model->get_user_input()->get_form_dataTable_ids())); ?>" data-form-blocked-triggers="<?php echo htmlspecialchars(json_encode($this->get_form_blocked_trigger_ids())); ?>">
    <?php
    if ($this->mode === INSERT) {
        $this->output_add_action();
    } else if ($this->mode === SELECT) {
        $this->output_action_view();
    } else if ($this->mode === UPDATE) {
        $this->output_check_multiple_users();
        $this->output_add_action();
        $this->output_delete_action();
    }
    ?>
</div>