<button class="btn btn-<?php echo $this->type; ?> tab-button <?php echo $active; ?> <?php echo $this->css; ?>" data-context="<?php echo $this->id; ?>" type=button>
    <?php echo $this->label; ?>
</button>
<div class="card card-body bg-light tab-content tab-content-index-<?php echo $this->id; ?> <?php echo $active; ?>">
    <?php echo $this->output_children(); ?>
</div>
