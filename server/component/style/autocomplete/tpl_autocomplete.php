<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="input-autocomplete <?php echo $this->css; ?>">
    <?php $this->output_autocomplete_debug(); ?>
    <div class="input-autocomplete-callback d-none"><?php echo $callback; ?></div>
    <?php $this->output_autocomplete_field(); ?>
    <input
        class="input-autocomplete-value"
        type="hidden"
        name="<?php echo $this->name_value_field; ?>"
    />
    <div class="input-autocomplete-search-target mb-3"></div>
</div>
