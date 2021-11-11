<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php 
        if($this->mode === INSERT){
            $this->output_add_action();
        }else if($this->mode === SELECT){
            $this->output_action_view();
        }else if($this->mode === UPDATE){
            $this->output_add_action();
            $this->output_delete_action();
        }
    ?>
</div>
