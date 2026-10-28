<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="sticky-top">   
    <div id="multiple-users-warning"><?php $this->output_check_multiple_users(); ?></div>    
    <?php $this->output_breadcrumb(); ?>
    <?php $this->output_outline_toolbar(); ?>
</div>
<div id="ui-cms">
    <?php
    // The page is not rendered in the CMS. The sections are shown as an outline
    // and the properties of the selected section are loaded on the right.
    $this->output_modal_add_section();
    $this->output_page_outline();
    ?>
</div>
