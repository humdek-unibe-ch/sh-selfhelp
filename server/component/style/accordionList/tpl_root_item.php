<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<div class="card">
    <div class="card-header accordion-header" id="<?php echo $this->id_prefix; ?>-<?php echo $child['id']; ?>" data-bs-toggle="collapse" data-bs-target="#<?php echo $this->id_prefix; ?>-content-<?php echo $child['id']; ?>" aria-expanded="true" aria-controls="collapseOne">
        <h5 class="mb-0">
            <?php $this->output_title_prefix($index); ?>
            <?php echo $child['title']; ?>
            <?php $this->output_link_symbol($url); ?>
        </h5>
    </div>
    <div id="<?php echo $this->id_prefix; ?>-content-<?php echo $child['id']; ?>" class="collapse <?php echo $active; ?>" aria-labelledby="<?php echo $this->id_prefix; ?>-<?php echo $child['id']; ?>" data-parent="#<?php echo $this->id_prefix; ?>-root">
        <div class="card-body">
            <?php
                $this->output_child($child, true);
                $this->output_nav_children($child['children']);
            ?>
        </div>
    </div>
</div>
