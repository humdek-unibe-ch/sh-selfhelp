<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<form class="formUserInputLogList-quick-form d-none"
      method="post"
      action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI'] . '#section-' . $this->id_section); ?>">
    <input type="hidden" name="__form_name" value="<?php echo htmlspecialchars($this->name); ?>">
    <input type="hidden" name="ajax" value="<?php echo htmlspecialchars((string)$this->ajax); ?>">
    <input type="hidden" name="<?php echo htmlspecialchars(QUICK_ACTION_ID); ?>" value="">
    <input type="hidden" name="<?php echo htmlspecialchars(SELECTED_RECORD_ID); ?>" value="">
</form>
