<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="ui-section-holder-page ui-section-holder ml-1 mr-1 mt-2 mb-2 rounded grabbable" draggable="true" data-section='<?php echo json_encode($fields['data_section']); ?>'>
    <span class="badge badge-secondary"></span>
    <span>
        Keyword: <code><?php echo $fields['page_keyword'] ?></code>
        Style: <code>Page</code>
        Id: <code><?php echo $fields['page_id'] ?></code>
    </span>
    <div class="p-1 section-can-have-children">
        <div class="section-children-ui-cms border rounded p-1">

        </div>
    </div>
</div>