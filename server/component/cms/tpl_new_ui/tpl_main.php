<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid">
    <div class="row">
        <div id="sidebar-container" class="sidebar-expanded  d-md-block">
            <ul class="list-group sticky-top">
                <div id="collapseBtn" data-toggle="sidebar-colapse" class="ui-side-menu-button list-group-item list-group-item-action">
                    <div data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Toggle Sidebar">
                        <span id="collapse-icon" class="fas"></span>
                        <span id="collapse-text" class="ml-1 menu-collapsed">Collapse sidebar</span>
                    </div>
                </div>
                <?php $this->output_create_new_button(); ?>
                <?php $this->output_import_button(); ?>
                <?php $this->output_lists(); ?>
            </ul>
        </div>

        <div class="col mt-3">
            <?php $this->output_page_content(); ?>
        </div>
    </div>
</div>