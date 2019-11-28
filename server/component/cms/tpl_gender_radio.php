<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-group">
    <label>Select CMS Content Gender</label>
    <div>
        <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="cms_gender" value="male" <?php echo $fields['checked_male']; ?>>
            <label class="form-check-label">male</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="cms_gender" value="female" <?php echo $fields['checked_female']; ?>>
            <label class="form-check-label">female</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="cms_gender" value="both" <?php echo $fields['checked_both']; ?>>
            <label class="form-check-label">both</label>
        </div>
    </div>
</div>
