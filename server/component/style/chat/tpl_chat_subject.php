<?php $this->output_alert(); ?>
<div class="row my-3">
    <div class="col-sm-auto mb-2">
        <button class="collapse-toggle d-sm-none d-auto btn btn-secondary w-100" type="button" data-toggle="collapse" data-parent="custom-collapse" data-target="#side-menu-collapse">
        <i class="fas fa-users mr-2"></i><?php echo "Hanuele"; ?>
        </button>
        <div id="side-menu-collapse" class="list-group collapse d-sm-block">
            <?php $this->output_rooms(); ?>
        </div>
    </div>
    <div class="col">
        <?php $this->output_chat($title); ?>
    </div>
</div>
