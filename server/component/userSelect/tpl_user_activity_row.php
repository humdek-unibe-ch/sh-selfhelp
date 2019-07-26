<tr class="user-activity-row <?php echo $row_state ?>" id="user-url-<?php echo $url; ?>">
    <td><?php echo $id; ?></td>
    <td><?php echo $email; ?></td>
    <td><?php echo $state; ?></td>
    <td><?php echo $code; ?></td>
    <td><?php echo $last_login; ?></td>
    <td><?php echo $activity; ?></td>
    <td><?php $this->output_user_progress_bar($progress); ?></td>
</tr>
