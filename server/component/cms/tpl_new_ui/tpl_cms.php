<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="sticky-top">
    <?php $this->output_alerts(); ?>
    <?php $this->output_breadcrumb(); ?>
</div>
<div id="ui-cms">
    <?php $this->output_page_preview(); ?>
    <?php $this->output_modal_add_section(); ?>
</div>