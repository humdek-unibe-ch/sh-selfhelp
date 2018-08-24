<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Add Group to User</h1>
        <p>Adding a group to a user will provide this user with the permissions of the group.</p>
    </div>
    <?php $this->output_local_component("form_add_groups"); ?>
</div>
