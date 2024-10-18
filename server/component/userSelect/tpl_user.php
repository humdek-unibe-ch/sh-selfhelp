<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="row">
    <div class="col">
        <div class="bg-light mb-4 rounded-2 py-5 px-3">
            <h1>User <code><?php echo $this->selected_user['email']; ?></code></h1>
            <div class="card card-body mb-3">
                <dl class="row">
                    <dt class="col-12"><?php $this->output_title('status'); ?></dt>
                    <dd class="col"><code><?php echo $state; ?></code> &ndash; <small class="text-body-secondary"><?php echo $desc; ?></small></dd>
                    <dt class="col-12"><?php $this->output_title('code'); ?></dt>
                    <dd class="col"><code><?php echo $code; ?></code></dd>  
                    <dt class="col-12"><?php $this->output_title('user_name'); ?></dt>
                    <dd class="col"><code><?php echo $user_name; ?></code></dd>  
                    <dt class="col-12"><?php $this->output_title('groups'); ?></dt>
                    <dd class="col"><?php echo $groups; ?></dd>                     
                    <dt class="col-12"><?php $this->output_title('user_type'); ?></dt>
                    <dd class="col"><?php echo $user_type; ?></dd>                     
                    <dt class="col-12"><?php $this->output_title('login'); ?></dt>
                    <dd class="col"><?php echo $last_login; ?></dd>
                    <dt class="col-12"><?php $this->output_title('activity'); ?></dt>
                    <dd class="col"><?php echo $activity; ?></dd>
                    <dt class="col-12"><?php $this->output_title('progress'); ?></dt>
                    <dd class="col"><?php $this->output_user_progress_bar($progress); ?></dd>
                </dl>
            </div>
            <p>Use the cards on the right (if available) to manipulate the groups of the user, block the user, or delete the user. The button <code>ACL</code> in the top right corner expands to a table, listing the access rights of the user in detail.</p>
        </div>
    </div>
    <div class="col">
        <?php $this->output_user_manipulation(); ?>
    </div>
    <div class="col-auto">
        <?php $this->output_user_acl(); ?>
    </div>
</div>
