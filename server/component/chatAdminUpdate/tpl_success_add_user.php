<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The user list of chat room <code><?php echo $room; ?></code> was successfully updated.</p>
        <p>Current users of the chat room:</p>
        <?php $this->output_room_users(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the Chat Room</a>
    </div>
</div>
