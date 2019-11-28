<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<tr>
    <th scope="row"><?php echo $name; ?></th>
    <?php $this->output_items_checkbox($key, $acl["acl"], $disabled); ?>
</tr>
