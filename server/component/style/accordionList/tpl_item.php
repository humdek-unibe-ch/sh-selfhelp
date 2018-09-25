<div>
    <div class="px-1 session-nav-link <?php echo $active; ?>">
        <?php $this->output_label($child); ?>
    </div>
    <div class="ml-3">
        <?php $this->output_nav_children($child['children']); ?>
    </div>
</div>
