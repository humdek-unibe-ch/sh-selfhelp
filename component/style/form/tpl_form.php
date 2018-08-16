<form action="<?php echo $url ?>" method="post">
    <?php $this->output_children(); ?>
    <button type="submit" class="btn btn-<?php echo $type; ?>">
        <?php echo $label; ?>
    </button>
</form>
