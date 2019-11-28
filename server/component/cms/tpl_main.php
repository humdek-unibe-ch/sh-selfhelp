<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <div class="sticky-menu">
                <?php $this->output_create_new_button(); ?>
                <?php $this->output_lists(); ?>
            </div>
        </div>
        <div class="col">
            <?php $this->output_page_content(); ?>
        </div>
    </div>
</div>
