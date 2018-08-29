<?php $this->output_alert(); ?>
<div class="row">
    <div class="col">
        <div class="jumbotron">
            <h1>Group <code><?php echo $this->selected_group['name']; ?></code></h1>
            <p class="lead">&mdash; <?php echo $this->selected_group['desc']; ?> &mdash;</p>
            <p>There are four different function where acces right can be specified:
 </p>
            <ul>
                <li><strong>Core Content</strong> is composed of all the pages which do not relate directly to the experiment (e.g. impressum, disclaimer, profile, etc.).
                    <ul>
                        <li><code>select</code> grants read access to a page.</li>
                        <li><code>insert</code> allows to add new sections to a page.</li>
                        <li><code>update</code> allows to modify existing content on a page.</li>
                        <li><code>delete</code> allows to remove sections from a page.</li>
                    </ul>
                </li>
                <li><strong>Experiment Content</strong> is composed of all pages which are created explicitly for the experiment.
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
                        <li><code>insert</code> allows to create a new page.</li>
                        <li><code>update</code> allows to modify page properties and content (if the specific page access rights are granted).</li>
                        <li><code>delete</code> allows to delete a page.</li>
                    </ul>
                </li>
                <li><strong>User Management</strong>
                    <ul>
                        <li><code>select</code> grants read access to the user and group overview.</li>
                        <li><code>insert</code> allows to create a new user.</li>
                        <li><code>update</code> allows to modify access right of users and groups.</li>
                        <li><code>delete</code> allows to delete users.</li>
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
