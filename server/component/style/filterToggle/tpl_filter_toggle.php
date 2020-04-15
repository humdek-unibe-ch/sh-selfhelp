<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<button class="filter-toggle-switch btn btn-outline-<?php echo $this->type; ?><?php echo $is_active ? " active" : ""; ?>">
    <?php echo $this->label; ?>
    <i class="d-none ml-1 filter-toggle-pending fas fa-spinner fa-spin"></i>
</button>
