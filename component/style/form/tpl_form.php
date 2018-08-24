<form action="<?php echo $this->url ?>" method="post">
    <?php $this->output_children(); ?>
    <button type="submit" class="btn btn-<?php echo $this->type; ?>">
        <?php echo $this->label; ?>
    </button>
    <?php $this->output_cancel(); ?>
</form>
