<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_button(); ?>
            <?php $this->output_languages(); ?>
        </div>
        <div class="col">
            <?php $this->output_alert(); ?>
            <?php $this->output_cms_preferences_form(); ?>
        </div>
    </div>
</div>