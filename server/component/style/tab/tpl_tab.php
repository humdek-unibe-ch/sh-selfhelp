<button class="btn btn-<?php echo $this->type; ?> tab-button <?php echo $this->css; ?>" type=button>
    <?php echo $this->label; ?>
</button>
<div class="d-none tab-content">
    <?php echo $this->output_children(); ?>
</div>
