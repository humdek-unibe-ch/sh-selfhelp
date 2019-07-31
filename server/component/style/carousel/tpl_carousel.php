<div id="<?php echo $this->id_prefix; ?>-carousel" class="carousel slide <?php echo $crossfade; ?> <?php echo $this->css; ?>" data-ride="carousel">
    <?php $this->output_indicator_wrapper(); ?>
    <div class="carousel-inner">
        <?php $this->output_carousel_items(); ?>
    </div>
    <?php $this->output_controls(); ?>
</div>
