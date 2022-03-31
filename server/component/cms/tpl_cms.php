<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="row">
    <div class="col">
        <?php $this->output_alerts(); ?>
        <?php $this->output_breadcrumb(); ?>
    </div>
</div>
<div id="cms-ui" class="row">
    <div class="col fieldsOnTop">
        <?php $this->output_fields(); ?>
    </div>
    <div class="col">
        <?php $this->output_page_preview(); ?>
    </div>
    <div class="col-auto">
        <?php $this->output_page_overview(); ?>
    </div>
</div>
