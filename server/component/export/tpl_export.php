<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="jumbotron">
        <h1><?php echo $title; ?></h1>
        <p><?php echo $text; ?></p>
    </div>
    <?php $this->output_export_item("user_input"); ?>
    <?php $this->output_export_item("user_input_form"); ?>
    <?php $this->output_export_item("user_activity"); ?>
    <?php $this->output_export_item("validation_codes"); ?>
</div>
