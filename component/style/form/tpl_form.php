<form action="<?php echo $this->url ?>" method="post">
    <?php $this->output_children(); ?>
    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-<?php echo $this->type; ?>">
            <?php echo $this->label; ?>
        </button>
    </div>
</form>
