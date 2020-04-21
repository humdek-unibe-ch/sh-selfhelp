<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_controller_alerts_fail(); ?>
<?php $this->output_controller_alerts_success(); ?>
<form action="<?php echo $target; ?>" method="post">
    <?php $this->output_form_items($langs); ?>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
