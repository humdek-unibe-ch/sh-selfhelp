<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The user was successfully removed from the chat room <code><?php echo $room; ?></code>.</p>
        <p>The chat room has now the following users:</p>
        <?php $this->output_room_users(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the Chat Room</a>
    </div>
</div>
