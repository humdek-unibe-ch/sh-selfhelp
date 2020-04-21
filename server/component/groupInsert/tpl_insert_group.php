<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container-fluid mt-3">
    <?php $this->output_alert(); ?>
    <div class="row">
        <div class="col">
            <div class="jumbotron">
                <h1>Create New Group</h1>
                <p>
                    A new user requires a name and ACL setings. A group can be assigned to a user which provides this user with the access rights defined in the group.
                </p>
                <p>There are four different functions where access rights can be specified:</p>
                <ul>
                    <li><strong>Core Content</strong> is composed of all the pages which do not relate directly to the experiment (e.g. impressum, disclaimer, profile, etc.).
                    </li>
                    <li><strong>Experiment Content</strong> is composed of all pages which are created explicitly for the experiment.
                    </li>
                    <li><strong>Open Content</strong> is composed of all pages which are openly accessible. These pages are not considered as experiment pages. For all the above:
                        <ul>
                            <li><code>select</code> grants read access to a page.</li>
                            <li><code>insert</code> allows to add new sections to a page.</li>
                            <li><code>update</code> allows to modify existing content on a page.</li>
                            <li><code>delete</code> allows to remove sections from a page.</li>
                        </ul>
                    </li>
                    <li><strong>Page Management</strong>
                        <ul>
                            <li><code>select</code> grants read access the page overview and page properties (CMS).</li>
                            <li><code>insert</code> allows to create new pages.</li>
                            <li><code>update</code> allows to modify page properties and content (if the specific page access rights are granted).</li>
                            <li><code>delete</code> allows to delete pages.</li>
                        </ul>
                    </li>
                    <li><strong>User Management</strong>
                        <ul>
                            <li><code>select</code> grants read access to the user and group overview and allows to download generated validation codes.</li>
                            <li><code>insert</code> allows to create new users and groups and generate validation codes.</li>
                            <li><code>update</code> allows to modify access right of users and groups.</li>
                            <li><code>delete</code> allows to delete users and groups.</li>
                        </ul>
                    </li>
                    <li><strong>Data Management</strong>
                        <ul>
                            <li><code>select</code> allows to download user data and see the list of assets.</li>
                            <li><code>insert</code> allows to upload new assets.</li>
                            <li><code>update</code> allows to modify asset names (not implemented).</li>
                            <li><code>delete</code> allows to delete assets and user data.</li>
                        </ul>
                    </li>
                    <li><strong>Chat Management</strong>
                        <ul>
                            <li><code>select</code> grants read access to the chat management page.</li>
                            <li><code>insert</code> allows to create new chat rooms.</li>
                            <li><code>update</code> allows to add users to chat rooms, and remove users from chat rooms.</li>
                            <li><code>delete</code> allows to delete chat rooms.</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">New Group</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo $action_url; ?>" method="post">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter group name" value="<?php echo $_POST['name'] ?? ""; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="desc" placeholder="Enter description" required><?php echo $_POST['desc'] ?? ""; ?></textarea>
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
    </div>
</div>