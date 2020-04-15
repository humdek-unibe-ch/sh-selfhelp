<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="row">
    <div class="col-auto">
        <input
            class="input-autocomplete-value"
            type="text"
            value="<?php echo $this->default_value; ?>"
            name="<?php echo $this->name_value_field; ?>"
            readonly
        />
    </div>
    <div class="col">
        <?php $this->output_autocomplete_field_search(); ?>
    </div>
</div>
