<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Block User</h1>
        <p>This will block the user <code><?php echo $this->selected_user['email']; ?></code>.</p>
    </div>
    <?php $this->output_form_block(); ?>
</div>
