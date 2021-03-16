<div class="card mb-1">
    <div class="card-body py-2">
        <div class="d-flex align-items-center flex-wrap">
            <div class="mr-2"><strong><?php echo $user; ?></strong>:</div>
            <div class="mr-2"><?php $this->output_message_reply($message) ?></div>
            <small class="text-muted ml-auto" title="<?php echo $ts; ?>"><?php echo $time; ?></small>
        </div>
    </div>
</div>
