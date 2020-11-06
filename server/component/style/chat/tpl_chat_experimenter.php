<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div class="card mt-3 mb-3">
    <div class="card-body">
        <?php $this->output_group_tabs_holder(); ?>
        <div class="row my-3">
            <div class="col-sm-auto mb-2">
                <table id="subjects" class="table table-sm table-borderless d-none">
                    <thead>
                        <tr>
                            <th><?php echo $subjects ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $this->output_subjects(); ?>
                    </tbody>
                </table>
            </div>
            <div class="col">
                <?php $this->output_chat($title); ?>
            </div>
        </div>
    </div>
</div>