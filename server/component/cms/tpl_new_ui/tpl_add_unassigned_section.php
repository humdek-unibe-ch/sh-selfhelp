<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<select id="ui-unassigned-section-select" class="ui-select-picker border rounded flex-grow-1" data-live-search="true">
    <option disabled selected value>-- select a section--</option>
    <?php foreach ($unassigned_sections as $key => $section) {
        echo '<option value=' . $key . '">' . $section['title'] . '</option>';
    } ?>
</select>
<button id="ui-unassigned-section-btn" class="btn btn-primary ml-2">Add</button>