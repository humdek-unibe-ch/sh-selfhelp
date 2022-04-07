<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<select id="ui-new-style" class="selectpicker border rounded flex-grow-1" data-live-search="true">
    <option disabled selected value>-- select a style --</option>
    <?php foreach ($styles as $key => $style) {
        echo '<option value=' . $style['value'] . '">' . $style['text'] . '</option>';
    } ?>
</select>
<button class="btn btn-primary ml-2">Add</button>