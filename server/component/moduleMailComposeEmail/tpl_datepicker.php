<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-group">
    <label for="<?php echo $fields['id'] ?>"><?php echo $fields['label'] ?></label>
    <div class="input-group">
        <input name="<?php echo $fields['name'] ?>" id="<?php echo $fields['id'] ?>" type="text" class="form-control" id="<?php echo $fields['id'] ?>">
        <div class="input-group-append mr-3">
            <div class="btn btn-primary" id="btn<?php echo $fields['id'] ?>"><i class="far fa-calendar-alt"></i></div>
        </div>        
    </div>
</div>