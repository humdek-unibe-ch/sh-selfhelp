<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<textarea name="<?php echo $this->name; ?>" class="selfhelpTextArea actionConfigBuilder form-control d-none">
    <?php echo $this->value; ?>
</textarea>
<div class="actionConfigBuilderMonaco form-control p-0"></div>
<?php $this->output_builder() ?>
