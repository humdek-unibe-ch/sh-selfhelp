<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3 <?php echo $this->css; ?>">
    <?php $this->output_alert(); ?>
    <div class="card card-header mb-4 rounded-2 py-5 px-3">
        <h1><?php echo $this->title; ?></h1>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0"><?php echo $this->subtitle; ?></h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                <div class="mb-3 d-none">
                    <label>Leave this field empty</label>
                    <input type="text" class="form-control" name="phone7h92jP" autocomplete="off">
                </div>
                <div class="mb-3 <?php echo $this->get_css_name(); ?>">
                    <label><?php echo $this->name_label; ?></label>
                    <input type="text" class="form-control" autocomplete="username" name="name" placeholder="<?php echo $this->name_placeholder; ?>" value="<?php echo $name; ?>" required>
                    <small class="form-text text-body-secondary"><?php echo $this->name_description; ?></small>
                </div>
                <div class="mb-3">
                    <label><?php echo $this->pw_label; ?></label>
                    <input type="password" autocomplete="new-password" class="form-control mb-1" name="pw" placeholder="<?php echo $this->pw_placeholder; ?>" required>
                    <input type="password" autocomplete="new-password" class="form-control" name="pw_verify" placeholder="<?php echo $this->pw_confirm_label; ?>" required>
                </div>
                <div class="mb-3 <?php echo $this->get_css_gender(); ?>">
                    <div>
                    <label><?php echo $this->gender_label; ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" value="1" <?php echo $male_checked; ?> required>
                        <label class="form-check-label"><?php echo $this->gender_male; ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="2" <?php echo $female_checked; ?> required>
                        <label class="form-check-label"><?php echo $this->gender_female; ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" value="3" <?php echo $divers_checked; ?> required>
                        <label class="form-check-label"><?php echo $this->gender_divers; ?></label>
                    </div>
                </div>
                <?php $this->check_custom_fields(); ?>
                <button type="submit" class="btn btn-primary"><?php echo $this->activate_label; ?></button>
            </form>
        </div>
    </div>
</div>
