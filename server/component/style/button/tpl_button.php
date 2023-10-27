<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<a href="<?php echo $this->url; ?>" id="<?php echo $this->id; ?>" class="<?php echo $this->css; ?> btn btn-<?php echo $this->type; ?>" data-data='<?php echo json_encode($this->data); ?>' data-confirmation='<?php echo json_encode($data_confirmation); ?>'>
    <?php echo $this->label; ?>
</a>