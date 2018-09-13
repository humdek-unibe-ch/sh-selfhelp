<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The group list of user <code><?php echo $this->selected_user['email']; ?></code> was successfully updated.</p>
        <p>Current groups of user  <code><?php echo $this->selected_user['email']; ?></code>:</p>
        <?php $this->output_local_component("user_groups"); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the User</a>
    </div>
</div>
