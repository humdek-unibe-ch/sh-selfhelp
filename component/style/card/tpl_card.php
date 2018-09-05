<div class="card card-<?php echo $this->type; ?> <?php echo $this->css; ?>">
    <?php $this->output_card_header(); ?>
    <div class="card-body <?php echo $collapse; ?> <?php echo $show; ?>">
        <?php $this->output_children(); ?>
    </div>
</div>
