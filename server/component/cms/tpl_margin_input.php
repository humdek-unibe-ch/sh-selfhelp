<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<input type="hidden" name="set_margin" value="1">
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="margin[]" value="mt-3" <?php echo $fields['checked_top']; ?>>
    <label class="form-check-label">top</label>
</div>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="margin[]" value="mr-3" <?php echo $fields['checked_right']; ?>>
    <label class="form-check-label">right</label>
</div>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="margin[]" value="mb-3" <?php echo $fields['checked_bottom']; ?>>
    <label class="form-check-label">bottom</label>
</div>
<div class="form-check form-check-inline">
    <input class="form-check-input" type="checkbox" name="margin[]" value="ml-3" <?php echo $fields['checked_left']; ?>>
    <label class="form-check-label">left</label>
</div>
