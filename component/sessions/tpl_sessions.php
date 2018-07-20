<div class="container-fluid">
    <div class="jumbotron my-3">
        <h1><?php echo $title ?></h1>
        <?php $this->output_intro(); ?>
        <a href="<?php echo $next_url; ?>" class="btn btn-primary">
            <?php echo $continue_label; ?>
        </a>
    </div>
    <div class="card mb-3">
        <div class="row">
            <div class="col-auto align-self-center d-none d-sm-flex">
            <h5 class="m-3"><?php echo $progress_label; ?></h5>
            </div>
            <div class="col">
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped" style="width: <?php echo $progress; ?>%" role="progressbar" aria-valuenow="<?php echo $progress;?>" aria-valuemin="0" aria-valuemax="100">
                            <?php $this->output_progress_label($count, $count_max); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <?php $this->output_nav(); ?>
    </div>
</div>
