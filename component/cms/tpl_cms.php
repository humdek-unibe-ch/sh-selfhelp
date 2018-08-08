<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_lists(); ?>
        </div>
        <div class="col d-block">
            <?php $this->output_controls(); ?>
            <?php $this->output_page_content(); ?>
            <?php $this->output_fields(); ?>
        </div>
        <?php $this->output_page_properties(); ?>
    </div>
</div>
