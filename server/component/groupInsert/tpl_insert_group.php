<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <?php $this->output_alert(); ?>
    <div class="row">
        <?php $this->output_jumbotron(); ?>        
        <div class="col">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">New <?php echo $this->group_type == groupTypes_group ? "Group" : "DB Role"; ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $action_url; ?>" method="post">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter group name" value="<?php echo $_POST['name'] ?? ""; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea class="form-control" name="desc" placeholder="Enter description" required><?php echo $_POST['desc'] ?? ""; ?></textarea>
                        </div>
                        <?php $this->output_acl_tmpl(); ?>
                        <button type="submit" class="btn btn-primary">Create</button>
                        <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-end">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>