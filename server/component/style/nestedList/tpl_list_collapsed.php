<button class="nested-list-menu-collapse collapse-toggle d-md-none d-auto btn btn-secondary w-100 rounded-0 text-truncate" type="button" data-toggle="collapse" data-parent="custom-collapse" data-target="#<?php echo $this->id_prefix; ?>-menu-collapse">
    <i class="fas fa-bars mr-2"></i><?php echo $title; ?>
</button>
<div id="<?php echo $this->id_prefix; ?>-menu-collapse" class="list-group collapse d-md-block nested-list-menu-collapsible border border-secondary rounded-bottom border-top-0 p-2">
    <?php $this->output_list(); ?>
</div>
