<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_lists(); ?>
        </div>
        <div class="col d-block">
            <div class="row">
                <div class="col fields">
                    <?php $this->output_fields(); ?>
                </div>
                <div class="col d-block">
                    <?php $this->output_page_content(); ?>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <?php $this->output_controls(); ?>
            <?php $this->output_page_property_items(); ?>
        </div>
    </div>
</div>
