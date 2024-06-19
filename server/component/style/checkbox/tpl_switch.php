<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<!-- Rounded switch -->
<label class="switch">     
    <input class="selfhelpInput" data-locked_after_submit="<?php echo $this->locked_after_submit; ?>" value="<?php echo $this->checkbox_value; ?>" type="checkbox" name="<?php echo $this->name; ?>"  <?php echo $checked; ?> <?php echo $this->required; ?>>
    <span class="slider round"></span>
</label>