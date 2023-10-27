<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<table class="table tableStyle <?php echo $this->css; ?> ">
    <thead>
        <tr>
            <?php $this->output_column_names(); ?>
        </tr>
    </thead>
    <tbody>
        <?php $this->output_children(); ?>
    </tbody>
</table>