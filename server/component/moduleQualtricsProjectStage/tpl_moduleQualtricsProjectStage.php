<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php 
        if($this->mode === INSERT){
            $this->output_add_stage();
        }else if(!$this->stage){
            echo 'Missing entry';
        }else if($this->mode === SELECT){
            $this->output_add_stage_view();
        }else if($this->mode === UPDATE){
            $this->output_add_stage();
            $this->output_delete_stage();
        }
    ?>
</div>
