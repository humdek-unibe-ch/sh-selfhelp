<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div class=".flex-row">
    <div class="btn mt-3 text-light category btn-warning">Chat rooms</div>
    <?php $this->output_room_list(); ?>
</div>
<div class=".flex-row">    
    <?php  
        if(!empty($this->model->get_groups())){
            echo '<div class="btn mt-3 text-light category btn-warning">Groups</div>';
        }
    ?>    
    <?php $this->output_group_list(); ?>
</div>
<div class="row my-3">
    <div class="col-sm-auto mb-2">
        <button class="collapse-toggle d-sm-none d-auto btn btn-secondary w-100" type="button" data-toggle="collapse" data-target="#side-menu-collapse" aria-controls="side-menu-collapse">
            <i class="fas fa-users mr-2"></i><?php echo $this->subjects; ?>
        </button>
        <div id="side-menu-collapse" class="list-group collapse d-sm-block">
            <?php $this->output_subjects(); ?>
        </div>
    </div>
    <div class="col">
        <?php $this->output_chat($title); ?>
    </div>
</div>
