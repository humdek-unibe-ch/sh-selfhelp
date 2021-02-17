<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div id="selectImage-<?php echo $this->id_section; ?>" class="selectImage <?php echo $this->css; ?>" data-values=<?php echo json_encode($this->items); ?>></div>
<input id="selectValue-<?php echo $this->id_section; ?>" type="hidden" name="<?php echo $this->name; ?>" value="<?php echo $this->value; ?>" <?php echo $required; ?>></input>