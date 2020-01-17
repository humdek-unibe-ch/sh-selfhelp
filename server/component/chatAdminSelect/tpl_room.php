<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div class="row">
    <div class="col">
        <div class="jumbotron">
            <h1>Chat Room <code><?php echo $name; ?></code></h1>
            <h2>Chat Title <code><?php echo $title; ?></code></h2>
            <p class="lead">&mdash; <?php echo $desc; ?> &mdash;</p>
            <?php $this->output_warnings(); ?>
            <?php $this->output_room_summary(); ?>
        </div>
    </div>
    <div class="col">
        <?php $this->output_room_manipulation(); ?>
    </div>
</div>
