<div class="card mb-3 card-<?php echo $this->type; ?>">
    <?php $this->output_card_header(); ?>
    <div class="card-body <?php echo $collapse; ?> <?php echo $show; ?>">
        <?php $this->output_children(); ?>
    </div>
</div>
