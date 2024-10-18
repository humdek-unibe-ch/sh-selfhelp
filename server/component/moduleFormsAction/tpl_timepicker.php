<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3 d-none" id="at_time_holder">
    <label for="<?php echo $fields['id'] ?>"><?php echo $fields['label'] ?></label>
    <div class="input-group">    
        <input name="<?php echo $fields['name'] ?>" id="<?php echo $fields['id'] ?>" type="text" class="form-control" id="<?php echo $fields['id'] ?>"
         <?php echo isset($fields['disabled']) ? $fields['disabled'] : '' ?>
        value="<?php echo $fields['value'] ?>">
        <div class="input-group-append">
            <div class="btn btn-primary" id="btn<?php echo $fields['id'] ?>"><i class="far fa-clock"></i></div>
        </div>    
        <div class="input-group-append">
            <div class="btn btn-secondary" id="clearBtn<?php echo $fields['id'] ?>" <?php echo isset($fields['disabled']) ? $fields['disabled'] : '' ?>><i class="far fa-times-circle"></i></div>
        </div>    
    </div>
</div>