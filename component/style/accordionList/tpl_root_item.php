<div class="card">
    <div style="cursor:pointer" class="card-header" id="session-<?php echo $child['id']; ?>" data-toggle="collapse" data-target="#session-content-<?php echo $child['id']; ?>" aria-expanded="true" aria-controls="collapseOne">
        <h5 class="mb-0">
            <small><?php echo $item_label . " " . intval($index + 1); ?>:</small> <?php echo $child['title']; ?>
        </h5>
    </div>
    <div id="session-content-<?php echo $child['id']; ?>" class="collapse <?php echo $active; ?>" aria-labelledby="session-<?php echo $child['id']; ?>" data-parent="#accordionExample">
        <div class="card-body">
            <?php
                $this->output_child($child, true);
                $this->output_nav_children($child['children']);
            ?>
        </div>
    </div>
</div>
