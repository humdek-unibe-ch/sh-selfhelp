<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<form id="section-<?php echo $this->id_section; ?>" action="<?php echo $this->url ?>" method="post" class="<?php echo $this->css; ?>">
    <?php $this->output_children(); ?>
    <button type="submit" class="btn btn-<?php echo $this->type; ?>">
        <?php echo $this->label; ?>
    </button>
    <?php $this->output_cancel(); ?>
</form>
