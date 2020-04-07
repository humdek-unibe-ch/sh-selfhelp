<div class="filter-toggle <?php echo $this->css; ?>">
<button class="filter-toggle-switch btn btn-outline-<?php echo $this->type; ?><?php echo $is_active ? " active" : ""; ?>">
        <?php echo $this->label; ?>
        <i class="d-none ml-1 filter-toggle-pending fas fa-spinner fa-spin"></i>
    </button>
    <div class="filter-toggle-data d-none"><?php $this->output_filter_data(); ?></div>
</div>
