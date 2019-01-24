<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Delete Chat Room</h1>
        <p>This will delete the chat room <code><?php echo $name; ?></code>.</p>
        <p><strong>Deleting a chat room will delete all conversation that happened within this room!</strong></p>
    </div>
    <?php $this->output_form(); ?>
</div>
