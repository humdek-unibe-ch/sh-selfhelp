<?php $this->output_alert(); ?>
<div class="row">
    <div class="col">
        <div class="jumbotron">
            <h1>Chat Room <code><?php echo $name; ?></code></h1>
            <p class="lead">&mdash; <?php echo $desc; ?> &mdash;</p>
            <?php $this->output_warnings(); ?>
            <?php $this->output_room_summary(); ?>
        </div>
    </div>
    <div class="col">
        <?php $this->output_room_manipulation(); ?>
    </div>
</div>
