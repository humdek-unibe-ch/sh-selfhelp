<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-group d-none" id="custom_time_holder">
    <div class="input-group">
        <input required name="<?php echo $fields['name'] ?>" id="<?php echo $fields['id'] ?>" type="text" 
        class="form-control" id="<?php echo $fields['id'] ?>" <?php echo isset($fields['disabled']) ? $fields['disabled'] : '' ?>
        value="<?php echo $fields['value'] ?>">
        <div class="input-group-append">
            <div class="btn btn-primary" id="btn<?php echo $fields['id'] ?>"><i class="far fa-calendar-alt"></i></div>
        </div>
    </div>
</div>