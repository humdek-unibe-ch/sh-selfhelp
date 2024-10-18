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
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
            <div class="mb-3">
                <input type="email" class="form-control" name="email" placeholder="<?php echo $this->user_label; ?>" required>
            </div>
            <div class="mb-3 d-none">
                <input type="text" class="form-control" name="type" required value="register">
            </div>
            <?php $this->open_registration ? '' :  require __DIR__ . "/tpl_code.php"; ?>
            <button type="submit" class="w-100 btn btn-<?php echo $this->type; ?>"><?php echo $this->submit_label; ?></button>
        </form>
    </div>
</div>
