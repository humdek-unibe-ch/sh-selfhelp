<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3 mb-3">
    <?php $this->output_alert(); ?>
    <div class="bg-light mb-4 rounded-2 py-5 px-3">
        <h1>Export <?php echo $this->type == 'section' ? 'section' : 'page' ?></h1>
        <p>Export <?php echo $this->type == 'section' ? 'section' : 'page' ?> and all its children as JSON file</p>
    </div>
    <input id='jsonExportData' type="hidden" value='<?php $this->export_json(); ?>' />
    <?php $this->output_back_button(); ?>
</div>