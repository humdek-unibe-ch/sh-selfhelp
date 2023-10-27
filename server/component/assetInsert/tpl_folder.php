<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="form-group col">
    <label>Folder</label>
    <input list="folders" type="text" class="form-control" name="folder" placeholder="Select folder">
    <datalist id="folders">
        <?php $this->output_folders(); ?>
    </datalist>
</div>