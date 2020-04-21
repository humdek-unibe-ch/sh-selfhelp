<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <div class="jumbotron">
    <h1>Success</h1>
        <p>The user was successfully removed from the chat room <code><?php echo $room; ?></code>.</p>
        <p>The chat room has now the following users:</p>
        <?php $this->output_room_users(); ?>
        <a href="<?php echo $url; ?>" class="btn btn-primary">Back to the Chat Room</a>
    </div>
</div>
