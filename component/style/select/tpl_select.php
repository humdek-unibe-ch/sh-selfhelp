<select id="<?php echo $this->id_field; ?>" <?php echo $multiple; ?> name="<?php echo $this->name; ?>" class="form-control <?php echo $css; ?>" <?php echo $required; ?>>
    <?php $this->output_fields(); ?>
</select>
