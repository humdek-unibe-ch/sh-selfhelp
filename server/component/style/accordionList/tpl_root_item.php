<div class="card">
    <div class="card-header accordion-header" id="session-<?php echo $child['id']; ?>" data-toggle="collapse" data-target="#session-content-<?php echo $child['id']; ?>" aria-expanded="true" aria-controls="collapseOne">
        <h5 class="mb-0">
            <?php $this->output_title_prefix($index); ?>
            <?php echo $title; ?>
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
