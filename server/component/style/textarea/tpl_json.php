<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="json-mapping">
    <div class="json form-control p-0"></div>
    <button type="button" class="w-100 mt-1 btn json-mapping-btn btn-sm <?php echo $button_class; ?>" data-name="<?php echo $field_name; ?>">
        <?php echo $button_label; ?>
    </button>
    <?php $this->output_json_mapper_modal(); ?>
</div>