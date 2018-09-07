<div class="<?php echo $css; ?>">
    <input id=<?php echo $this->id_field; ?> type="range" name="<?php echo $this->name; ?>" class="custom-range" min="<?php echo $this->min; ?>" max="<?php echo $this->max; ?>" value="<?php echo $this->count; ?>">
    <div class="slider-legend">
        <?php $this->output_legend(); ?>
    </div>
</div>
