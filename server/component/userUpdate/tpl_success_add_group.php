<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<p>The group list of user <code><?php echo $this->selected_user['email']; ?></code> was successfully updated.</p>
<p>Current groups of user  <code><?php echo $this->selected_user['email']; ?></code>:</p>
<?php $this->output_user_groups(); ?>
