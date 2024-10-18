<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php $this->output_alert(); ?>
<div>
   <div class="bg-light mb-4 rounded-2 py-5 px-3">
      <h1><?php echo $this->selected_group['group_type'] == groupTypes_db_role ? 'DB Role' : 'Group' ?>  <code><?php echo $this->selected_group['name']; ?></code></h1>
      <p class="lead">&mdash; <?php echo $this->selected_group['desc']; ?> &mdash;</p>
   </div>
</div>
<div>
   <?php $this->output_group_acl_custom(); ?>
</div>
<?php /*    
<div class="col-auto">
   <!-- I find it confusing if I show both options. I will hide it for now -->
   <?php $this->output_group_manipulation(); ?>
</div>*/ ?>