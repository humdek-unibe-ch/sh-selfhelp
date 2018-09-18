<div class="container-fluid <?php echo $this->css; ?>">
    <div class="row">
        <div class="col-auto d-none d-lg-flex">
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
