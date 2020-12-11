<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<form action="<?php echo $action_url; ?>" method="post">
    <div class="form-group">
        <label>Callback API Key</label>
        <input type="text" maxlength="5" class="form-control" name="callback_api_key" placeholder="Callback API key" value="<?php echo $callback_api_key ?>" required>
    </div>
    <div class="form-group">
        <label>Default Language</label>
        <input type="text" class="form-control" name="defaultLanguage" placeholder="Enter language name" value="<?php echo $selectedLanguage["language"] ?? ""; ?>" required>
    </div>
    <div class="form-group">
        <label>CSV Separator</label>
        <input type="text" maxlength="1" class="form-control" name="csv_separator" placeholder="Enter CSV separator" value="<?php echo $selectedLanguage["csv_separator"] ?? ""; ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="<?php echo $cancel_url; ?>" class="btn btn-secondary float-right">Cancel</a>
</form>