<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr class="user-activity-row <?php echo $row_state ?>" id="user-url-<?php echo $url; ?>">
    <td><?php echo $id; ?></td>
    <td><?php echo $email; ?></td>
    <td><?php echo $state; ?></td>
    <td><?php echo $code; ?></td>
    <td><?php echo $last_login; ?></td>
    <td><?php echo $activity; ?></td>
    <td><?php $this->output_user_progress_bar($progress); ?></td>
</tr>
