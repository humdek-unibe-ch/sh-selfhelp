<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr class="cursor-pointer" id="stage-url-<?php echo ($stage['project_id'] . '-' . $stage['id']); ?>">
    <td><?php echo $stage['id']; ?></td>
    <td><?php echo $stage['stage_name']; ?></td>
    <td><?php echo $stage['stage_type']; ?></td>
    <td><?php echo $stage['survey_name']; ?></td>
    <td><?php echo $stage['trigger_type']; ?></td>
    <td><?php echo $stage['groups']; ?></td>
    <td><?php echo $stage['functions']; ?></td>
</tr>
