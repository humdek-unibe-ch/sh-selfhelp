<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Remove Group from User</h1>
        <p>Removing a group from a user will revoke the permissions of the group from the user.</p>
    </div>
    <?php $this->output_local_component("form_rm_group"); ?>
</div>
