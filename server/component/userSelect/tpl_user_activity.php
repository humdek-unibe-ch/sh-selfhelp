<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="mb-3">
    <table id="user-activity" class="table table-sm table-hover d-none">
        <thead>
            <tr>
                <th scope="col"><?php $this->output_title("id"); ?></th>
                <th scope="col"><?php $this->output_title("email"); ?></th>
                <th scope="col"><?php $this->output_title("status"); ?></th>
                <th scope="col"><?php $this->output_title("code"); ?></th>
                <th scope="col"><?php $this->output_title("user_name"); ?></th>
                <th scope="col"><?php $this->output_title("groups"); ?></th>
                <th scope="col"><?php $this->output_title("user_type"); ?></th>
                <th scope="col"><?php $this->output_title("login"); ?></th>
                <th scope="col"><?php $this->output_title("activity"); ?></th>
                <th scope="col"><?php $this->output_title("progress"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $this->output_user_activity_rows(); ?>
        </tbody>
    </table>
</div>
