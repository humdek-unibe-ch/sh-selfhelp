<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr class="mailQueue-row" data-row-id='<?php echo $queue['id']; ?>' id="mailQueue-url-<?php echo $url; ?>">
    <td class="details-control"></td>
    <td><?php echo $queue['id']; ?></td>
    <td><?php echo $queue['status']; ?></td>
    <td><?php echo $queue['date_create']; ?></td>
    <td><?php echo $queue['date_to_be_sent']; ?></td>
    <td><?php echo $queue['date_sent']; ?></td>
    <td><?php echo $queue['from_email']; ?></td>
    <td><?php echo $queue['from_name']; ?></td>
    <td><?php echo $queue['reply_to']; ?></td>
    <td><div class="recipients"><?php echo $queue['recipient_emails']; ?></div></td>
    <td><?php echo $queue['cc_emails']; ?></td>
    <td><?php echo $queue['bcc_emails']; ?></td>
    <td><?php echo $queue['subject']; ?></td>
    <td><?php echo $queue['is_html']; ?></td>
</tr>