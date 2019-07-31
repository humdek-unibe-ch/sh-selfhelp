<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<form id="section-<?php echo $this->id_section; ?>" action="<?php echo $url ?>" method="post" class="form-inline <?php echo $this->css; ?>">
    <?php $input->output_content(); ?>
    <div class="form-group d-none">
        <label>Leave this field empty</label>
        <input type="text" class="form-control" name="phone7h92jP" autocomplete="off">
    </div>
    <button type="submit" class="ml-3 btn btn-<?php echo $this->type; ?>">
        <?php echo $this->label; ?>
    </button>
</form>
