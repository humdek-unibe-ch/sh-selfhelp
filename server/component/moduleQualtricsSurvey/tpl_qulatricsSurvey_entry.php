<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container">    
    <?php 
        if($this->mode === UPDATE && !$this->survey){
            echo "Missing entry!";
        }else{
            $this->output_entry_form();
            echo $this->mode === INSERT ? '' : $this->output_delete_form();
        }
    ?>
</div>