<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_side_buttons(); ?>            
        </div>
        <div class="col">    
            <?php $this->output_alert(); ?>        
            <div class="bg-light mb-4 rounded-2 py-5 px-3">
                <h1>Forms Actions</h1>
                <p>This page shows all actions attached to a form. If you click on the action you can select it.
                </p>                 
            </div>
            <?php $this->output_page_content(); ?>   
        </div>
    </div>
</div>
