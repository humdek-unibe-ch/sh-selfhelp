<div class="card mb-3 card-<?php echo $type; ?>">
    <?php $this->output_card_header($show); ?>
    <div class="card-body <?php echo $collapse; ?> <?php echo $show; ?>">
        <?php $this->output_children(); ?>
    </div>
</div>
