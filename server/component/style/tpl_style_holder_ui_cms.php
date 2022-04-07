<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="ui-section-holder m-3 rounded grabbable" draggable="true" data-section='<?php echo json_encode($data_section); ?>'>
    <span class="badge badge-secondary"></span>
    <span>
        Style: <code><?php echo $this->style_name; ?></code>
        Name: <code><?php echo $this->model->get_section_name(); ?></code>
        Children: <code><?php echo $data_section['can_have_children'] ?></code>
    </span>
    <div class="p-3 <?php echo ($data_section['can_have_children'] ? 'section-can-have-children' : ''); ?>">
        <?php $this->output_content(); ?>
    </div>
</div>