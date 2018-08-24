<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Delete User</h1>
        <p>This will delete the user <code><?php echo $this->selected_user['email']; ?></code> and all the data associated to this user.</p>
    </div>
    <?php $this->output_form(); ?>
</div>
