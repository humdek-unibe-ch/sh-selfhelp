<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<p>The group was successfully removed from the user <code><?php echo $this->selected_user['email']; ?></code>.</p>
<p>The user has now the following groups:</p>
<?php $this->output_user_groups(); ?>
