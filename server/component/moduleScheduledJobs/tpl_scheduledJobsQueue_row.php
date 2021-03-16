<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr class="mailQueue-row" data-row-id='<?php echo $queue['id']; ?>' id="mailQueue-url-<?php echo $url; ?>">
    <td class="details-control"></td>
    <td><?php echo $queue['id']; ?></td>
    <td><?php echo $queue['status']; ?></td>
    <td><?php echo $queue['type']; ?></td>
    <td><?php echo $queue['date_create']; ?></td>
    <td><?php echo $queue['date_to_be_executed']; ?></td>
    <td><?php echo $queue['date_executed']; ?></td>    
    <td><?php echo $queue['description']; ?></td>
    <td><?php echo $queue['recipient']; ?></td>
    <td><?php echo $queue['title']; ?></td>
    <td><?php echo $queue['message']; ?></td>
</tr>