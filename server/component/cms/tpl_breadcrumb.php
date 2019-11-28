<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php $this->output_breadcrumb_children($this->model->get_page_sections()); ?>
    </ol>
</nav>
