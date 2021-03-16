<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-group">
    <label for="<?php echo $fields['id'] ?>"><?php echo $fields['label'] ?></label>
    <select required class="selectpicker form-control" multiple id="<?php echo $fields['id'] ?>" name="<?php echo $fields['name'] ?>" data-live-search="true" data-size="10">
        <optgroup label="Groups">
            <?php foreach ($fields['groups'] as $key => $group) {
                echo '<option value=' . $group['value'] . '>' . $group['text'] . '</option>';
            } ?>
        </optgroup>
        <optgroup label="Users">
            <?php foreach ($fields['users'] as $key => $user) {
                echo '<option value=' . $user['value'] . '>' . $user['text'] . '</option>';
            } ?>
        </optgroup>
    </select>
</div>