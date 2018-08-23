<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-auto">
            <?php $this->output_lists(); ?>
        </div>
        <div class="col">
            <div class="row">
                <div class="col">
                    <?php $this->output_alerts(); ?>
                    <?php $this->output_breadcrumb(); ?>
                </div>
            </div>
            <div class="row">
                <?php $this->output_fields(); ?>
                <div class="col">
                    <?php $this->output_page_content(); ?>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <?php $this->output_controls(); ?>
        </div>
    </div>
</div>
