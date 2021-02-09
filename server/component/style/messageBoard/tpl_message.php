<div class="card bg-light mb-3">
    <div class="card-header">
        <h5 class="mb-0 d-flex align-items-center">
            <i class="fas fa-chart-line mr-3"></i>
            <?php echo $title; ?>
            <small class="text-muted ml-auto"><?php echo $time; ?></small>
        </h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <h1><span class="badge badge-<?php echo $color; ?> mr-3"><?php echo $score; ?></span></h1>
            <span class="messageBoard-message"><?php echo $message ?></span>
        </div>
        <div class="pl-3">
            <?php $this->output_message_replies($reply_messages); ?>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center">
        <div>
            <?php $this->output_message_footer_icons($user, $icon_counter, $record_id); ?>
        </div>
        <?php $this->output_message_footer_comments($user, $record_id); ?>
    </div>
</div>
