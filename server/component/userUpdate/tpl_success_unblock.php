<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The user <code><?php echo $this->selected_user['email']; ?></code> was successfully unblocked.</p>
        <p>The new status of the user is <code><?php echo $this->user_status; ?></code>.</p>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the User</a>
    </div>
</div>

