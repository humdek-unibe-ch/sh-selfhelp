<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<form action="<?php echo $target; ?>" method="post">
    <?php $this->output_form_items($langs); ?>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
