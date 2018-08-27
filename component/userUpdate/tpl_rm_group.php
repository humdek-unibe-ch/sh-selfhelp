<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Remove Group <code><?php echo $group; ?></code> from User <code><?php echo $this->selected_user['email']; ?></code></h1>
        <p>Removing a group from a user will revoke the permissions of the group from the user.</p>
    </div>
    <?php $this->output_local_component("form_rm_group"); ?>
</div>
