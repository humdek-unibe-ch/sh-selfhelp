<div class="progress">
    <div class="progress-bar progress-bar-striped" style="width: <?php echo $progress; ?>%" role="progressbar" aria-valuenow="<?php echo $progress;?>" aria-valuemin="0" aria-valuemax="100">
        <?php $this->output_progress_label($count, $count_max); ?>
    </div>
</div>
