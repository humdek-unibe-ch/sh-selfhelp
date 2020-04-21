<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a href="<?php echo $url; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo $active; ?>">
    <?php echo $name . ' - [ ' . $subject_code . ' ]'; ?>
    <?php $this->output_new_badge_subject($id); ?>
</a>