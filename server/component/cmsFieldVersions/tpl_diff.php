<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="cms-fv-diff-wrap">
    <table class="cms-fv-diff">
        <?php foreach ($rows as $row) { ?>
        <?php if ($row['type'] === 'skip') { ?>
        <tr class="cms-fv-skip"><td colspan="4">&hellip; <?php echo intval($row['text']); ?> unchanged <?php echo intval($row['text']) === 1 ? 'line' : 'lines'; ?></td></tr>
        <?php } else { ?>
        <tr class="cms-fv-<?php echo $row['type']; ?>">
            <td class="cms-fv-ln"><?php echo $row['old'] !== null ? intval($row['old']) : ''; ?></td>
            <td class="cms-fv-ln"><?php echo $row['new'] !== null ? intval($row['new']) : ''; ?></td>
            <td class="cms-fv-sign"><?php echo $row['type'] === 'add' ? '+' : ($row['type'] === 'del' ? '&minus;' : ''); ?></td>
            <td class="cms-fv-code"><?php echo htmlspecialchars($row['text']); ?></td>
        </tr>
        <?php } ?>
        <?php } ?>
    </table>
</div>
