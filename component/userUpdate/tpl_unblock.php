<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Unblock User</h1>
        <p>This will unblock the user <code><?php echo $this->selected_user['email']; ?></code>.</p>
    </div>
    <?php $this->output_local_component("form_unblock"); ?>
</div>
