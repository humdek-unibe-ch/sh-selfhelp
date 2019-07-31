<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Add User to Chat Room</h1>
        <p>Adding a user to a chat room will allow this user to communicate within this room.</p>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            Adding User
        </div>
        <div class="card-body">
            <form action="<?php echo $url; ?>" method="POST" autocomplete="off">
                <input class="form-control" type="text" name="user_search" value="" placeholder="Search User Email" required>
                <input type="hidden" name="add_user" value="" placeholder="">
                <div class="search-target mb-3">
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="<?php echo $url_cancel; ?>" class="float-right btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
