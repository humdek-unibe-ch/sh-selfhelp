<div id="<?php echo $this->id_prefix; ?>-carousel" class="carousel slide" data-ride="carousel">
    <?php $this->output_indicator_wrapper(); ?>
    <div class="carousel-inner">
        <?php $this->output_carousel_items(); ?>
    </div>
    <?php $this->output_controls(); ?>
</div>
