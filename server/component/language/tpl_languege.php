<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="container mt-3">
    <?php $this->output_alert(); ?>
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">Edit Language: <code><?php echo $selectedLanguage["language"]; ?></code></h5>
        </div>
        <div class="card-body">
            <form action="<?php echo $action_url; ?>" method="post">
                <div class="form-group">
                    <label>Locale</label>
                    <input type="text" maxlength="5" class="form-control" name="locale" placeholder="Enter locale" value="<?php echo $selectedLanguage["locale"] ?? ""; ?>" required>
                </div>
                <div class="form-group">
                    <label>Language Name</label>
                    <input type="text" class="form-control" name="language" placeholder="Enter language name" value="<?php echo $selectedLanguage["language"] ?? ""; ?>" required>
                </div>
                <div class="form-group">
                    <label>CSV Separator</label>
                    <input type="text" maxlength="1" class="form-control" name="csv_separator" placeholder="Enter CSV separator" value="<?php echo $selectedLanguage["csv_separator"] ?? ""; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
            </form>
        </div>
    </div>
    <?php $this->output_delete_form(); ?>
</div>