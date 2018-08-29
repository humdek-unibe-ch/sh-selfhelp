<div class="row">
    <div class="col">
        <div class="jumbotron">
            <h1>User <code><?php echo $this->selected_user['email']; ?></code> <small>[<?php echo $state; ?>]</small></h1>
            <p>A user can be in one of the following states:</p>
            <ul>
                <li>An <code>active</code> user can log in and visit all accessible pages.</li>
                <li>An <code>inactive</code> user cannot login as long as the account is not veryfied.</li>
                <li>A <code>blocked</code> user cannot login until the blocked status is reversed.</li>
            </ul>
            <p>Use the cards on the right (if available) to manipulate the groups of the user, block the user, or delete the user. The button <code>ACL</code> in the top right corner expands to a table, listing the access rights of the user in detail.</p>
        </div>
    </div>
    <div class="col">
        <?php $this->output_user_manipulation(); ?>
    </div>
    <div class="col-auto">
        <?php $this->output_user_acl(); ?>
    </div>
</div>
