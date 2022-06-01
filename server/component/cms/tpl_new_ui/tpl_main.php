<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="d-flex flex-grow-1">
    <div id="sidebar-container" class="sidebar-expanded">
        <div class="d-flex h-100 flex-column">
            <ul class="list-group sticky-top">
                <div id="collapseBtn" data-toggle="sidebar-collapse" class="ui-side-menu-button list-group-item list-group-item-action rounded-0 d-flex">
                    <div class="d-flex align-items-center">
                        <span id="collapse-icon" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Toggle Sidebar" class="fas fa-fw mr-0"></span>
                        <!-- <span id="collapse-text" class="ml-1 menu-collapsed">Collapse sidebar</span> -->
                    </div>
                </div>
                <div id="left-side-scroll-area">
                <input data-style="ui-toggle" id="ui-edit-toggle" type="checkbox" data-toggle="toggle" data-onstyle="warning" data-on="<i class='fa fa-fw fa-edit' data-trigger='hover focus' data-toggle='popover' data-placement='top' data-content='Toggle Edit'></i> <span class='ml-1  menu-collapsed'>Disable edit</span>" data-off="<i class='fa fa-edit fa-fw' data-trigger='hover focus' data-toggle='popover' data-placement='top' data-content='Toggle Edit'></i> <span class=' ml-1 menu-collapsed'>Edit</span>">
                
                    <?php $this->output_page_preview_button(); ?>
                    <?php $this->output_create_new_button(); ?>
                    <?php $this->output_import_button(); ?>
                    <?php $this->output_lists(); ?>
                </div>
            </ul>
            <div class="flex-grow-1"></div>
        </div>
    </div>

    <div id="ui-middle" class="flex-grow-1">
        <?php $this->output_page_content(); ?>
    </div>

    <div id="properties" class="properties-expanded">
        <div class="d-flex h-100 flex-column">
            <ul class="list-group sticky-top">
                <div id="collapsePropertiesBtn" data-toggle="properties-collapse" class="ui-side-menu-button list-group-item list-group-item-action rounded-0 pt-0 pb-0 pr-2 d-flex align-items-center">
                    <div class="d-flex align-items-center w-100">
                        <span id="collapse-properties-icon" data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="Toggle Properties" class="fas"></span>
                        <!-- <span class="ml-1 properties-collapsed flex-grow-1 align-items-center justify-content-between">Collapse properties 
                            <button id="save-properties" type="button" class="btn btn-warning btn-sm">Save</button>
                            </span> -->
                    </div>
                </div>
                <?php $this->output_fields(); ?>
            </ul>
            <!-- <div class="flex-grow-1"></div> -->
        </div>
    </div>
</div>