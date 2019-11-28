<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div class="row">
    <div class="col">
        <div class="jumbotron">
            <h1>Group <code><?php echo $this->selected_group['name']; ?></code></h1>
            <p class="lead">&mdash; <?php echo $this->selected_group['desc']; ?> &mdash;</p>
            <p>There are four different functions where access rights can be specified:
 </p>
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
            <p>The button <code>ACL</code> in the top right corner expands to a table, listing the access rights of the group in detail.</p>
        </div>
    </div>
    <div class="col">
        <?php $this->output_group_manipulation(); ?>
    </div>
    <div class="col-auto">
        <?php $this->output_group_acl(); ?>
    </div>
</div>
