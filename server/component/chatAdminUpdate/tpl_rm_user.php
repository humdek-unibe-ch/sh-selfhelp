<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Removing a User from a Chat Room</h1>
        <p>Removing the user <code><?php echo $user; ?></code> from the chat room <code><?php echo $room?></code> will deprive this user from any communication in the room.</p>
    </div>
    <?php $this->output_form_rm_user(); ?>
</div>
