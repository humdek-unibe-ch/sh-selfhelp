<div class="container-fluid">
    <div class="card mb-3">
        <div class="row">
            <div class="col-auto align-self-center d-none d-sm-flex">
            <h5 class="m-3"><?php echo $progress_label; ?></h5>
            </div>
            <div class="col">
                <div class="card-body">
                    <?php $this->output_progress_bar(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <?php $this->output_nav(); ?>
    </div>
</div>
