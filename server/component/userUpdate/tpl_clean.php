<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Clean User Data</h1>
        <p>This will remove the user data of user <code><?php echo $this->selected_user['email']; ?></code>. Specifically, this operation will remove all <strong>user activity</strong> as well as all <strong>input data</strong>. User input data is all data that was entered by this user through either a style of type <code>formUserInput</code>, <code>mermaidForm</code>, as well as all information entered during the validation process and in the settings of the user profile (except name, password, and gender).</p>
    </div>
    <?php $this->output_form_clean(); ?>
</div>
