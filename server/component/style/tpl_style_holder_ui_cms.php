<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="ui-style-holder m-3 rounded grabbable" draggable="true" data-style='<?php echo json_encode($data_style); ?>' data-trigger="hover focus" data-toggle="popover" data-placement="top" data-content="<div>
        Style: <code><?php echo $this->style_name; ?></code>
        Name: <code><?php echo $data_style['section_name'] ?></code>
    </div>">
    <div>
        Style: <code><?php echo $this->style_name; ?></code>
        Name: <code><?php echo $this->model->get_section_name(); ?></code>
    </div>
    <div class="p-3 <?php echo ($data_style['can_have_children'] ? 'style-can-have-children' : ''); ?>">
        <?php $this->output_content(); ?>
    </div>
</div>