<div class="<?php echo $css; ?>">
    <input type="range" name="<?php echo $this->name; ?>" class="custom-range" min="<?php echo $this->min; ?>" max="<?php echo $this->max; ?>" value="<?php echo $this->value; ?>">
    <div class="slider-legend">
        <?php $this->output_legend(); ?>
    </div>
</div>
