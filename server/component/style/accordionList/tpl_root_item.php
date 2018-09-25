<div class="card">
    <div class="card-header accordion-header" id="<?php echo $this->id_prefix; ?>-<?php echo $child['id']; ?>" data-toggle="collapse" data-target="#<?php echo $this->id_prefix; ?>-content-<?php echo $child['id']; ?>" aria-expanded="true" aria-controls="collapseOne">
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
