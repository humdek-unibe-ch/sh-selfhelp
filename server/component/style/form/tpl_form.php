<form action="<?php echo $this->url ?>" method="post" class="<?php echo $this->css; ?>">
    <?php $this->output_children(); ?>
    <button type="submit" class="btn btn-<?php echo $this->type; ?>">
        <?php echo $this->label; ?>
    </button>
    <?php $this->output_cancel(); ?>
</form>
