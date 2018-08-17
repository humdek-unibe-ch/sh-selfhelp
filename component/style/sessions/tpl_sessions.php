<div class="container-fluid">
    <div class="jumbotron my-3">
        <h1><?php echo $this->title ?></h1>
        <?php echo $this->text; ?>
        <a href="<?php echo $next_url; ?>" class="btn btn-primary">
            <?php echo $this->continue_label; ?>
        </a>
    </div>
    <div class="card mb-3">
        <div class="row">
            <div class="col-auto align-self-center d-none d-sm-flex">
            <h5 class="m-3"><?php echo $this->progress_label; ?></h5>
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
