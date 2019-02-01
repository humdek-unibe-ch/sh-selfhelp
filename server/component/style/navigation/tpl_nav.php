<div class="container<?php echo $fluid; ?> my-3">
    <div class="row">
        <div class="col-md-auto nav-col nav-md-col mb-2">
            <?php $this->output_nav(); ?>
        </div>
        <div class="col">
            <?php $this->output_children(); ?>
            <div>
                <?php $this->output_buttons(); ?>
            </div>
        </div>
    </div>
</div>
