<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="jumbotron">
        <h1>Send activation Email</h1>
        <p>This send actiavation email to user <code><?php echo $this->selected_user['email']; ?></code>.</p>
    </div>
    <?php $this->output_form_activation_email(); ?>
</div>
