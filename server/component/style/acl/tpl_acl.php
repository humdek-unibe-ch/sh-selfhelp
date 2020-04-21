<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<table class="table table-hover table-sm table-responsive-sm <?php echo $this->css; ?>">
    <thead class="thead-dark">
        <tr>
        <th scope="col"><?php echo $this->title; ?></th>
            <th scope="col">Select</th>
            <th scope="col">Insert</th>
            <th scope="col">Update</th>
            <th scope="col">Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php $this->output_items(); ?>
    </tbody>
</table>
