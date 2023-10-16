<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<input class="selfhelpInput <?php echo $css_input; ?>" <?php echo $this->format ? ("pattern='" . $this->format . "'") : ''; ?> data-locked_after_submit="<?php echo $this->locked_after_submit; ?>" type="<?php echo $this->type; ?>" name="<?php echo $this->name; ?>" value="<?php echo $this->value; ?>" <?php echo $checked; ?> <?php echo $this->required; ?> <?php echo $this->min ? 'minlength="' . $this->min . '"' : "" ?> <?php echo $this->max ? 'maxlength="' . $this->max . '"' : "" ?> placeholder="<?php echo $this->placeholder; ?>" <?php echo $autocomplete; ?>>