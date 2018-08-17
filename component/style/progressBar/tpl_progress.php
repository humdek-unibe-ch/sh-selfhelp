<div class="progress">
    <div class="progress-bar progress-bar-striped bg-<?php echo $this->type; ?>" style="width: <?php echo $progress; ?>%" role="progressbar" aria-valuenow="<?php echo $progress;?>" aria-valuemin="0" aria-valuemax="100">
        <?php $this->output_progress_label(); ?>
    </div>
</div>
