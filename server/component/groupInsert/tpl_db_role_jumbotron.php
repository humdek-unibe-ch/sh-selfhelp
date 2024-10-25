<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="col">
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1>Create New DB Role</h1>
        <p>
            A new user requires a name and ACL settings. A group can be assigned to a user which provides this user with the access rights defined in the group.
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
        </ul>
    </div>
</div>