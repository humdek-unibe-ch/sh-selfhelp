<div class="container<?php echo $fluid; ?> <?php echo $this->css; ?>">
    <div class="row">
        <div class="col-md-auto nav-col nav-md-col mb-2">
            <?php $this->output_nav(); ?>
        </div>
        <div class="col">
            <?php $this->output_children(); ?>
            <div>
                <?php $this->output_button($button_back); ?>
                <?php $this->output_button($button_next); ?>
            </div>
        </div>
    </div>
</div>
