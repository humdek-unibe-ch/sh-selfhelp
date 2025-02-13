<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<textarea name="<?php echo $this->name; ?>" class="selfhelpTextArea form-control <?php echo $this->markdown_editor ? 'selfhelpTextArea_MDE' : '' ?> <?php echo (($this->type_input == "json" || $this->type_input == "css") ? "d-none" : "") ?> <?php echo $css; ?>" placeholder="<?php echo $this->placeholder; ?>" <?php echo $this->min ? 'minlength="' . $this->min . '"' : "" ?> <?php echo $this->max ? 'maxlength="' . $this->max . '"' : "" ?> <?php echo $required; ?> data-locked_after_submit="<?php echo $this->locked_after_submit; ?>"><?php echo $this->value; ?></textarea>
<?php $this->output_monaco_editor() ?>
<!-- <div id="speech" class="btn btn-primary ">Start Voice Input</div>
<div id="output"></div> -->