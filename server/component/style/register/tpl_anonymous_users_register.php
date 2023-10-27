<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card card-<?php echo $this->type; ?> <?php echo $this->css; ?>">
    <div class="card-header">
        <h5 class="m-0"><?php echo $this->title; ?></h5>
    </div>
    <div class="card-body">
        <?php $this->output_alert(); ?>
        <form id = "anonymous-register" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">            
            <div class="form-group d-none">
                <input type="text" class="form-control" name="type" required value="register">
            </div>
            <!-- For now always need a code for anonymous_users -->
            <?php ($this->open_registration && false) ? '' :  require __DIR__ . "/tpl_code.php"; ?>
            <?php $this->output_security_questions(); ?>
            <button type="submit" class="w-100 btn btn-<?php echo $this->type; ?>"><?php echo $this->submit_label; ?></button>
        </form>
    </div>
</div>
