<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="<?php echo $this->id_section; ?>" class="modal fade <?php echo $this->css ?>">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $this->output_title(); ?>
            <div class="modal-body">
                <?php $this->output_children(); ?>
            </div>
        </div>
    </div>
</div>
