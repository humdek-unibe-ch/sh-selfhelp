<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container">
    <?php
    if ($this->mode === INSERT) {
        $this->output_entry_form();
    } else if (!$this->survey) {
        echo "Missing entry!";
    } else if ($this->mode === SELECT) {
        $this->output_entry_form_view();
    } else if ($this->mode === UPDATE) {
        $this->output_entry_form();
        echo $this->mode === INSERT ? '' : $this->output_delete_form();
    } else {
        echo "Missing entry!";
    }
    ?>
</div>