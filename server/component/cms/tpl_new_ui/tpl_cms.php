<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="sticky-top">   
    <div id="multiple-users-warning"><?php $this->output_check_multiple_users(); ?></div>    
    <?php $this->output_breadcrumb(); ?>
</div>
<div id="ui-cms">
    <?php
    // Modal before page preview so unclosed tags in section HTML cannot nest
    // #ui-add-section-modal inside #section-page-view.
    $this->output_modal_add_section();
    $this->output_page_preview();
    ?>
</div>