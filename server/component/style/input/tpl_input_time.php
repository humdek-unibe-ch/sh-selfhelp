<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="input-group">
    <input id="selfhelp-input-<?php echo $this->section_id; ?>" class="selfhelp-input-date <?php echo $css_input; ?>" type="<?php echo $this->type; ?>" name="<?php echo $this->name; ?>" value="<?php echo $this->value; ?>" <?php echo $checked; ?> <?php echo $this->required; ?> placeholder="<?php echo $this->placeholder; ?>" <?php echo $autocomplete; ?>>
    <div class="input-group-append">
        <div class="btn btn-primary selfhelp-icon-btn" id="selfhelp-icon-btn-<?php echo $this->section_id; ?>"><i class="far fa-clock"></i></div>
    </div>
</div>