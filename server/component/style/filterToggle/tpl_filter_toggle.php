<div class="filter-toggle <?php echo $this->css; ?>">
    <button class="filter-toggle-switch btn btn-<?php echo $this->type; ?>">
        <?php echo $this->label; ?>
    </button>
    <div class="filter-toggle-data d-none"><?php $this->output_filter_data(); ?></div>
</div>
