<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr class="cursor-pointer" id="action-url-<?php echo ($action['project_id'] . '-' . $action['id']); ?>">
    <td><?php echo $action['id']; ?></td>
    <td><?php echo $action['action_name']; ?></td>
    <td><?php echo $action['survey_type']; ?></td>    
    <td><?php echo $action['survey_name']; ?></td>    
    <td><?php echo $action['trigger_type']; ?></td>
    <td><?php echo $action['groups']; ?></td>
    <td><?php echo $action['action_schedule_type']; ?></td>
    <td><?php echo $action['survey_reminder_name']; ?></td>
    <td><?php echo $action['functions']; ?></td>
</tr>
