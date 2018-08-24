<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The groups were successfully added to the user <code><?php echo $this->selected_user['email']; ?></code>.</p>
        <p>The user has now the following groups:</p>
        <?php $this->output_local_component("user_groups"); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the User</a>
    </div>
</div>
