<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Create New Group</h1>
        <p>
            A new user requires a name and ACL setings. A group can be assigned to a user which provides this user with the access rights defined in the group.
        </p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">New Group</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter group name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="desc" placeholder="Enter description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Assign Group Access Rights</label>
                    <?php $this->output_group_acl(); ?>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
</div>
