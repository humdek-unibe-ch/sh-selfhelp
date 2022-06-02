<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="ui-section-holder ml-1 mr-1 mt-2 mb-2 rounded grabbable" draggable="true" data-section='<?php echo json_encode($data_section); ?>'>
    <span class="badge badge-secondary"></span>
    <span>
        Name: <code><?php echo $this->model->get_section_name(); ?></code>
        Style: <code><?php echo $this->model->get_style_name(); ?></code>
        Id: <code><?php echo $this->id_section ?></code>
    </span>
    <div class="p-1 <?php echo ($data_section['can_have_children'] ? 'section-can-have-children' : ''); ?>">
        <?php $this->output_content(); ?>
    </div>
</div>