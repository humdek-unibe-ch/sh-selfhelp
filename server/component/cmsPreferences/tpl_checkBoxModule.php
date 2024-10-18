<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3">    
    <div class="form-check form-switch d-flex">
        <input name = "<?php echo $fields['id_HTML'] ?>" <?php echo $fields['disabled'] ?> type="checkbox" class="custom-control-input" id="<?php echo $fields['id_HTML'] ?>" <?php echo ($fields['is_checked'] == 1) ? "checked" : ""; ?>>
        <label class="form-check-label" for="<?php echo $fields['id_HTML'] ?>"><?php echo $fields['id_HTML'] ?></label>
    </div>
</div>