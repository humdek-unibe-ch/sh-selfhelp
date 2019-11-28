<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-check <?php echo $inline; ?>">
    <input class="form-check-input" type="radio" name="<?php echo $this->name; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?>>
    <label class="form-check-label">
        <?php echo $text; ?>
    </label>
</div>
