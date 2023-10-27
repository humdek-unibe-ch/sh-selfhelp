<div id = "scheduled-jobs-events" data-scheduled-jobs="<?php echo htmlspecialchars(json_encode($this->model->get_scheduled_events()), ENT_QUOTES, 'UTF-8'); ?>">
    <?php $this->output_calendar() ?>
</div>